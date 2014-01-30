<?php

/**
 * @author QiangYu
 *
 * 商品的类型属性信息
 *
 * */

namespace Controller\Goods\Edit;

use Core\Cache\ClearHelper;
use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Goods\Type as GoodsTypeService;

class Type extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_get');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $goods_id = $validator->required('商品ID不能为空')->digits()->min(1)->validate('goods_id');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 取得商品信息
        $goodsBasicService = new GoodsBasicService();
        $goods = $goodsBasicService->loadGoodsById($goods_id);
        if ($goods->isEmpty()) {
            $this->addFlashMessage('商品ID[' . $goods_id . ']非法');
            goto out_fail;
        }

        // smarty 赋值
        $smarty->assign('goods', $goods);

        out_display:
        $smarty->display('goods_edit_type.tpl');
        return;

        out_fail:
        RouteHelper::reRoute($this, '/Goods/Search');
    }

    public function ajaxListAttrValue($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_get', true);

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $goods_id = $validator->required()->digits()->min(1)->validate('goods_id');
        $typeId = $validator->required()->digits()->min(1)->validate('typeId');
        $errorMessage = '';

        if (!$this->validate($validator)) {
            $errorMessage = implode('|', $this->flashMessageArray);
            goto out_fail;
        }

        $goodsTypeService = new GoodsTypeService();
        // 取得属性值的树形结构
        $goodsAttrValueTreeTable = $goodsTypeService->fetchGoodsAttrItemValueTreeTable($goods_id, $typeId);

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, $goodsAttrValueTreeTable);
        return;

        out_fail:
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_post');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $goods_id = $validator->required('商品ID不能为空')->digits()->min(1)->validate('goods_id');

        if (!$this->validate($validator)) {
            goto out_fail;
        }
        unset($validator);

        $goodsBasicService = new GoodsBasicService();
        $goods = $goodsBasicService->loadGoodsById($goods_id);
        if ($goods->isEmpty()) {
            $this->addFlashMessage('商品ID[' . $goods_id . ']非法');
            goto out_fail;
        }

        // 商品类型属性做验证
        $validator = new Validator($f3->get('POST'));

        //表单数据验证、过滤
        $type_id = $validator->digits()->min(1)->validate('type_id');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        $goodsTypeService = new GoodsTypeService();

        // 商品类型发生了变化，清除所有旧的属性
        if ($goods['type_id'] != $type_id) {
            $goodsTypeService->removeAllGoodsAttrItemValue($goods_id);
            $goods->type_id = $type_id;
            $goods->save();
        }

        // 获得属性值列表
        $goodsAttrValueArray = $f3->get('POST[goodsAttrValueArray]');
        if (!empty($goodsAttrValueArray)) {
            foreach ($goodsAttrValueArray as $goodsAttrValueInfo) {
                $goodsAttrValueInfo = @json_decode($goodsAttrValueInfo, true);
                if (empty($goodsAttrValueInfo)) {
                    continue;
                }
                // 更新属性值
                $goodsAttrValue = $goodsTypeService->loadGoodsAttrById(intval($goodsAttrValueInfo['goods_attr_id']));
                $goodsAttrValue->goods_id = $goods_id;
                $goodsAttrValue->attr_item_id = $goodsAttrValueInfo['meta_id'];
                $goodsAttrValue->attr_item_value = $goodsAttrValueInfo['attr_item_value'];
                $goodsAttrValue->save();
            }
        }

        // 成功，显示商品详情
        $this->addFlashMessage('商品类型属性保存成功');

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goods_id);

        RouteHelper::reRoute($this, RouteHelper::makeUrl('/Goods/Edit/Type', array('goods_id' => $goods_id), true));
        return;

        out_fail:
        RouteHelper::reRoute($this, '/Goods/Search');
    }

}
