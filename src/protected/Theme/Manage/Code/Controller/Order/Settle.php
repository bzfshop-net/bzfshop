<?php

/**
 * @author QiangYu
 *
 * 订单商品结算
 *
 * */

namespace Controller\Order;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;
use Core\Service\Order\Goods as OrderGoodsService;
use Core\Service\Order\Settle as OrderSettleService;
use Core\Service\User\Supplier as UserSupplierService;

class Settle extends \Controller\AuthController
{
    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_settle');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        //表单查询
        $formQuery = array();
        //付款时间
        $payTimeStartStr = $validator->validate('pay_time_start');
        $payTimeStart    = Time::gmStrToTime($payTimeStartStr) ? : null;
        $payTimeEndStr   = $validator->validate('pay_time_end');
        $payTimeEnd      = Time::gmStrToTime($payTimeEndStr) ? : null;
        $suppliers_id    = $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('suppliers_id');

        if (empty($payTimeStart) || empty($payTimeEnd) || empty($suppliers_id)) {
            goto out;
        }

        $formQuery['pay_time']     = array($payTimeStart, $payTimeEnd);
        $formQuery['suppliers_id'] = $suppliers_id;

        // 取得 order_goods 信息
        $condArray   = array();
        $condArray[] = array('oi.order_id = og.order_id');
        //只有 付款订单  和  已经退款 订单需要结算
        $condArray[] = array('og.order_goods_status = 1 or og.order_goods_status = 4');
        //只有发货了的订单才需要结算
        $condArray[] = array('og.shipping_id > 0');
        // 没有结算的订单才需要结算
        $condArray[] = array('og.settle_id = 0');

        // 表单查询
        $condArray = array_merge($condArray, QueryBuilder::buildQueryCondArray($formQuery));

        $orderGoodsService = new OrderGoodsService();
        $orderGoodsArray   = $orderGoodsService->_fetchArray(
            array('order_info' => 'oi', 'order_goods' => 'og'), //table
            'og.*, oi.pay_time',
            // fields
            $condArray,
            array('order' => 'rec_id asc'),
            0,
            $f3->get('sysConfig[max_query_record_count]'),
            0
        ); //最多限制 max_query_record_count 条记录

        // 没有订单，不需要结算
        if (empty($orderGoodsArray)) {
            goto out;
        }

        $goodsIdArray = array();
        // 订单的支付状态显示
        foreach ($orderGoodsArray as &$orderGoodsItem) {
            $orderGoodsItem['order_goods_status_desc'] =
                OrderGoodsService::$orderGoodsStatusDesc[$orderGoodsItem['order_goods_status']];
            $goodsIdArray[]                            = $orderGoodsItem['goods_id'];
        }
        // 前面用了引用，这里一定要清除，防止影响后面的数据
        unset($orderGoodsItem);

        // 取商品的商家备注，结算的时候可能用得着
        $goodsIdArray = array_unique($goodsIdArray);
        $queryArray   = $orderGoodsService->_fetchArray(
            'goods', //table
            'goods_id,seller_note', // fields
            array(array(QueryBuilder::buildInCondition('goods_id', $goodsIdArray))),
            array('order' => 'goods_id asc'),
            0,
            $f3->get('sysConfig[max_query_record_count]'),
            0
        ); //最多限制 max_query_record_count 条记录

        // 建立 goods_id -> seller_note 的反查表
        $goodsIdToSellerNote = array();
        foreach ($queryArray as $queryItem) {
            $goodsIdToSellerNote[$queryItem['goods_id']] = $queryItem['seller_note'];
        }

        // 把 seller_note 放到 orderGoods 中去
        foreach ($orderGoodsArray as &$orderGoodsItem) {
            $orderGoodsItem['seller_note'] = $goodsIdToSellerNote[$orderGoodsItem['goods_id']];
        }
        // 前面用了引用，这里一定要清除，防止影响后面的数据
        unset($orderGoodsItem);

        // 给模板赋值
        $smarty->assign('payTimeStart', $payTimeStart);
        $smarty->assign('payTimeEnd', $payTimeEnd);
        $smarty->assign('suppliers_id', $suppliers_id);
        $smarty->assign('orderGoodsArray', $orderGoodsArray);

        out:
        $smarty->assign('max_query_record_count', $f3->get('sysConfig[max_query_record_count]'));
        $smarty->display('order_settle.tpl');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_settle');

        // 参数验证
        $validator = new Validator($f3->get('POST'));

