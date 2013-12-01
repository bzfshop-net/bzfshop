<?php

/**
 * @author QiangYu
 *
 * 某个商品，一次性购买 shipping_free_number 或者更多，则免除该商品的邮费
 *
 */

namespace Core\Service\Cart;

use Core\Base\IPipeline;

class ShippingFreeNumberPipeline implements IPipeline
{

    public function execute($cartInstance)
    {

        // 使用引用取得内部数据，方便操作
        $cartContext = & $cartInstance->getCartContextRef();

        if ($cartContext->isEmpty()) {
            // 没有数据，不需要计算
            goto out_fail;
        }

        $goodIdToGoodsNumberArray = array(); // 商品 id --> 用户购买了多少个

        // 统计每个商品买了多少个
        foreach ($cartContext->orderGoodsArray as $orderGoodsItem) {
            if (!isset($goodIdToGoodsNumberArray[$orderGoodsItem->getValue('goods_id')])) {
                $goodIdToGoodsNumberArray[$orderGoodsItem->getValue('goods_id')] =
                    $orderGoodsItem->getValue('goods_number');
            } else {
                $goodIdToGoodsNumberArray[$orderGoodsItem->getValue('goods_id')] += $orderGoodsItem->getValue(
                    'goods_number'
                );
            }
        }
        unset($orderGoodsItem);

        // 根据上面的统计，调整相应的邮费
        foreach ($cartContext->orderGoodsArray as &$orderGoodsItem) {

            if ($orderGoodsItem->goods['shipping_free_number'] <= 0) {
                // 不免邮费，没必要计算了
                continue;
            }

            // 一次性购买多于 shipping_free_number，则免除邮费
            if ($goodIdToGoodsNumberArray[$orderGoodsItem->getValue('goods_id')] >=
                $orderGoodsItem->goods['shipping_free_number']
            ) {
                // 顾客购买免邮
                $orderGoodsItem->setValue('shipping_fee', 0);
                $orderGoodsItem->setValue(
                    'shipping_fee_note',
                    $orderGoodsItem->goods['shipping_free_number'] . '件以上免邮'
                );

                // 供货商也同样免邮
                $orderGoodsItem->setValue('suppliers_shipping_fee', 0);
                $orderGoodsItem->setValue(
                    'suppliers_shipping_fee_note',
                    $orderGoodsItem->goods['shipping_free_number'] . '件以上免邮'
                );
            }

        }
        unset($orderGoodsItem);

        return true;

        out_fail:
        return false; // 返回 false，后面的 pipeline 就不会被执行了
    }

}