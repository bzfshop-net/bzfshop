<?php

/**
 * @author QiangYu
 *
 * 计算最后的总价，这个步骤应该最后执行
 *
 */

namespace Core\Service\Cart;

use Core\Base\IPipeline;

class TotalPricePipeline implements IPipeline
{

    public function execute($cartInstance)
    {

        // 使用引用取得内部数据，方便操作
        $cartContext = & $cartInstance->getCartContextRef();

        if ($cartContext->isEmpty()) {
            // 没有数据，不需要计算
            goto out_fail;
        }

        $totalGoodsPrice    = 0; // 总商品价格
        $totalDiscount      = 0; // 总的商品折扣
        $totalExtraDiscount = 0; // 总的额外折扣
        $totalShipFee       = 0; // 总的快递价格
        $totalPrice         = 0; // 总的支付价格

        foreach ($cartContext->orderGoodsArray as &$orderGoodsItem) {
            $totalGoodsPrice += $orderGoodsItem->getValue('goods_price');
            $totalDiscount += $orderGoodsItem->getValue('discount');
            $totalExtraDiscount += $orderGoodsItem->getValue('extra_discount');
            $totalShipFee += $orderGoodsItem->getValue('shipping_fee');
        }
        unset($orderGoodsItem);

        $totalPrice = $totalGoodsPrice + $totalShipFee - $totalDiscount - $totalExtraDiscount;
        $totalPrice = ($totalPrice > 0) ? $totalPrice : 0;

        $cartContext->setValue('goods_amount', $totalGoodsPrice);
        $cartContext->setValue('discount', $totalDiscount);
        $cartContext->setValue('extra_discount', $totalExtraDiscount);
        $cartContext->setValue('shipping_fee', $totalShipFee);
        $cartContext->setValue('order_amount', $totalPrice);

        return true;

        out_fail:
        return false; // 返回 false，后面的 pipeline 就不会被执行了
    }

}