        $payTimeStart = $validator->required()->digits()->validate('payTimeStart');
        $payTimeEnd   = $validator->required()->digits()->validate('payTimeEnd');
        $suppliers_id = $validator->required()->digits()->validate('suppliers_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        $orderGoodsIdArray = $validator->validate('orderGoodsIdArray');
        $memo              = $validator->validate('memo');

        if (empty($orderGoodsIdArray) || !is_array($orderGoodsIdArray)) {
            $this->addFlashMessage('没有订单需要结算');
            goto out;
        }

        // 取得供货商的信息
        $userSupplierService = new UserSupplierService();
        $supplier            = $userSupplierService->loadSupplierById($suppliers_id);
        if ($supplier->isEmpty()) {
            $this->addFlashMessage('供货商不存在');
            goto out;
        }

        // 取得所有 order_goods 记录
        $orderGoodsService = new OrderGoodsService();
        $orderGoodsArray   = $orderGoodsService->_fetchArray(
            'order_goods', //table
            'rec_id, order_goods_status, goods_number,suppliers_id, suppliers_price, suppliers_shipping_fee, suppliers_refund, shipping_id',
            // fields
            array(array(QueryBuilder::buildInCondition('rec_id', $orderGoodsIdArray))),
            array('order' => 'rec_id asc'),
            0,
            $f3->get('sysConfig[max_query_record_count]'),
            0
        ); //最多限制 max_query_record_count 条记录

        if (empty($orderGoodsArray)) {
            $this->addFlashMessage('没有订单需要结算');
            goto out;
        }

        // 检查订单,计算订单结算金额
        $totalGoodsPrice      = 0;
        $totalShippingFee     = 0;
        $totalRefund          = 0;
        $totalOrderGoodsCount = 0;

        //剔除非法的 orderGoodsId
        $invalidOrderGoodsIdArray = array();
        foreach ($orderGoodsArray as $orderGoodsItem) {
            if (OrderGoodsService::OGS_UNPAY == $orderGoodsItem['order_goods_status'] // 没有付款的订单不能结算
                || $orderGoodsItem['suppliers_id'] != $suppliers_id // 不是这个供货商
                || $orderGoodsItem['shipping_id'] <= 0 // 没有发货的订单
            ) {
                // 非法订单，剔除掉
                $invalidOrderGoodsIdArray[] = $orderGoodsItem['rec_id'];
                continue;
            }

            $totalGoodsPrice += $orderGoodsItem['goods_number'] * $orderGoodsItem['suppliers_price'];
            $totalShippingFee += $orderGoodsItem['suppliers_shipping_fee'];
            $totalRefund += $orderGoodsItem['suppliers_refund'];
            $totalOrderGoodsCount++;
        }

        //剔除非法的 orderGoodsId
        $orderGoodsIdArray = array_diff($orderGoodsIdArray, $invalidOrderGoodsIdArray);

        if (empty($orderGoodsIdArray)) {
            $this->addFlashMessage('没有订单需要结算');
            goto out;
        }

        // 取得当前结算的管理员
        $authAdminUser = AuthHelper::getAuthUser();

        $dbEngine = DataMapper::getDbEngine();
        try {

            // 我们这里需要事务保障
            $dbEngine->begin();

            //创建 order_settle 记录
            $orderSettleService = new OrderSettleService();
            $orderSettle        = $orderSettleService->loadOrderSettleBySettleId(0);

            $orderSettle->user_id                = $authAdminUser['user_id'];
            $orderSettle->user_name              = $authAdminUser['user_name'];
            $orderSettle->settle_start_time      = $payTimeStart;
            $orderSettle->settle_end_time        = $payTimeEnd;
            $orderSettle->suppliers_id           = $suppliers_id;
            $orderSettle->suppliers_name         = $supplier['suppliers_name'];
            $orderSettle->suppliers_goods_price  = $totalGoodsPrice;
            $orderSettle->suppliers_shipping_fee = $totalShippingFee;
            $orderSettle->suppliers_refund       = $totalRefund;
            $orderSettle->create_time            = Time::gmTime();
            $orderSettle->memo                   = $memo;

            $orderSettle->save();

            // 更新 order_goods ，设置上 settle_id
            $sql = "update " . DataMapper::tableName('order_goods') . ' set settle_id = ? where '
                . QueryBuilder::buildInCondition('rec_id', $orderGoodsIdArray);
            $dbEngine->exec($sql, $orderSettle->settle_id);

            $dbEngine->commit();

            $this->addFlashMessage('成功创建结算记录');

        } catch (\Exception $e) {
            $dbEngine->rollback();
            $this->addFlashMessage('数据库读写错误');
        }

        out:
        // 回到结算页面
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    /**
     * 列出结算列表
     *
     * @param $f3
     */
    public function ListSettle($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_settle_listsettle');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        // 表单查询
        $formQuery = array();
        //结算时间
        $settleTimeStartStr = $validator->validate('settle_time_start');
        $settleTimeStart    = Time::gmStrToTime($settleTimeStartStr) ? : null;
        $settleTimeEndStr   = $validator->validate('settle_time_end');
        $settleTimeEnd      = Time::gmStrToTime($settleTimeEndStr) ? : null;
        //供货商
        $suppliers_id = $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('suppliers_id');

        $formQuery['create_time']  = array($settleTimeStart, $settleTimeEnd);
        $formQuery['suppliers_id'] = $suppliers_id;

        //是否已经付款
        $is_pay = $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('is_pay');

        switch ($is_pay) {
            case 1:
                $formQuery['pay_time'] = 0;
                break;
            case 2:
                $formQuery['pay_time'] = array($is_pay, null);
                break;
            default:
                break;
        }

        // 构建查询条件
        $condArray = null;
        if (!empty($formQuery)) {
            $condArray = QueryBuilder::buildQueryCondArray($formQuery);
        }

        // 查询结算列表
        $orderSettleService = new OrderSettleService();
        $totalCount         = $orderSettleService->countOrderSettleArray($condArray);
        if ($totalCount <= 0) { // 没商品，可以直接退出了
            goto out;
        }

        // 页数超过最大值，返回
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Order/Settle/ListSettle');
        }

        // 结算列表
        $orderSettleArray = $orderSettleService->fetchOrderSettleArray($condArray, $pageNo * $pageSize, $pageSize);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('orderSettleArray', $orderSettleArray);

        out:
        $smarty->display('order_settle_listsettle.tpl');
    }

