<?php

/**
 *
 * @author QiangYu
 *
 * order_goods 操作，一个订单可以同时购买多个商品，这里是操作订单里面商品记录的
 *
 * */

namespace Core\Service\Order;

use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;
use Core\Service\Goods\Comment as GoodsCommentService;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Goods\Spec as GoodsSpecService;
use Core\Service\Order\Order as OrderBasicService;
use Core\Service\User\User as UserBasicSerivce;

class Goods extends \Core\Service\BaseService
{

    /** 订单状态 order_goods 中 order_goods_status 字段 */
    const OGS_UNPAY   = 0, // 未付款
        OGS_PAY       = 1, // 已付款
        OGS_ASKREFUND = 2, // 申请退款
        OGS_REFUNDING = 3, // 退款中
        OGS_REFUND    = 4; // 已经退款

    public static $orderGoodsStatusDesc = array(
        Goods::OGS_UNPAY     => '未付款',
        Goods::OGS_PAY       => '已付款',
        Goods::OGS_ASKREFUND => '申请退款',
        Goods::OGS_REFUNDING => '退款中',
        Goods::OGS_REFUND    => '退款完成',
    );

    /**
     * @param int    $recId
     * @param int    $status     对应我们定义的常量
     * @param string $adminName  操作人姓名
     * @param string $logMessage 日志记录
     */
    public function markOrderGoodsStatus($recId, $status, $adminName, $logMessage)
    {
        //TDODO: 晚会实现
    }

    /**
     * 更新 goods 中用户支付的数量
     *
     * @param $goods_id
     * @param $userPayNumber
     */
    public function updateGoodsUserPayCount($goods_id, $userPayNumber)
    {
        // 参数验证
        $validator     = new Validator(array('goods_id' => $goods_id, 'userPayNumber' => $userPayNumber));
        $goods_id      = $validator->required()->digits()->min(1)->validate('goods_id');
        $userPayNumber = $validator->required()->digits()->min(1)->validate('userPayNumber');
        $this->validate($validator);

        // 用户完成了支付，我们需要更新支付数量
        $sql      = 'UPDATE ' . DataMapper::tableName('goods')
            . ' set user_pay_number = user_pay_number + (' . $userPayNumber . ')'
            . ' where goods_id = ? limit 1';
        $dbEngine = DataMapper::getDbEngine();
        $dbEngine->exec($sql, $goods_id);
    }

    /**
     * 更新商品的库存
     *
     * @param int    $goods_id
     * @param string $specStr      商品规格
     * @param int    $goods_number 库存
     */
    public function updateGoodsGoodsNumber($goods_id, $specStr, $goods_number)
    {
        // 参数验证
        $validator    =
            new Validator(array('goods_id' => $goods_id, 'specStr' => $specStr, 'goods_number' => $goods_number));
        $goods_id     = $validator->required()->digits()->min(1)->validate('goods_id');
        $specStr      = $validator->validate('specStr');
        $goods_number = $validator->required()->digits()->min(1)->validate('goods_number');
        $this->validate($validator);

        $goodsBasicService = new GoodsBasicService();

        $goods = $goodsBasicService->loadGoodsById($goods_id);
        if ($goods->isEmpty()) {
            // 不存在的商品，退出
            return;
        }

        // 商品有规格选择，我们需要计算规格的库存
        if (!empty($goods->goods_spec)) {
            $goodsSpecService = new GoodsSpecService();
            $goodsSpecService->initWithJson($goods->goods_spec);
            $goodsSpecDataArray = $goodsSpecService->getGoodsSpecDataArray($specStr);
            if (empty($goodsSpecDataArray)) {
                goto no_spec_goods_calc; // 不正常的商品规格选择，按照普通购买计算库存
            }

            // 计算剩余库存
            $specGoodsNumber = @$goodsSpecDataArray['goods_number'];
            $specGoodsNumber -= $goods_number;
            $specGoodsNumber = ($specGoodsNumber >= 0) ? $specGoodsNumber : 0; // 确保库存不能为负数

            // 更新商品规格对应的库存
            $goodsSpecService->setGoodsSpecGoodsNumber($specStr, $specGoodsNumber);
            $goods->goods_spec = $goodsSpecService->getJsonStr();
            $goods->save();
            return;
        }

        no_spec_goods_calc: // 普通商品，没有商品规格选择

        $goods->goods_number -= $goods_number;
        // 确保库存不能是负数
        $goods->goods_number = ($goods->goods_number >= 0) ? $goods->goods_number : 0;
        $goods->save();
    }

