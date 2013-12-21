<?php

/**
 * @author QiangYu
 *
 * 订单的退款处理
 *
 * */

namespace Controller\Order;


use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Search\SearchHelper;
use Core\Service\Order\Action as OrderActionService;
use Core\Service\Order\Goods as OrderGoodsService;
use Core\Service\Order\Order as OrderBasicService;

class Refund extends \Controller\AuthController
{

    public function Search($f3)
    {
        /**
         * 我们使用搜索模块做搜索操作
         */
        $searchFieldSelector = 'og.*, oi.system_id, oi.consignee';

        global $smarty;
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        // shippingStatus, 0 全部，1 已发货，2 未发货
        $shippingStatus = $validator->digits()->min(1)->validate('shippingStatus');

        //订单表单查询
        $searchFormQuery                  = array();
        $searchFormQuery['og.order_id']   =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('order_id');
        $searchFormQuery['og.rec_id']     =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('rec_id');
        $searchFormQuery['og.goods_id']   =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('goods_id');
        $searchFormQuery['og.goods_name'] = $validator->validate('goods_name');
        $searchFormQuery['oi.order_sn']   = $validator->validate('order_sn');

        $order_goods_status =
            $validator->min(0)->filter('ValidatorIntValue')->validate('order_goods_status');

        if (null == $order_goods_status) {
            // 选择 请求退款、退款中、退款完成 状态的订单
            $searchFormQuery['og.order_goods_status'] = array('>', OrderGoodsService::OGS_PAY);
        } else {
            $searchFormQuery['og.order_goods_status'] = $order_goods_status;
        }

        $searchFormQuery['oi.consignee']  = $validator->validate('consignee');
        $searchFormQuery['oi.mobile']     = $validator->validate('mobile');
        $searchFormQuery['oi.address']    = $validator->validate('address');
        $searchFormQuery['oi.postscript'] = $validator->validate('postscript');
        $searchFormQuery['og.memo']       = $validator->validate('memo');

        //退款时间
        $refundTimeStartStr                = $validator->validate('refund_time_start');
        $refundTimeStart                   = Time::gmStrToTime($refundTimeStartStr) ? : null;
        $refundTimeEndStr                  = $validator->validate('refund_time_end');
        $refundTimeEnd                     = Time::gmStrToTime($refundTimeEndStr) ? : null;
        $searchFormQuery['og.refund_time'] = array($refundTimeStart, $refundTimeEnd);

        //完成时间
        $refundFinishTimeStartStr                 = $validator->validate('refund_finish_time_start');
        $refundFinishTimeStart                    = Time::gmStrToTime($refundFinishTimeStartStr) ? : null;
        $refundFinishTimeEndStr                   = $validator->validate('refund_finish_time_end');
        $refundFinishTimeEnd                      = Time::gmStrToTime($refundFinishTimeEndStr) ? : null;
        $searchFormQuery['og.refund_finish_time'] = array($refundFinishTimeStart, $refundFinishTimeEnd);

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 构造查询条件
        $authSupplierUser = AuthHelper::getAuthUser();

        // 供货商只能查看自己商品对应的订单
        $searchFormQuery['og.suppliers_id'] = intval($authSupplierUser['suppliers_id']);

        // 构造查询条件
        $searchParamArray = array();

        // 用户取消的订单商品我们就不再显示了
        $searchParamArray[] = array(
            QueryBuilder::buildNotInCondition(
                'oi.order_status',
                array(OrderBasicService::OS_CANCELED, OrderBasicService::OS_INVALID),
                \PDO::PARAM_INT
            )
        );

        if (1 == $shippingStatus) {
            // 1 未发货
            $searchParamArray[] = array('og.shipping_id = 0');
        } elseif (2 == $shippingStatus) {
            // 2 已发货
            $searchParamArray[] = array('og.shipping_id <> 0');
        } else {
            // do nothing
        }

        // 表单查询
        $searchParamArray = array_merge($searchParamArray, QueryBuilder::buildSearchParamArray($searchFormQuery));

        // 使用哪个搜索模块
        $searchModule = SearchHelper::Module_OrderGoodsOrderInfo;

        // 查询订单列表
        $totalCount = SearchHelper::count($searchModule, $searchParamArray);
        if ($totalCount <= 0) { // 没订单，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Order/Refund/Search');
        }

        // 查询订单列表
        $orderGoodsArray = SearchHelper::search(
            $searchModule,
            $searchFieldSelector,
            $searchParamArray,
            array(array('og.order_id', 'desc')),
            $pageNo * $pageSize,
            $pageSize
        );

        // 订单的支付状态显示
        foreach ($orderGoodsArray as &$orderGoodsItem) {
            $orderGoodsItem['order_goods_status_desc'] =
                OrderGoodsService::$orderGoodsStatusDesc[$orderGoodsItem['order_goods_status']];
        }
        // 前面用了引用，这里一定要清除，防止影响后面的数据
        unset($orderGoodsItem);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('orderGoodsArray', $orderGoodsArray);

        out_display:
        $smarty->display('order_refund_search.tpl');
    }


    /**
     * 设置订单为 退款中 状态
     *
     * 即：确认已经收到用户的退货了，告知财务这个订单可以给用户退款了
     *
     * @param $f3
     */
    public function SetRefund($f3)
    {
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $rec_id    = $validator->required()->digits()->min(1)->validate('rec_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        // 取得当前供货商
        $authSupplierUser = AuthHelper::getAuthUser();

        $orderBasicService = new OrderBasicService();
        $orderGoods        = $orderBasicService->loadOrderGoodsById($rec_id);
        if ($orderGoods->isEmpty()
            || OrderGoodsService::OGS_ASKREFUND != $orderGoods->order_goods_status
            || $orderGoods['suppliers_id'] != $authSupplierUser['suppliers_id'] // 供货商只能查看自己商品对应的订单
        ) {
            $this->addFlashMessage('订单ID非法');
            goto out;
        }

        $orderInfo = $orderBasicService->loadOrderInfoById($orderGoods['order_id']);
        if ($orderInfo->isEmpty() || $orderInfo->pay_status != OrderBasicService::PS_PAYED) {
            $this->addFlashMessage('订单ID非法');
            goto out;
        }

        // 设置订单状态为  退款中
        $orderGoods->order_goods_status = OrderGoodsService::OGS_REFUNDING;
        $orderGoods->save();

        // 更新 order_info 的 update_time 字段
        $orderInfo->update_time = Time::gmTime();
        $orderInfo->save();

        $action_note = '设置为退款中' . "\n";
        $action_note .= '操作人：[' . $authSupplierUser['suppliers_id'] . ']' . $authSupplierUser['suppliers_name'] . "\n";

        // 添加订单操作日志
        $orderActionService = new OrderActionService();
        $orderActionService->logOrderAction(
            $orderGoods['order_id'],
            $orderGoods['rec_id'],
            $orderInfo['order_status'],
            $orderInfo['pay_status'],
            $orderGoods['order_goods_status'],
            $action_note,
            $authSupplierUser['suppliers_name'],
            0,
            $orderInfo['shipping_status']
        );

        $this->addFlashMessage('订单状态设置为[退款中]');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

}
