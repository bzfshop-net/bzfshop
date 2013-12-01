<?php

/**
 * @author QiangYu
 *
 * 把一些需要计算的单元先清零
 *
 */

namespace Core\Service\Cart;

use Core\Base\IPipeline;

class ResetCalcPipeline implements IPipeline
{

    public function execute($cartInstance)
    {

        // 使用引用取得内部数据，方便操作
        $cartContext = & $cartInstance->getCartContextRef();

        $totalGoodsPrice    = 0; // 总商品价格
        $totalDiscount      = 0; // 总的商品折扣
        $totalExtraDiscount = 0; // 总的额外折扣
        $totalShipFee       = 0; // 总的快递价格
        $totalPrice         = 0; // 总的支付价格

        $cartContext->setValue('goods_amount', $totalGoodsPrice);
        $cartContext->setValue('discount', $totalDiscount);
        $cartContext->setValue('extra_discount', $totalExtraDiscount);
        $cartContext->setValue('shipping_fee', $totalShipFee);
        $cartContext->setValue('order_amount', $totalPrice);

        return true;
    }

}