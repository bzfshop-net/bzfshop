<?php

/**
 * @author QiangYu
 *
 * 所有的支付回调接口
 *
 * */

namespace Core\Controller\Payment;

use Core\OrderRefer\ReferHelper;
use Core\Payment\PaymentGatewayHelper;

class PaymentNotify extends \Controller\BaseController
{

    protected function preCallback($f3)
    {

    }

    /**
     *
     * @param object  $f3         F3框架对象
     * @param boolean $result     执行 doNotifyUrl() 的返回结果，是否执行成功
     * @param object  $payGateway 支付网关对象
     */
    protected function postCallback($f3, $result, $payGateway)
    {

        if (!$result) {
            // 支付失败不用做什么操作
            return;
        }

        // order_refer 可能需要做一些额外的操作，比如推送订单给 CPS 方
        $orderId = $payGateway->getOrderId();
        ReferHelper::notifyOrderRefer($f3, $orderId);
    }

    /**
     * 采用 Magic 方法来实现，就不用每个支付方式都实现一次了
     */
    public function __call($method, $args)
    {
        global $f3;

        $this->preCallback($f3);
        $payGateway = PaymentGatewayHelper::getPaymentGateway($method);
        $ret        = $payGateway->doNotifyUrl($f3);
        flush(); // flush 输出
        $this->postCallback($f3, $ret, $payGateway);
    }

}
