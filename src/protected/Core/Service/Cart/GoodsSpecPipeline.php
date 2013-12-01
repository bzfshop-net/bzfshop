<?php

/**
 * @author QiangYu
 *
 * 计算用户选择的每个 Spec (规格) 对应的价钱
 * 比如，一款衣服，用户选择的规格是 user_spec -> '红色、XL码，男款'
 * 商品基本价格是 120元，'红色、XL码，男款' 加 10 元，所以这个选择的最终价格是 spec_price = 120 + 10 = 130 元
 *
 */

namespace Core\Service\Cart;

use Core\Base\IPipeline;
use Core\Service\Goods\Spec as GoodsSpecService;

class GoodsSpecPipeline implements IPipeline
{

    public function execute($cartInstance)
    {

        // 使用引用取得内部数据，方便操作
        $cartContext = & $cartInstance->getCartContextRef();

        if ($cartContext->isEmpty()) {
            // 没有数据，不需要计算
            goto out_fail;
        }

        // 计算每个商品的组合选择
        foreach ($cartContext->orderGoodsArray as &$orderGoodsItem) {

            $goodsInfo = $orderGoodsItem->goods;
            if (empty($goodsInfo['goods_spec'])) {
                continue; // 普通商品，没有组合选择
            }

            $goodsAttr = $orderGoodsItem->getValue('goods_attr');

            // 计算这个规格对应的价格
            $goodsSpecService = new GoodsSpecService();
            $goodsSpecService->initWithJson($goodsInfo['goods_spec']);
            $goodsSpecDataArray = $goodsSpecService->getGoodsSpecDataArray($goodsAttr);

            // 商品的销售价格 = 基础价格 + 规格额外加价
            $shopPrice = $orderGoodsItem->goods['shop_price'] + @$goodsSpecDataArray['goods_spec_add_price'];

            // 设置这个规格对应的价格
            $orderGoodsItem->setValue('shop_price', $shopPrice);
        }
        unset($orderGoodsItem);


        return true;

        out_fail:
        return false; // 返回 false，后面的 pipeline 就不会被执行了
    }

}