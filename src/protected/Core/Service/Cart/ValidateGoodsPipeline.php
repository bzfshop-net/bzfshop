<?php

/**
 * @author QiangYu
 *
 * 购物车计算逻辑 pipeline，用于验证商品是否可以购买，比如商品已经下架，商品超过最大购买数量等等，库存控制
 *
 */

namespace Core\Service\Cart;

use Core\Base\IPipeline;
use Core\Helper\Utility\Utils;
use Core\Plugin\ThemeHelper;
use Core\Service\Goods\Spec as GoodsSpecService;

class ValidateGoodsPipeline implements IPipeline
{

    public function execute($cartInstance)
    {

        // 使用引用取得内部数据，方便操作
        $cartContext = & $cartInstance->getCartContextRef();

        if ($cartContext->isEmpty()) {
            // 没有数据，不需要计算
            goto out_fail;
        }

        // 当前主题允许哪些系统的商品被加入到购物车
        $currentThemeInstance   = ThemeHelper::getCurrentSystemThemeInstance();
        $goodsFilterSystemArray = $currentThemeInstance->getGoodsFilterSystemArray();

        // 从购物车中删除掉的 OrderGoods
        $removeOrderGoods = array();

        // 对每个商品做检查
        foreach ($cartContext->orderGoodsArray as &$orderGoodsItem) {

            // 商品已经下线了，不能购买
            $goodsInfo = $orderGoodsItem->goods;
            if (empty($goodsInfo) || !$goodsInfo['is_on_sale']) {
                $removeOrderGoods[] = array($goodsInfo['goods_id'], null);
                $cartContext->addErrorMessage('商品 [' . $goodsInfo['goods_id'] . '] 已经下线');
                continue;
            }

            $goodsAttr = $orderGoodsItem->getValue('goods_attr');

            // 有规格的商品，用户必须做出选择
            if (!empty($goodsInfo['goods_spec']) && empty($goodsAttr)) {
                $cartContext->addErrorMessage('商品 [' . $goodsInfo['goods_id'] . '] 必须选择规格');
                $removeOrderGoods[] = array($goodsInfo['goods_id'], null);
                continue;
            }

            // 检查商品库存
            if (!empty($goodsAttr)) {

                // 商品有规格选择
                if (empty($goodsInfo['goods_spec'])) {
                    $cartContext->addErrorMessage('商品 [' . $goodsInfo['goods_id'] . '|' . $goodsAttr . '] 选择非法');
                    $removeOrderGoods[] = array($goodsInfo['goods_id'], $goodsAttr);
                    continue;
                }

                // 检查规格是否合法
                $goodsSpecService = new GoodsSpecService();
                $goodsSpecService->initWithJson($goodsInfo['goods_spec']);

                $goodsSpecDataArray = $goodsSpecService->getGoodsSpecDataArray($goodsAttr);
                if (empty($goodsSpecDataArray)) {
                    $cartContext->addErrorMessage('商品 [' . $goodsInfo['goods_id'] . '|' . $goodsAttr . '] 选择非法');
                    $removeOrderGoods[] = array($goodsInfo['goods_id'], $goodsAttr);
                    continue;
                }

                // 检查规格对应的库存
                if ($orderGoodsItem->getValue('goods_number') > @$goodsSpecDataArray['goods_number']) {
                    $cartContext->addErrorMessage('商品 [' . $goodsInfo['goods_id'] . '|' . $goodsAttr . '] 库存不足');
                    $removeOrderGoods[] = array($goodsInfo['goods_id'], $goodsAttr);
                    continue;
                }

            } else {
                // 简单商品，没有规格，检查库存
                if ($orderGoodsItem->getValue('goods_number') > $goodsInfo['goods_number']) {
                    $cartContext->addErrorMessage('商品 [' . $goodsInfo['goods_id'] . '] 库存不足');
                    $removeOrderGoods[] = array($goodsInfo['goods_id'], null);
                    continue;
                }
            }

            // 商品不属于当前系统，不能购买
            $goodsSystemArray = Utils::parseTagString($goodsInfo['system_tag_list']);
            $intersectSystem  = array_intersect($goodsSystemArray, $goodsFilterSystemArray);
            if (empty($intersectSystem)) {
                $removeOrderGoods[] = array($goodsInfo['goods_id'], null);
                continue;
            }
        }
        unset($orderGoodsItem);

        // 删除不合格的 orderGoods 记录
        foreach ($removeOrderGoods as $removeItem) {
            $cartContext->removeGoods($removeItem[0], $removeItem[1]);
            $cartContext->addErrorMessage('商品 [' . $removeItem[0] . '] 不能购买，移除');
        }

        return true;

        out_fail:
        return false; // 返回 false，后面的 pipeline 就不会被执行了
    }

}