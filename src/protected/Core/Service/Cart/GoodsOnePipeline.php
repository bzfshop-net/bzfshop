<?php

/**
 * @author QiangYu
 *
 * 计算单个商品的费用
 *
 */

namespace Core\Service\Cart;

use Core\Base\IPipeline;

class GoodsOnePipeline implements IPipeline
{

    public function execute($cartInstance)
    {

        // 使用引用取得内部数据，方便操作
        $cartContext =  & $cartInstance->getCartContextRef();

        if ($cartContext->isEmpty()) {
            // 没有数据，不需要计算
            goto out_fail;
        }

        $goodIdArray = array(); // 已经收过邮费的商品 id 列表
        foreach ($cartContext->orderGoodsArray as &$orderGoodsItem) {

            // 商品的邮费，同一个商品的不同组合只收一份邮费
            $goodsId = $orderGoodsItem->getValue('goods_id');
            if (in_array($goodsId, $goodIdArray)) {
                $orderGoodsItem->setValue('shipping_fee', 0);
                $orderGoodsItem->setValue('shipping_fee_note', '相同商品只收一份邮费');
                $orderGoodsItem->setValue('suppliers_shipping_fee', 0); // 我们不收邮费，同时供货商那份也不给了
            } else {
                $goodIdArray[] = $goodsId;
                // 设置邮费到 orderGoodsValue 里面
                $orderGoodsItem->setValue('shipping_fee', $orderGoodsItem->goods['shipping_fee']);
            }

            $orderGoodsItem->setValue(
                'goods_price',
                $orderGoodsItem->getValue('shop_price') * $orderGoodsItem->getValue('goods_number')
            );
        }
        unset($orderGoodsItem);

        return true;

        out_fail:
        return false; // 返回 false，后面的 pipeline 就不会被执行了
    }

}