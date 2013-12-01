<?php

/**
 *
 * @author QiangYu
 *
 * 订单的基本操作
 *
 * */

namespace Core\Service\Order;

use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;

class Order extends \Core\Service\BaseService
{

    public static $orderSnPrefix = '';

    /** 订单状态 order_info 中 order_status 字段 */

    const OS_UNCONFIRMED = 0, // 未确认
        OS_CONFIRMED     = 1, // 已确认
        OS_CANCELED      = 2, // 已取消
        OS_INVALID       = 3, // 无效
        OS_RETURNED      = 4, // 退货
        OS_SPLITED       = 5, // 已分单
        OS_SPLITING_PART = 6; // 部分分单

    public static $orderStatusDesc = array(
        Order::OS_UNCONFIRMED   => '未确认',
        Order::OS_CONFIRMED     => '已确认',
        Order::OS_CANCELED      => '已取消',
        Order::OS_INVALID       => '无效',
        Order::OS_RETURNED      => '退货',
        Order::OS_SPLITED       => '已分单',
        Order::OS_SPLITING_PART => '部分分单',
    );

    /** order_info 中 pay_status 字段 */

    const PS_UNPAYED = 0, // 未付款
        PS_PAYING    = 1, //付款中
        PS_PAYED     = 2; // 已付款

    public static $payStatusDesc = array(
        Order::PS_UNPAYED => '未付款',
        Order::PS_PAYING  => '付款中',
        Order::PS_PAYED   => '已付款',
    );

    /**
     * 生成订单的 SN 号码
     *
     * @return string SN 号码
     */
    public static function generateOrderSn()
    {
        //mt_srand((double) microtime() * 1000000);
        return Order::$orderSnPrefix . Time::localTimeStr('YmdHis');
    }

    /**
     * 根据订单 ID 取得订单信息
     *
     * @return object order_info 的详细信息
     *
     * @param int $id  订单数字 ID
     * @param int $ttl 缓存时间
     */
    public function loadOrderInfoById($id, $ttl = 0)
    {
        return $this->_loadById('order_info', 'order_id=?', $id, $ttl);
    }

    /**
     * 保存 order_info 信息，如果没有 $id 则创建记录
     *
     * @return object order_info 对象
     *
     * @param int   $id              order_info 的数字 id : order_id
     * @param array $orderInfoArray  orderInfo 的数据数组
     */
    public function saveOrderInfo($id, array $orderInfoArray)
    {
        // 参数验证
        $validator      = new Validator(array('id' => $id, 'orderInfoArray' => $orderInfoArray), '');
        $id             = $validator->digits()->min(1)->validate('id');
        $orderInfoArray = $validator->required()->requireArray(false)->validate('orderInfoArray');
        $this->validate($validator);

        $orderInfo = $this->loadOrderInfoById($id);

        //清除对主键的修改
        unset($orderInfoArray['order_id']);

        $orderInfo->copyFrom($orderInfoArray);
        $orderInfo->update_time = Time::gmTime(); // 记录更新时间
        $orderInfo->save();
        return $orderInfo;
    }

    /**
     * 根据 ID 取得 order_goods 信息
     *
     * @return object order_goods 的详细信息
     *
     * @param int $id  数字 ID
     * @param int $ttl 缓存时间
     */
    public function loadOrderGoodsById($id, $ttl = 0)
    {
        return $this->_loadById('order_goods', 'rec_id=?', $id, $ttl);
    }

    /**
     * 更新 goods 中用户购买商品的数量
     *
     * @param $goods_id
     * @param $oldBuyNumber
     * @param $newBuyNumber
     */
    public function updateGoodsUserBuyCount($goods_id, $oldBuyNumber, $newBuyNumber)
    {
        if ($oldBuyNumber != $newBuyNumber) {
            // 用户修改了购买数量，我们需要更新 goods 中对应的 user_buy_number
            $sql      = 'UPDATE ' . DataMapper::tableName('goods')
                . ' set user_buy_number = user_buy_number + (' . ($newBuyNumber - $oldBuyNumber) . ')'
                . ' where goods_id = ? limit 1';
            $dbEngine = DataMapper::getDbEngine();
            $dbEngine->exec($sql, $goods_id);
        }
    }