    /**
     * 标记 order_goods 记录为已经支付状态
     *
     * @param $orderId
     */
    public function markOrderGoodsPay($orderInfo)
    {
        // 参数验证
        $validator = new Validator(array('orderId' => $orderInfo['order_id']));
        $orderId   = $validator->required()->digits()->min(1)->validate('orderId');
        $this->validate($validator);

        // 设置 order_goods 的状态
        $sql      = 'update ' . DataMapper::tableName('order_goods') . ' set order_goods_status = ' . Goods::OGS_PAY
            . ', update_time = ' . Time::gmTime()
            . ' where order_id = ? ';
        $dbEngine = DataMapper::getDbEngine();
        $dbEngine->exec($sql, $orderId);

        $goodsCommentService = new GoodsCommentService();
        $userBasicService    = new UserBasicSerivce();
        $userInfo            = $userBasicService->loadUserById($orderInfo['user_id']);
        $orderBasicService   = new OrderBasicService();
        $orderGoodsArray     = $orderBasicService->fetchOrderGoodsArray($orderId);
        foreach ($orderGoodsArray as $orderGoodsItem) {
            // 更新商品的销售数量
            $this->updateGoodsUserPayCount($orderGoodsItem['goods_id'], $orderGoodsItem['goods_number']);
            // 更新商品的库存
            $this->updateGoodsGoodsNumber(
                $orderGoodsItem['goods_id'],
                $orderGoodsItem['goods_attr'],
                $orderGoodsItem['goods_number']
            );
            // 添加一个 goods_comment 记录
            if (!$goodsCommentService->isOrderGoodsCommentExist($orderGoodsItem['rec_id'])) {
                $goodsComment               = $goodsCommentService->loadGoodsCommentById(0);
                $goodsComment->create_time  = Time::gmTime();
                $goodsComment->rec_id       = $orderGoodsItem['rec_id'];
                $goodsComment->goods_id     = $orderGoodsItem['goods_id'];
                $goodsComment->goods_price  = $orderGoodsItem['goods_price'];
                $goodsComment->goods_number = $orderGoodsItem['goods_number'];
                $goodsComment->goods_attr   = $orderGoodsItem['goods_attr'];
                $goodsComment->comment_rate = 5; // 用户不评论，我们默认为好评，显示
                $goodsComment->is_show      = 1; // 用户不评论，我们默认为好评，显示
                $goodsComment->user_id      = $userInfo['user_id'];
                $goodsComment->user_name    = $userInfo['user_name'];
                $goodsComment->save();
            }
        }
    }

    /**
     * 取得一组 order_goods 的列表
     *
     * @return array 商品列表
     *
     * @param array $condArray 查询条件数组，例如：
     *                         array(
     *                         array('supplier_id = ?', $supplier_id)
     *                         array('is_on_sale = ?', 1)
     *                         array('supplier_price > ? or supplier_price < ?', $priceMin, $priceMax)
     * )
     *
     * @param int   $offset    用于分页的开始 >= 0
     * @param int   $limit     每页多少条
     * @param int   $ttl       缓存多少时间
     *
     */
    public function fetchOrderGoodsArray(array $condArray, $offset = 0, $limit = 10, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('condArray' => $condArray), '');
        $condArray = $validator->requireArray(true)->validate('condArray');
        $this->validate($validator);

        $tableLeftJoinStr =
            DataMapper::tableName('order_goods') . ' as og LEFT JOIN ' . DataMapper::tableName('order_refer')
            . ' as orf ON og.order_id = orf.order_id LEFT JOIN ' . DataMapper::tableName('order_info')
            . ' as oi ON og.order_id = oi.order_id';

        return $this->_fetchArray(
            $tableLeftJoinStr, //table
            'og.*, oi.user_id, oi.pay_time, oi.add_time', // fields
            $condArray,
            array('order' => 'og.order_id desc, og.rec_id desc'),
            $offset,
            $limit,
            $ttl
        );
    }

    /**
     * order_info 和 order_goods 做 inner join 操作
     *
     * 取得一组记录的数目，用于分页
     *
     * @return int 查询条数
     *
     * @param array $condArray 查询条件数组，例如：
     *                         array(
     *                         array('supplier_id = ?', $supplier_id)
     *                         array('is_on_sale = ?', 1)
     *                         array('supplier_price > ? or supplier_price < ?', $priceMin, $priceMax)
     * )
     *
     * @param int   $ttl       缓存多少时间
     *
     */
    public function countOrderGoodsArray(array $condArray, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('condArray' => $condArray), '');
        $condArray = $validator->requireArray(true)->validate('condArray');
        $this->validate($validator);

        $tableLeftJoinStr =
            DataMapper::tableName('order_goods') . ' as og LEFT JOIN ' . DataMapper::tableName('order_refer')
            . ' as orf ON og.order_id = orf.order_id LEFT JOIN ' . DataMapper::tableName('order_info')
            . ' as oi ON og.order_id = oi.order_id';

        return $this->_countArray(
            $tableLeftJoinStr, //table
            $condArray,
            null,
            $ttl
        );
    }

    /**
     * 对 order_info , order_goods 做 inner_join 查询
     *
     * @param array $condArray
     * @param int   $ttl
     *
     * @return int
     */
    public function countOrderInfoOrderGoodsArray(array $condArray, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('condArray' => $condArray), '');
        $condArray = $validator->requireArray(true)->validate('condArray');
        $this->validate($validator);

        $tableInnerJoinStr =
            DataMapper::tableName('order_goods') . ' as og INNER JOIN ' . DataMapper::tableName('order_info')
            . ' as oi ON og.order_id = oi.order_id';

        return $this->_countArray(
            $tableInnerJoinStr, //table
            $condArray,
            null,
            $ttl
        );
    }

    /**
     *
     * 对 order_info , order_goods 做 inner_join 查询
     *
     * @param array $condArray
     * @param int   $offset
     * @param int   $limit
     * @param int   $ttl
     *
     * @return array
     */
    public function fetchOrderInfoOrderGoodsArray(array $condArray, $offset = 0, $limit = 10, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('condArray' => $condArray), '');
        $condArray = $validator->requireArray(true)->validate('condArray');
        $this->validate($validator);

        $tableInnerJoinStr =
            DataMapper::tableName('order_goods') . ' as og INNER JOIN ' . DataMapper::tableName('order_info')
            . ' as oi ON og.order_id = oi.order_id';

        return $this->_fetchArray(
            $tableInnerJoinStr, //table
            'og.*, oi.user_id, oi.pay_time, oi.add_time, oi.order_sn, ' . // fields
            'oi.consignee, oi.address, oi.mobile',
            $condArray,
            array('order' => 'og.order_id desc, og.rec_id desc'),
            $offset,
            $limit,
            $ttl
        );
    }

}
