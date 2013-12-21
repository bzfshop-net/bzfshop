<?php

/**
 * @author QiangYu
 *
 * 订单 order_goods 操作
 *
 * */

namespace Controller\Order;

use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Search\SearchHelper;
use Core\Service\Meta\Express as ExpressService;
use Core\Service\Order\Action as OrderActionService;
use Core\Service\Order\Goods as OrderGoodsService;
use Core\Service\Order\Order as OrderBasicService;

class Goods extends \Controller\AuthController
{

    public function Search($f3)
    {
        /**
         * 我们使用搜索模块做搜索操作
         */
        $searchFieldSelector = 'og.*, oi.pay_time, oi.system_id';

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

        //表单查询
        $searchFormQuery                          = array();
        $searchFormQuery['og.order_id']           =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('order_id');
        $searchFormQuery['og.rec_id']             =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('rec_id');
        $searchFormQuery['og.goods_id']           =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('goods_id');
        $searchFormQuery['og.order_goods_status'] =
            $validator->min(1)->filter('ValidatorIntValue')->validate('order_goods_status');
        $searchFormQuery['og.goods_sn']           = $validator->validate('goods_sn');

        $searchFormQuery['oi.consignee']  = $validator->validate('consignee');
        $searchFormQuery['oi.mobile']     = $validator->validate('mobile');
        $searchFormQuery['oi.address']    = $validator->validate('address');
        $searchFormQuery['oi.postscript'] = $validator->validate('postscript');

        $searchFormQuery['og.memo'] = $validator->validate('memo');

        //下单时间
        $addTimeStartStr                = $validator->validate('add_time_start');
        $addTimeStart                   = Time::gmStrToTime($addTimeStartStr) ? : null;
        $addTimeEndStr                  = $validator->validate('add_time_end');
        $addTimeEnd                     = Time::gmStrToTime($addTimeEndStr) ? : null;
        $searchFormQuery['oi.add_time'] = array($addTimeStart, $addTimeEnd);

        //付款时间
        $payTimeStartStr                = $validator->validate('pay_time_start');
        $payTimeStart                   = Time::gmStrToTime($payTimeStartStr) ? : null;
        $payTimeEndStr                  = $validator->validate('pay_time_end');
        $payTimeEnd                     = Time::gmStrToTime($payTimeEndStr) ? : null;
        $searchFormQuery['oi.pay_time'] = array($payTimeStart, $payTimeEnd);

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 构造查询条件
        $authSupplierUser = AuthHelper::getAuthUser();

        // 供货商只能查看自己商品对应的订单
        $searchFormQuery['og.suppliers_id'] = intval($authSupplierUser['suppliers_id']);

        $searchParamArray = array();
        // 供货商只能查看已经付款的订单
        $searchParamArray[] = array('og.order_goods_status > 0');

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

        // 查询订单列表
        $totalCount = SearchHelper::count(SearchHelper::Module_OrderGoodsOrderInfo, $searchParamArray);
        if ($totalCount <= 0) { // 没订单，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Order/Goods/Search');
        }

        // 订单列表
        $orderGoodsArray = SearchHelper::search(
            SearchHelper::Module_OrderGoodsOrderInfo,
            $searchFieldSelector,
            $searchParamArray,
            array(array('oi.pay_time', 'desc')),
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
        $smarty->display('order_goods_search.tpl');
    }

    /**
     * 显示订单详情
     */
    public function ajaxDetail($f3)
    {
        global $smarty;

        $errorMessage = '';
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $rec_id    = $validator->required()->digits()->min(1)->validate('rec_id');
        if (!$this->validate($validator)) {
            $errorMessage = '订单ID非法';
            goto out_fail;
        }

        // 取 order_goods
        $orderBasicService = new OrderBasicService();
        $orderGoods        = $orderBasicService->loadOrderGoodsById($rec_id);
        if ($orderGoods->isEmpty() || $orderGoods['order_id'] <= 0) {
            $errorMessage = '订单ID非法';
            goto out_fail;
        }

        // 权限检查
        $authUser = AuthHelper::getAuthUser();
        if ($orderGoods['suppliers_id'] !== $authUser['suppliers_id']) {
            $errorMessage = '订单ID非法';
            goto out_fail;
        }
        $orderGoods['order_goods_status_desc'] =
            OrderGoodsService::$orderGoodsStatusDesc[$orderGoods['order_goods_status']];

        // 取 order_info
        $orderInfo = $orderBasicService->loadOrderInfoById($orderGoods['order_id']);
        if ($orderInfo->isEmpty()) {
            $errorMessage = '订单ID非法';
            goto out_fail;
        }

        // 给模板赋值
        $smarty->assign('orderGoods', $orderGoods);
        $smarty->assign('orderInfo', $orderInfo);

        out_fail:
        $smarty->assign('errorMessage', $errorMessage);

        out_display:
        global $f3;
        $f3->expire(60); // 客户端缓存1分钟
        $smarty->display('order_goods_ajaxdetail.tpl');
    }

    /**
     * 设置订单的快递信息
     *
     * @param $f3
     */
    public function ajaxUpdate($f3)
    {
        // 参数验证
        $validator = new Validator($f3->get('POST'));

        $rec_id      = $validator->required('子订单ID不能为空')->digits('子订单ID必须是数字')->min(1)->validate('rec_id');
        $shipping_id = $validator->digits('快递ID必须是数字')->min(1)->validate('shipping_id');
        $shipping_no = $validator->validate('shipping_no');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 取 order_goods
        $orderBasicService = new OrderBasicService();
        $orderGoods        = $orderBasicService->loadOrderGoodsById($rec_id);
        if ($orderGoods->isEmpty()) {
            $this->addFlashMessage('非法订单');
            goto out_fail;
        }

        // 权限检查
        $authSupplierUser = AuthHelper::getAuthUser();
        if ($orderGoods['suppliers_id'] !== $authSupplierUser['suppliers_id']) {
            $this->addFlashMessage('非法订单');
            goto out_fail;
        }

        // 取得快递名
        if ($shipping_id > 0) {
            //取得快递信息
            $expressService = new ExpressService();
            $expressInfo    = $expressService->loadMetaById($shipping_id);
            if ($expressInfo->isEmpty() || ExpressService::META_TYPE != $expressInfo['meta_type']) {
                $this->addFlashMessage('快递ID非法');
                goto out_fail;
            }
            $shipping_name = $expressInfo['meta_name'];
        } else {
            $shipping_name = null;
        }

        // 更新快递信息
        $orderGoods->shipping_id   = $shipping_id;
        $orderGoods->shipping_name = $shipping_name;
        $orderGoods->shipping_no   = $shipping_no;
        $orderGoods->save();

        // 更新 order_info 的 update_time 字段
        $orderInfo              = $orderBasicService->loadOrderInfoById($orderGoods['order_id']);
        $orderInfo->update_time = Time::gmTime();
        $orderInfo->save();

        // 添加订单操作日志
        if ($shipping_id > 0) {
            $action_note = '' . $shipping_id . ',' . $shipping_name . ',' . $shipping_no;
        } else {
            $action_note = '删除快递信息';
        }

        $orderActionService = new OrderActionService();
        $orderActionService->logOrderAction(
            $orderGoods['order_id'],
            $orderGoods['rec_id'],
            $orderInfo['order_status'],
            $orderInfo['pay_status'],
            $orderGoods['order_goods_status'],
            $action_note,
            '供货商：[' . $authSupplierUser['suppliers_id'] . ']' . $authSupplierUser['suppliers_name'],
            0,
            $orderInfo['shipping_status']
        );

        Ajax::header();
        echo Ajax::buildResult(null, null, null);
        return; // 成功从这里返回

        out_fail: // 失败从这里退出
        Ajax::header();
        $errorMessage = '';
        foreach ($this->flashMessageArray as $messageItem) {
            $errorMessage .= $messageItem . ',';
        }
        echo Ajax::buildResult(-1, $errorMessage, null);
    }

}