    /**
     * 保存 order_goods 信息，如果没有 $id 则创建记录
     *
     * @return object order_goods 对象
     *
     * @param int   $id              order_goods 的数字 id : rec_id
     * @param int   $orderId         订单 ID , order_info 中的字段
     * @param array $orderGoodsInfo  orderGoods 的数据数组
     */
    public function saveOrderGoods($id, $orderId, array $orderGoodsInfo)
    {
        // 参数验证
        $validator      =
            new Validator(array('id' => $id, 'orderId' => $orderId, 'orderGoodsInfo' => $orderGoodsInfo), '');
        $id             = $validator->digits()->min(0)->validate('id');
        $orderId        = $validator->required()->digits()->min(1)->validate('orderId');
        $orderGoodsInfo = $validator->required()->requireArray(false)->validate('orderGoodsInfo');
        $this->validate($validator);

        $orderGoods = $this->loadOrderGoodsById($id);
        if ($orderGoods->isEmpty()) {
            // 新记录
            $orderGoods->create_time = Time::gmTime();
            $orderGoods->update_time = $orderGoods->create_time;
        } else {
            // 更新记录
            $orderGoods->update_time = Time::gmTime();
        }

        // 更新 goods 中对应的 user_buy_number
        $oldBuyNumber = intval($orderGoods->goods_number);
        $newBuyNumber =
            array_key_exists('goods_number', $orderGoodsInfo) ? intval($orderGoodsInfo['goods_number']) : $oldBuyNumber;
        $this->updateGoodsUserBuyCount(
            $orderGoodsInfo['goods_id'],
            $oldBuyNumber,
            $newBuyNumber
        );

        //清除对主键的修改
        unset($orderGoodsInfo['rec_id']);
        unset($orderGoodsInfo['order_id']);

        // 商品货号如果已经有了，就不允许修改
        if (!empty($orderGoods->goods_sn)) {
            unset($orderGoodsInfo['goods_sn']);
        }

        //保存记录到数据库
        $orderGoods->copyFrom($orderGoodsInfo);
        $orderGoods->order_id = $orderId;
        $orderGoods->save();
        return $orderGoods;
    }

    /**
     * 根据订单 ID 取得对应的所有 order_goods 信息
     *
     * @return array order_goods 的详细信息
     *
     * @param int $orderId 订单ID
     * @param int $ttl     缓存时间
     */
    public function fetchOrderGoodsArray($orderId, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('orderId' => $orderId));
        $orderId   = $validator->required()->digits()->min(1)->validate('orderId');
        $this->validate($validator);

