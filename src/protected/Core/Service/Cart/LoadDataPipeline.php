<?php

/**
 * @author QiangYu
 *
 * 购物车计算逻辑 pipeline，用于加载数据，后面的计算会需要用到这些数据
 */

namespace Core\Service\Cart;

use Core\Base\IPipeline;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Order\Order as OrderBasicService;

class LoadDataPipeline implements IPipeline
{

    public function execute($cartInstance)
    {

        // 使用引用取得内部数据，方便操作
        $cartContext = & $cartInstance->getCartContextRef();

        if ($cartContext->isEmpty()) {
            // 没有数据，不需要计算
            goto out_fail;
        }

        // 对每个商品加载数据
        $goodsBasicService = new GoodsBasicService();
        $orderBasicService = new OrderBasicService();
        foreach ($cartContext->orderGoodsArray as &$orderGoodsItem) {
            //加载商品信息，缓存1秒，提高后面同样商品的查询效率
            $goods                 = $goodsBasicService->loadGoodsById($orderGoodsItem->getValue('goods_id'), 1);
            $orderGoodsItem->goods = $goods->toArray();

            // 如果已经有存在的 orderGoods 记录（对应数据库中 order_goods 记录），我们需要重新加载一次
            // 因为客服有可能在后台对订单做了修改
            if ($orderGoodsItem->orderGoods && @$orderGoodsItem->orderGoods['rec_id'] > 0) {
                $orderGoods                 =
                    $orderBasicService->loadOrderGoodsById($orderGoodsItem->orderGoods['rec_id']);
                $orderGoodsItem->orderGoods = $orderGoods->toArray();
                unset($orderGoods);
            }
        }
        unset($orderGoodsItem);

        return true;

        out_fail:
        return false; // 返回 false，后面的 pipeline 就不会被执行了
    }

}