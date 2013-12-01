<?php

namespace Core\Payment;

use Core\Base\RegisterHelper;
use Core\Helper\Utility\Validator;

/**
 * 支付网关的通用 Helper 类，方便插件注册自己的支付方式
 *
 * @author QiangYu
 */
abstract class PaymentGatewayHelper extends RegisterHelper
{

    // 接口检测
    protected static $instanceType = '\Core\Payment\IGateway';

    // 注册 class 对应的 name --> initParam
    protected static $registerClassArray = array(
        '\Core\Payment\CreditGateway' => array(), //用户使用余额支付
    );

    // 记录 key --> class 的对应，这样我们可以从 $registerClassArray 加载类实例
    protected static $registerKeyArray = array(
        'credit' => '\Core\Payment\CreditGateway', // credit 代表余额支付
    );

    /**
     * 根据 $gatewayType 的不同创建不同的支付网关
     *
     * @return IGateway 对象，不同的支付网关的具体实现
     *
     * @param string $gatewayType 网关类型参数，比如 tenpay_1001 ：财付通招商银行直连
     */
    public static function getPaymentGateway($gatewayType)
    {
        // 参数验证
        $validator   = new Validator(array('gatewayType' => $gatewayType));
        $gatewayType = $validator->required()->validate('gatewayType');

        $hasError = $validator->hasErrors();

        if ($hasError) {
            // 有错误，收集错误信息，抛出异常
            $errorMsg   = '';
            $errorArray = $validator->getAllErrors();
            foreach ($errorArray as $errorField => $errorMsg) {
                $errorMsg .= '{[' . $errorField . '][' . $errorMsg . ']}';
            }

            throw new \InvalidArgumentException($errorMsg);
        }

        if (!array_key_exists($gatewayType, static::$registerKeyArray)) {
            throw new \InvalidArgumentException('unrecognize payment gateway [' . $gatewayType . ']');
        }

        return static::loadKeyInstance($gatewayType);
    }

}

