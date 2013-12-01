<?php

/**
 * @author QiangYu
 *
 * 购物车中多个商品来自同一个供货商，我们只收一份最贵的邮费，而不是收多份邮费
 *
 */

namespace Core\Service\Cart;

use Core\Base\IPipeline;

class SameSupplierShippingPipeline implements IPipeline
{

    public function execute($cartInstance)
    {

        // 使用引用取得内部数据，方便操作
        $cartContext = & $cartInstance->getCartContextRef();

        if ($cartContext->isEmpty()) {
            // 没有数据，不需要计算
            goto out_fail;
        }

        $supplierIdToOrderGoodsItem = array(); // 记录供货商对应的最贵邮费商品

        // 根据商品所属供货商的情况调整相应的邮费
        foreach ($cartContext->orderGoodsArray as &$orderGoodsItem) {

            if (!isset($supplierIdToOrderGoodsItem[$orderGoodsItem->goods['suppliers_id']])) {
                // 记录供货商对应的商品邮费
                $supplierIdToOrderGoodsItem[$orderGoodsItem->goods['suppliers_id']] = & $orderGoodsItem;
                continue;
            }

            // 同一个供货商只收最贵的那个邮费
            unset($orderGoodsFreeShipping);
            $orderGoodsFreeShipping = & $orderGoodsItem;
            unset($orderGoodsSaved);
            $orderGoodsSaved = $supplierIdToOrderGoodsItem[$orderGoodsItem->goods['suppliers_id']];

            // 这个的邮费比保存的更贵，那么我们就保留这个邮费
            if ($orderGoodsItem->getValue('shipping_fee') > $orderGoodsSaved->getValue('shipping_fee')) {
                unset($orderGoodsFreeShipping);
                $orderGoodsFreeShipping =
                    $supplierIdToOrderGoodsItem[$orderGoodsItem->goods['suppliers_id']];
                unset($supplierIdToOrderGoodsItem[$orderGoodsItem->goods['suppliers_id']]);
                $supplierIdToOrderGoodsItem[$orderGoodsItem->goods['suppliers_id']] = & $orderGoodsItem;
            }

            if ($orderGoodsFreeShipping->getValue('shipping_fee') <= 0) {
                // 本来就没有邮费，不用再设置了
                continue;
            }

            // 顾客购买免邮
            $orderGoodsFreeShipping->setValue('shipping_fee', 0);
            $orderGoodsFreeShipping->setValue(
                'shipping_fee_note',
                '相同供货商只收一份邮费[' . $orderGoodsFreeShipping->goods['suppliers_id'] . ']'
            );

            // 供货商也同样免邮
            $orderGoodsFreeShipping->setValue('suppliers_shipping_fee', 0);
            $orderGoodsFreeShipping->setValue(
                'suppliers_shipping_fee_note',
                '相同供货商只收一份邮费[' . $orderGoodsFreeShipping->goods['suppliers_id'] . ']'
            );

        }
        unset($orderGoodsItem);

        return true;

        out_fail:
        return false; // 返回 false，后面的 pipeline 就不会被执行了
    }

}