    /**
     * 列出结算对应的订单明细
     *
     * @param $f3
     */
    public function ListOrderGoods($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_settle_listordergoods');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $settle_id = $validator->required('结算ID非法')->digits('结算ID非法')->min(1, true, '结算ID非法')->validate('settle_id');
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        $orderGoodsService = new OrderGoodsService();

        $totalCount = $orderGoodsService->countOrderGoodsArray(array(array('settle_id = ?', $settle_id)));
        if ($totalCount <= 0) { // 没订单，可以直接退出了
            goto out;
        }

        // 页数超过最大值，返回结算列表
        if ($pageNo * $pageSize >= $totalCount) {
            goto out_fail;
        }

        // 取得订单列表
        $orderGoodsArray = $orderGoodsService->fetchOrderGoodsArray(
            array(array('settle_id = ?', $settle_id)),
            $pageNo * $pageSize,
            $pageSize
        );

        // 转换状态显示
        foreach ($orderGoodsArray as &$orderGoodsItem) {
            $orderGoodsItem['order_goods_status_desc'] =
                OrderGoodsService::$orderGoodsStatusDesc[$orderGoodsItem['order_goods_status']];
        }
        unset($orderGoodsItem);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('orderGoodsArray', $orderGoodsArray);

        out:
        $smarty->display('order_settle_listordergoods.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Order/Settle/ListSettle');
    }

    /**
     * 显示结算记录详情
     *
     * @param $f3
     */
    public function ajaxDetail($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_settle_ajaxdetail');

        global $smarty;

        $errorMessage = '';
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $settle_id = $validator->required()->digits()->min(1)->validate('settle_id');
        if (!$this->validate($validator)) {
            $errorMessage = '结算ID非法';
            goto out;
        }

        // 加载 order_settle 记录
        $orderSettleService = new OrderSettleService();
        $orderSettle        = $orderSettleService->loadOrderSettleBySettleId($settle_id);

        if ($orderSettle->isEmpty()) {
            $errorMessage = '结算ID非法';
            goto out;
        }

        $smarty->assign('settle_id', $settle_id);
        $smarty->assign('orderSettle', $orderSettle);

        out:
        $smarty->assign('errorMessage', $errorMessage);
        $smarty->display('order_settle_ajaxdetail.tpl');
    }

    /**
     * 更新结算详情记录
     *
     * @param $f3
     */
    public function Update($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_settle_update');

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $settle_id = $validator->required()->digits()->min(1)->validate('settle_id');
        if (!$this->validate($validator)) {
            $this->addFlashMessage('结算ID非法');
            goto out;
        }

        // 加载 order_settle 记录
        $orderSettleService = new OrderSettleService();
        $orderSettle        = $orderSettleService->loadOrderSettleBySettleId($settle_id);

        if ($orderSettle->isEmpty()) {
            $this->addFlashMessage('结算ID非法');
            goto out;
        }

        // 表单验证
        $validator             = new Validator($f3->get('POST[orderSettle]'));
        $orderSettle->pay_type = $validator->validate('pay_type');
        $orderSettle->pay_no   = $validator->validate('pay_no');
        $orderSettle->pay_time = Time::gmStrToTime($validator->validate('pay_time'));
        $orderSettle->memo     = $validator->validate('memo');
        $orderSettle->save();

        $this->addFlashMessage('结算记录设置成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

}
