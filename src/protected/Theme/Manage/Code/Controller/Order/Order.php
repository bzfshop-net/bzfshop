<?php

/**
 * @author QiangYu
 *
 * 订单列表操作
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
use Core\Service\Order\Payment as OrderPaymentService;
use Core\Service\Order\Refer as OrderReferService;
use Core\Service\User\User as UserBasicService;

class Order extends \Controller\AuthController
{

    public function Search($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_order_search');

        /**
         * 我们使用搜索模块做搜索操作
         */
        $searchFieldSelector =
            'oi.order_id, oi.order_sn, oi.add_time, oi.confirm_time, oi.user_id, oi.order_status, oi.pay_status'
            . ', oi.goods_amount, oi.shipping_fee, oi.discount, oi.system_id, oi.kefu_user_id, oi.kefu_user_name, oi.kefu_user_rate';

        global $smarty;
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        // 构造查询条件
        $searchParamArray = array();

        //表单查询
        $searchFormQuery                    = array();
        $searchFormQuery['oi.order_id']     =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('order_id');
        $searchFormQuery['oi.order_sn']     = $validator->validate('order_sn');
        $searchFormQuery['oi.pay_no']       = $validator->validate('pay_no');
        $searchFormQuery['oi.consignee']    = $validator->validate('consignee');
        $searchFormQuery['oi.mobile']       = $validator->validate('mobile');
        $searchFormQuery['oi.address']      = $validator->validate('address');
        $searchFormQuery['oi.postscript']   = $validator->validate('postscript');
        $searchFormQuery['oi.system_id']    = array('=', $validator->validate('system_id'));
        $searchFormQuery['oi.kefu_user_id'] = $validator->filter('ValidatorIntValue')->validate('kefu_user_id');

        $utmSource = $validator->validate('utm_source');
        if ('SELF' == $utmSource) {
            $searchParamArray[] = array('orf.utm_source is null');
        } else {
            $searchFormQuery['orf.utm_source'] = array('=', $utmSource);
        }

        $utmMedium = $validator->validate('utm_medium');
        if ('SELF' == $utmMedium) {
            $searchParamArray[] = array('orf.utm_medium is null');
        } else {
            $searchFormQuery['orf.utm_medium'] = array('=', $utmMedium);
        }

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
        $searchParamArray = array_merge($searchParamArray, QueryBuilder::buildSearchParamArray($searchFormQuery));

        // 使用哪个搜索模块
        $searchModule = SearchHelper::Module_OrderInfo;
        if (!empty($utmSource) || !empty($utmMedium)) {
            $searchModule = SearchHelper::Module_OrderInfoOrderRefer;
        }

        $totalCount = SearchHelper::count($searchModule, $searchParamArray);
        if ($totalCount <= 0) { // 没订单，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Order/Order/Search');
        }

        // 订单列表
        $orderInfoArray = SearchHelper::search(
            $searchModule,
            $searchFieldSelector,
            $searchParamArray,
            array(array('oi.order_id', 'desc')),
            $pageNo * $pageSize,
            $pageSize
        );

        // 订单的支付状态显示
        foreach ($orderInfoArray as &$orderInfoItem) {
            $orderInfoItem['order_status_desc'] = OrderBasicService::$orderStatusDesc[$orderInfoItem['order_status']];
            $orderInfoItem['pay_status_desc']   = OrderBasicService::$payStatusDesc[$orderInfoItem['pay_status']];
        }
        // 前面用了引用，这里一定要清除，防止影响后面的数据
        unset($orderInfoItem);

        // 取得用户 id 列表
        $userIdArray = array();
        foreach ($orderInfoArray as $orderInfoItem) {
            $userIdArray[] = $orderInfoItem['user_id'];
        }
        $userIdArray = array_unique($userIdArray);

        //取得用户信息
        $userBasicService = new UserBasicService();
        $userArray        = $userBasicService->fetchUserArrayByUserIdArray($userIdArray);

        // 建立 user_id --> user 的反查表，方便快速查询
        $userIdToUserArray = array();
        foreach ($userArray as $user) {
            $userIdToUserArray[$user['user_id']] = $user;
        }

        // 放入用户信息
        foreach ($orderInfoArray as &$orderInfoItem) {
            if (isset($userIdToUserArray[$orderInfoItem['user_id']])) {
                // 很老的订单，用户可能被删除了
                $orderInfoItem['user_name'] = $userIdToUserArray[$orderInfoItem['user_id']]['user_name'];
                $orderInfoItem['email']     = $userIdToUserArray[$orderInfoItem['user_id']]['email'];
            }
        }
        // 前面用了引用，这里一定要清除，防止影响后面的数据
        unset($orderInfoItem);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('orderInfoArray', $orderInfoArray);

        out_display:
        $smarty->display('order_order_search.tpl');
    }

    /**
     * 订单详情
     */
    public function Detail($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_order_detail');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $order_id  = $validator->required('订单ID非法')->digits('订单ID非法')->min(1)->validate('order_id');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 查询订单
        $orderBasicService = new OrderBasicService();
        $orderInfo         = $orderBasicService->loadOrderInfoById($order_id);

        if ($orderInfo->isEmpty()) {
            $this->addFlashMessage('订单ID非法');
            goto out_fail;
        }

        $orderInfo['order_status_desc'] = OrderBasicService::$orderStatusDesc[$orderInfo['order_status']];
        $orderInfo['pay_status_desc']   = OrderBasicService::$payStatusDesc[$orderInfo['pay_status']];
        $orderGoodsArray                = $orderBasicService->fetchOrderGoodsArray($order_id);
        // 转换状态显示
        foreach ($orderGoodsArray as &$orderGoodsItem) {
            $orderGoodsItem['order_goods_status_desc'] =
                OrderGoodsService::$orderGoodsStatusDesc[$orderGoodsItem['order_goods_status']];
        }
        unset($orderGoodsItem);

        // 取订单来源信息
        $orderReferService = new OrderReferService();
        $orderRefer        = $orderReferService->loadOrderReferByOrderId($orderInfo['order_id'], 300); //缓存5分钟

        // 取用户账户
        $userBasicService = new UserBasicService();
        $userInfo         = $userBasicService->loadUserById($orderInfo['user_id']);

        // 取得订单的操作日志
        $orderActionService = new OrderActionService();
        $orderLogArray      = $orderActionService->fetchOrderLogArray($order_id, 0);

        // 状态字段转换成可以显示的字符串
        foreach ($orderLogArray as &$orderLog) {
            $orderLog['order_status']       = OrderBasicService::$orderStatusDesc[$orderLog['order_status']];
            $orderLog['pay_status']         = OrderBasicService::$payStatusDesc[$orderLog['pay_status']];
            $orderLog['order_goods_status'] = OrderGoodsService::$orderGoodsStatusDesc[$orderLog['order_goods_status']];
            $orderLog['action_note']        = nl2br($orderLog['action_note']);
        }
        unset($orderLog);

        // 给模板赋值
        $smarty->assign('orderInfo', $orderInfo);
        $smarty->assign('orderRefer', $orderRefer);
        $smarty->assign('userInfo', $userInfo);
        $smarty->assign('orderGoodsArray', $orderGoodsArray);
        $smarty->assign('orderLogArray', $orderLogArray);

        $smarty->display('order_order_detail.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Order/Order/Search');
    }

    /**
     * 把订单手动设置成已经付款
     *
     * @param $f3
     */
    public function MarkPay($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_order_markpay');

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $order_id  = $validator->required('订单ID非法')->digits('订单ID非法')->min(1)->validate('order_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        $orderBasicService = new OrderBasicService();
        $orderInfo         = $orderBasicService->loadOrderInfoById($order_id);
        if ($orderInfo->isEmpty()) {
            $this->addFlashMessage('订单不存在');
            goto out;
        }

        if ($orderBasicService::PS_PAYED == $orderInfo['pay_status']) {
            $this->addFlashMessage('订单已经是支付状态，不允许重复设置');
            goto out;
        }

        $authAdminUser       = AuthHelper::getAuthUser();
        $orderPaymentService = new OrderPaymentService();
        $orderPaymentService->markOrderInfoPay(
            $order_id,
            1,
            'admin_set',
            '0',
            '手动设置为已经支付',
            $authAdminUser['user_name']
        );

        $this->addFlashMessage('订单成功设置为支付状态');

        out:
        RouteHelper::reRoute($this, RouteHelper::makeUrl('/Order/Order/Detail', array('order_id' => $order_id), true));
    }

}
