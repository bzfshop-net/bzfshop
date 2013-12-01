<?php

namespace Core\Payment;

use Core\Helper\Utility\Validator;

/**
 * 支付网关的通用接口定义
 *
 * @author QiangYu
 */
abstract class AbstractBaseGateway implements IGateway
{

    protected function validate(Validator $validator)
    {
        $hasError = $validator->hasErrors();

        if (!$hasError) {
            // 没有错误，成功返回
            return;
        }

        // 有错误，收集错误信息，抛出异常
        $errorMsg   = '';
        $errorArray = $validator->getAllErrors();
        foreach ($errorArray as $errorField => $errorMsg) {
            $errorMsg .= '{[' . $errorField . '][' . $errorMsg . ']}';
        }

        throw new \InvalidArgumentException($errorMsg);
    }

    public function getGatewayType()
    {
        return __CLASS__;
    }

    public function init(array $paramArray = null)
    {
        return true;
    }

    public function getRequestUrl($orderId, $returnUrl, $notifyUrl)
    {
        return null;
    }

}

