<?php

/**
 * @author QiangYu
 *
 * 所有的支付回调接口
 *
 * */

namespace Core\Controller\Payment;

use Core\Helper\Utility\Route as RouteHelper;
use Core\Payment\PaymentGatewayHelper;

class PaymentReturn extends \Controller\BaseController
{

    /**
     * 采用 Magic 方法来实现，就不用每个支付方式都实现一次了
     */
    public function __call($method, $args)
    {
        global $f3;

        $payGateway = PaymentGatewayHelper::getPaymentGateway($method);
        $ret        = $payGateway->doReturnUrl($f3);

        if ($ret) {
            $this->addFlashMessage('订单支付成功');
        } else {
            $this->addFlashMessage('订单支付失败，请联系在线客服');
        }

        $order_id = $payGateway->getOrderId();
        if (!empty($order_id)) {
            // 跳转到订单查看
            RouteHelper::reRoute($this, RouteHelper::makeUrl('/My/Order/Detail', array('order_id' => $order_id), true));
            return;
        }
        // 跳转到我的订单
        RouteHelper::reRoute($this, '/My/Order');
    }

}