        return $this->_fetchArray(
            'order_goods',
            '*',
            array(array('order_id=?', $orderId)),
            array('order' => 'rec_id asc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 删除 order_info 下面对应的所有 order_goods 记录
     *
     * @param int $orderId 订单ID号
     *
     */
    public function removeAllOrderGoods($orderId)
    {
        // 参数验证
        $validator = new Validator(array('orderId' => $orderId));
        $orderId   = $validator->required()->digits()->min(1)->validate('orderId');
        $this->validate($validator);

        $dbEngine = DataMapper::getDbEngine();
        $dbEngine->exec(
            'DELETE from ' . DataMapper::tableName('order_goods') . ' where order_id = ' . $orderId
        );
    }

    /**
     * 删除订单 order_info 已经后面对应的所有 order_goods 记录
     *
     * @return boolean 成功 true，失败 false
     *
     * @param int $orderId 订单ID号
     *
     */
    public function removeOrderInfo($orderId)
    {
        // 参数验证
        $validator = new Validator(array('orderId' => $orderId));
        $orderId   = $validator->required()->digits()->min(1)->validate('orderId');
        $this->validate($validator);

        $orderInfo = $this->loadOrderInfoById($orderId);

        if (Order::OS_UNCONFIRMED != $orderInfo['order_status']) {
            //只有 OS_UNCONFIRMED 的订单才允许删除
            return false;
        }

        $dbEngine = DataMapper::getDbEngine();
        try {
            $dbEngine->begin();

            // 先删除所有的 order_goods 记录
            $this->removeAllOrderGoods($orderId);
            // 删除 orderInfo 记录 
            $orderInfo->erase();

            $dbEngine->commit();
        } catch (Exception $e) {
            $dbEngine->rollback();
            return false;
        }

        return true;
    }

    /**
     * 删除 order_goods 记录
     *
     * @param int     $id   记录的 rec_id 值
     * @param boolean $safe 安全删除，只有订单是未付款模式才可以删除
     */
    public function removeOrderGoods($id, $safe = true)
    {
        // 参数验证
        $validator = new Validator(array('id' => $id));
        $id        = $validator->required()->digits()->min(1)->validate('id');
        $this->validate($validator);

        $orderGoods = $this->loadOrderGoodsById($id);
        if (!empty($orderGoods) && !$orderGoods->isEmpty()) {

            // safe 模式下，只允许删除未付款的订单
            if ($safe && Goods::OGS_UNPAY != $orderGoods->order_goods_status) {
                return;
            }

            // 更新 goods 中的 user_buy_number
            $this->updateGoodsUserBuyCount($orderGoods->goods_id, $orderGoods->goods_number, 0);
            // 删除记录
            $orderGoods->erase();
        }
    }

    /**
     * 取得用户的订单列表
     *
     * @return array 格式 array(array('key'=>'value', 'key'=>'value', ...))
     *
     * @param int $userId      用户 ID
     * @param int $orderStatus 订单的状态
     * @param int $ttl         缓存多少时间
     */
    public function countUserOrderInfo($userId, $orderStatus, $ttl = 0)
    {
        //参数数组
        $validatorArray           = array();
        $validatorArray['userId'] = $userId;
        if (isset($orderStatus)) {
            $validatorArray['orderStatus'] = $orderStatus;
        }
        // 参数验证
        $validator = new Validator($validatorArray);
        $userId    = $validator->required()->digits()->min(1)->validate('userId');
        if (isset($orderStatus)) {
            $orderStatus = $validator->digits()->min(0)->validate('orderStatus');
        }

        $this->validate($validator);

        return $this->_countArray(
            'order_info', // table
            array(array(' user_id = ' . $userId . (isset($orderStatus) ? ' and order_status = ' . $orderStatus : ''))),
            // filter
            null,
            $ttl
        );
    }

    /**
     * 取得用户的订单列表
     *
     * @return array 格式 array(array('key'=>'value', 'key'=>'value', ...))
     *
     * @param int $userId      用户 ID
     * @param int $orderStatus 订单的状态
     * @param int $offset      用于分页的开始 >= 0
     * @param int $limit       每页多少条
     * @param int $ttl         缓存多少时间
     */
    public function fetchUserOrderInfoArray($userId, $orderStatus, $offset = 0, $limit = 10, $ttl = 0)
    {
        //参数数组
        $validatorArray           = array();
        $validatorArray['userId'] = $userId;
        if (isset($orderStatus)) {
            $validatorArray['orderStatus'] = $orderStatus;
        }
        // 参数验证
        $validator = new Validator($validatorArray);
        $userId    = $validator->required()->digits()->min(1)->validate('userId');
        if (isset($orderStatus)) {
            $orderStatus = $validator->digits()->min(0)->validate('orderStatus');
        }

        $this->validate($validator);

        return $this->_fetchArray(
            'order_info',
            '*', // table, fields
            array(array(' user_id = ' . $userId . (isset($orderStatus) ? ' and order_status = ' . $orderStatus : ''))),
            // filter
            array('order' => 'order_id desc'), // options,
            $offset,
            $limit,
            $ttl
        );
    }

}
