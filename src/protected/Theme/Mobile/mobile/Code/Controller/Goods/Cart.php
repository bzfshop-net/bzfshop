<?php

/**
 * @author QiangYu
 *
 * 把商品放入到购物车中，只支持由 ajax 调用
 *
 * */

namespace Controller\Goods;

use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Plugin\PluginHelper;
use Core\Service\Cart\Cart as CartBasicService;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Goods\Spec as GoodsSpecService;

class Cart extends \Controller\BaseController
{

    public function post($f3)
    {
        // 这里操作加入购物车

        // 首先做参数合法性验证
        $validator              = new Validator($f3->get('POST'));
        $goods_id               =
            $validator->required('商品id不能为空')->digits('商品id非法')->min(1, true, '商品id非法')->validate('goods_id');
        $goodsChooseSpecListStr = $validator->validate('goods_choose_speclist');
        $goodsChooseBuyCount    =
            $validator->required('商品最少购买 1 件')->digits('商品最少购买 1 件')
                ->min(1, true, '商品最少购买 1 件')->validate('goods_choose_buycount');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 查询商品信息
        $goodsBasicService = new GoodsBasicService();
        $goodsInfo         = $goodsBasicService->loadGoodsById($goods_id);

        // 商品不存在，不允许显示等
        if ($goodsInfo->isEmpty() || !$goodsInfo->is_on_sale
            || !Utils::isTagExist(
                PluginHelper::SYSTEM_MOBILE,
                $goodsInfo['system_tag_list']
            )
        ) {
            $this->addFlashMessage('商品不存在或者不能购买');
            goto out_fail;
        }

        $cartBasicService = new CartBasicService(); // 购物车服务
        $cartBasicService->loadFromStorage(); // 恢复购物车中已有的数据
        $cartContext = & $cartBasicService->getCartContextRef(); // 取得 cartContext

        // 商品多组合选择
        if (!empty($goodsChooseSpecListStr)) {

            // 检查商品 Spec 是否合法
            $goodsSpecService = new GoodsSpecService();
            $goodsSpecService->initWithJson($goodsInfo['goods_spec']);
            $goodsSpecDataArray = $goodsSpecService->getGoodsSpecDataArray($goodsChooseSpecListStr);

            if (!$goodsSpecDataArray) {
                $this->addFlashMessage('商品选择[' . $goodsChooseSpecListStr . ']非法');
                goto out_fail;
            }

            // 检查商品库存
            if ($goodsChooseBuyCount > @$goodsSpecDataArray['goods_number']) {
                $this->addFlashMessage(
                    $goodsChooseSpecListStr . '库存不足，只剩 ' . @$goodsSpecDataArray['goods_number'] . ' 件'
                );
                goto out_fail;
            }

            // 商品加入到购物车
            $cartContext->addGoods(
                $goods_id,
                $goodsChooseBuyCount,
                $goodsChooseSpecListStr,
                @$goodsSpecDataArray['goods_sn']
            );

        } else {

            // 检查库存
            if ($goodsChooseBuyCount > $goodsInfo['goods_number']) {
                $this->addFlashMessage('库存不足，剩余 ' . $goodsInfo['goods_number'] . ' 件');
                goto out_fail;
            }

            // 普通简单选择，加入购物车
            $cartContext->addGoods($goods_id, $goodsChooseBuyCount);
        }

        $cartBasicService->syncStorage(); // 保存购物车的数据

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, null);
        return;

        out_fail: // 失败，返回出错信息
        $errorMessage = implode('|', $this->flashMessageArray);
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }

}
