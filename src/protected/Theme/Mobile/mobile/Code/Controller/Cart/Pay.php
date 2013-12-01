<?php

/**
 * @author QiangYu
 *
 * 购物车支付处理
 *
 * */

namespace Controller\Cart;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Log\Base;
use Core\OrderRefer\ReferHelper;
use Core\Payment\PaymentGatewayHelper;
use Core\Service\Cart\Cart as CartBasicService;
use Core\Service\Order\Order as OrderBasicService;
use Core\Service\User\User as UserBasicService;

class Pay extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('GET'));
        $order_id  = $validator->required('订单ID非法')->digits('订单ID非法')->min(1, true, '订单ID非法')->validate('order_id');
        if (!$this->validate($validator)) {
            goto out_fail;
        }

        $userInfo         = AuthHelper::getAuthUser();
        $userBasicService = new UserBasicService();
        $userInfo         = $userBasicService->loadUserById($userInfo['user_id']);

        // 支付某一个特定的订单需要把订单加载到临时购物车里面
        $orderBasicService = new OrderBasicService();

        // 检查权限
        $orderInfo = $orderBasicService->loadOrderInfoById($order_id);
        if ($orderInfo->isEmpty() || $userInfo['user_id'] != $orderInfo['user_id']
            || OrderBasicService::OS_UNCONFIRMED != $orderInfo['order_status']
        ) {
            $this->addFlashMessage('订单ID非法');
            goto out_fail;
        }

        $cartBasicService = new CartBasicService();

        // 加载订单到购物车里
        if (!$cartBasicService->loadFromOrderInfo($order_id)) {
            $this->addFlashMessage('订单加载失败');
            goto out_fail;
        }

        $cartContext = & $cartBasicService->getCartContextRef();
        if ($cartContext->isEmpty()) {
            $this->addFlashMessage('订单为空，不能支付');
            goto out_fail;
        }

        // 做一次购物车计算
        $cartBasicService->calcOrderPrice();

        // 如果购物车里面有错误消息，我们需要显示它
        if ($cartContext->hasError()) {
            $this->addFlashMessageArray($cartContext->getAndClearErrorMessageArray());
            goto out_fail;
        }

        // 给模板赋值
        $smarty->assign('userInfo', $userInfo);
        $smarty->assign('cartContext', $cartContext);
        $smarty->assign('orderGoodsArray', $cartContext->orderGoodsArray);
        $smarty->assign($cartContext->getAddressInfo());
        $smarty->assign('postscript', $cartContext->getValue('postscript'));

        //订单推送url
        $smarty->assign('orderNotifyUrlArray', ReferHelper::getOrderReferNotifyUrlArray($f3, $order_id));

        $smarty->display('cart_pay.tpl');
        return;

        out_fail: //失败从这里退出
        RouteHelper::reRoute($this, '/My/Order');
    }

    public function post($f3)
    {

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('GET'));
        $order_id  = $validator->required('订单ID非法')->digits('订单ID非法')->min(1, true, '订单ID非法')->validate('order_id');
        if (!$this->validate($validator)) {
            goto out_fail;
        }

        $validator      = new Validator($f3->get('POST'));
        $payGatewayType = $validator->required('必须选择一种支付方式')->validate('pay_gateway_type');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 取得用户信息
        $userInfo         = AuthHelper::getAuthUser();
        $userBasicService = new UserBasicService();
        $userInfo         = $userBasicService->loadUserById($userInfo['user_id']);

        // 支付某一个特定的订单需要把订单加载到临时购物车里面
        $orderBasicService = new OrderBasicService();

        // 检查权限
        $orderInfo = $orderBasicService->loadOrderInfoById($order_id);
        if ($orderInfo->isEmpty() || $userInfo['user_id'] != $orderInfo['user_id']
            || OrderBasicService::OS_UNCONFIRMED != $orderInfo['order_status']
        ) {
            $this->addFlashMessage('订单ID非法');
            goto out_fail;
        }

        $cartBasicService = new CartBasicService();

        // 加载订单到购物车里
        if (!$cartBasicService->loadFromOrderInfo($order_id)) {
            $this->addFlashMessage('订单加载失败');
            goto out_fail;
        }

        $cartContext = & $cartBasicService->getCartContextRef();
        if ($cartContext->isEmpty()) {
            $this->addFlashMessage('订单为空，不能支付');
            goto out_fail;
        }

        // 做第一次购物车计算，需要计算原始订单的金额，后面红包使用的时候有最低订单金额限制
        $cartBasicService->calcOrderPrice();

        // 计算支付金额
        $cartBasicService->calcOrderPayment();

        // 如果购物车里面有错误消息，我们需要显示它
        if ($cartContext->hasError()) {
            $this->addFlashMessageArray($cartContext->getAndClearErrorMessageArray());
            goto out_fail;
        }

        // 更新订单信息
        $orderInfo = $cartBasicService->saveOrder($userInfo['user_id'], '买家：' . $userInfo['user_name']);

        if (!$orderInfo || $orderInfo->isEmpty()) {
            //订单创建失败，报错
            $this->addFlashMessage('更新订单信息失败，请联系客服');
            goto out_fail;
        }

        // 如果订单金额为 0 ，使用 credit 支付网关
        if ($orderInfo['order_amount'] <= 0) {
            $payGatewayType = 'credit';
        }

        $order_id = $orderInfo['order_id'];

        // 解析参数，我们允许写成 tenpay_cmbchina  代表财付通、招商银行
        $payGatewayParamArray = explode('_', $payGatewayType);

        // 获取支付网关
        $payGateway = PaymentGatewayHelper::getPaymentGateway($payGatewayParamArray[0]);
        // 根据参数做初始化
        if (!$payGateway->init($payGatewayParamArray)) {
            $this->addFlashMessage('支付网关' . $payGatewayType . '初始化失败');
            goto out_fail;
        }
        $payRequestUrl = $payGateway->getRequestUrl(
            $order_id, // 订单ID
            RouteHelper::makeUrl('/Payment/PaymentReturn/' . $payGateway->getGatewayType(), null, false, true),
            // returnUrl
            RouteHelper::makeUrl('/Payment/PaymentNotify/' . $payGateway->getGatewayType(), null, false, true)
        ); //notifyUrl

        if (empty($payRequestUrl)) {
            $this->addFlashMessage('系统错误：无法生成支付链接');
            goto out_fail;
        }

        // 记录支付日志
        printLog(
            '[orderId:' . $order_id . ']' . $payRequestUrl,
            'PAYMENT',
            Base::INFO
        );

        // 跳转去支付
        header('Location:' . $payRequestUrl);
        return;

        out_fail: //失败从这里退出
        RouteHelper::reRoute($this, '/My/Order');
    }

}
