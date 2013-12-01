<?php

/**
 * 完全用站内信用支付，比如 账户余额、积分、红包等
 *
 * @author QiangYu
 *
 */

namespace Core\Payment;

use Core\Helper\Utility\Money;
use Core\Helper\Utility\Validator;
use Core\Log\Base;
use Core\Payment\AbstractBaseGateway;
use Core\Service\Order\Order as OrderBasicService;
use Core\Service\Order\Payment as OrderPaymentService;

/**
 * 支付网关的通用接口定义
 *
 * @author QiangYu
 */
class CreditGateway extends AbstractBaseGateway
{

    private $orderId = null;
    private $payId = null; //用于填充订单 order_info  中的 pay_id 字段

    public function getGatewayType()
    {
        return 'credit'; //注意大小写
    }

    public function getOrderId()
    {
        if (!empty($this->orderId)) {
            return $this->orderId;
        }

        if (array_key_exists('order_id', $_GET)) {
            return $_GET['order_id'];
        }

        return 0;
    }

    public function init(array $paramArray = null)
    {
        global $f3;
        $configArray = $f3->get('creditpay');
        if (empty($configArray)) {
            return false;
        }
        $validator   = new Validator($configArray);
        $this->payId = $validator->required()->digits()->min(1)->validate('payId');
        $this->validate($validator);

        return true;
    }

    public function getRequestUrl($orderId, $returnUrl, $notifyUrl)
    {
        // 参数验证
        $validator = new Validator(array('orderId' => $orderId, 'returnUrl' => $returnUrl));
        $orderId   = $validator->required()->digits()->min(1)->validate('orderId');
        $returnUrl = $validator->required()->validate('returnUrl');
        $this->validate($validator);

        $this->orderId = $orderId; //设置订单 ID
        // 自己调用 notify 完成订单支付
        $this->doNotifyUrl(null);

        return $returnUrl . '?order_id=' . $orderId; //返回 returnUrl
    }

    public function doNotifyUrl($f3)
    {
        if (empty($this->orderId)) {
            throw new \InvalidArgumentException('orderId invalid');
        }

        // 记录所有的参数，方便调试查找
        printLog(
            'CreditPay Notify orderId [' . $this->orderId . ']',
            'PAYMENT',
            Base::INFO
        );

        $orderBasicService = new OrderBasicService();
        $orderInfo         = $orderBasicService->loadOrderInfoById($this->orderId);

        //判断金额是否一致
        if ($orderInfo->isEmpty() || 0 != $orderInfo['order_amount']) {
            printLog(
                'CreditPay total_fee error, order_amount :' . Money::storageToCent($orderInfo['order_amount'])
                . ' total_fee : 0 ',
                'PAYMENT',
                Base::ERROR
            );
            goto out_fail;
        }

        //检查订单状态
        if (OrderBasicService::OS_UNCONFIRMED != $orderInfo['order_status']) {
            printLog(
                'CreditPay order_status is not OS_UNCONFIRMED, order_status[' . $orderInfo['order_status']
                . '] orderId[' . $this->orderId . ']',
                'PAYMENT',
                Base::WARN
            );
            goto out_succ;
        }

        // 把订单设置为已付款状态
        $orderPaymentService = new OrderPaymentService();
        $orderPaymentService->markOrderInfoPay($this->orderId, $this->payId, $this->getGatewayType(), '0');
        printLog(
            'CreditPay orderId[' . $this->orderId . '] notifyUrl success',
            'PAYMENT',
            Base::INFO
        );

        //------------------------------
        //处理业务完毕
        //------------------------------        

        out_succ:
        return true;

        out_fail: // 失败从这里返回
        return false;
    }

    public function doReturnUrl($f3)
    {
        printLog(
            'CreditPay returnUrl success',
            'PAYMENT',
            Base::INFO
        );

        // 设置 order_id
        $this->orderId = $f3->get('GET[order_id]');
        return true;
    }

}

