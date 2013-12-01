<?php

/**
 * @author QiangYu
 *
 * 用户可以使用 余额支付，红包支付
 * 这里计算这些支付手段使用之后的商品总价
 *
 */

namespace Core\Service\Cart;

use Core\Base\IPipeline;

class CreditPayPipeline implements IPipeline
{

    public function execute($cartInstance)
    {

        // 使用引用取得内部数据，方便操作
        $cartContext = & $cartInstance->getCartContextRef();

        if ($cartContext->isEmpty()) {
            // 没有数据，不需要计算
            goto out_fail;
        }

        // 用户使用了余额支付
        $surplus = ($cartContext->getValue('surplus') > 0) ? $cartContext->getValue('surplus') : 0;
        // 用户使用了红包支付
        $bonus = ($cartContext->getValue('bonus') > 0) ? $cartContext->getValue('bonus') : 0;

        // 扣除余额支付和红包支付的金额
        $totalPrice = $cartContext->getValue('order_amount') - $surplus - $bonus;
        $totalPrice = ($totalPrice > 0) ? $totalPrice : 0;

        $cartContext->setValue('order_amount', $totalPrice);

        return true;

        out_fail:
        return false; // 返回 false，后面的 pipeline 就不会被执行了
    }

}