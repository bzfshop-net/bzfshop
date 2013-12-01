<?php

/**
 * @author QiangYu
 *
 * 商品的规格选择，比如 红色 XL 码
 *
 * */

namespace Controller\Goods\Edit;

use Core\Cache\ClearHelper;
use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Money;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Goods\Log as GoodsLogService;
use Core\Service\Goods\Spec as GoodsSpecService;

class Spec extends \Controller\AuthController
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

        // 取得商品规格信息
        $goodsSpecService = new GoodsSpecService();

        if (!$goodsSpecService->loadGoodsSpec($goods_id)) {
            $this->addFlashMessage('非法商品ID');
            goto out_fail;
        }

        // 显示商品
        $smarty->assign($goodsSpecService->getData());

        $smarty->display('goods_edit_spec.tpl');
        return;

        out_fail:
        RouteHelper::reRoute($this, '/Goods/Search');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_post');

        $goodsLogContent = '';

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $goods_id  = $validator->required()->digits()->min(1)->validate('goods_id');

        if (!$this->validate($validator)) {
            goto out_fail_list_goods;
        }
        unset($validator);

        $goodsBasicService = new GoodsBasicService();
        $goods             = $goodsBasicService->loadGoodsById($goods_id, 1);

        if ($goods->isEmpty()) {
            $this->addFlashMessage('商品 id[' . $goods_id . '] 非法');
            goto out_fail_list_goods;
        }

        // POST 参数验证
        $validator = new Validator($f3->get('POST'));

        $goodsSpecNameArray     = $validator->validate('goodsSpecNameArray');
        $goodsSpecValue1Array   = $validator->validate('goodsSpecValue1Array');
        $goodsSpecValue2Array   = $validator->validate('goodsSpecValue2Array');
        $goodsSpecValue3Array   = $validator->validate('goodsSpecValue3Array');
        $goodsNumberArray       = $validator->validate('goodsNumberArray');
        $goodsSpecAddPriceArray = $validator->validate('goodsSpecAddPriceArray');
        $goodsSnArray           = $validator->validate('goodsSnArray');
        $imgIdArray             = $validator->validate('imgIdArray');

        if (empty($goodsSpecValue1Array)) {
            goto save_spec;
        }

        // 检查，商品属性名不能为空
        foreach ($goodsSpecNameArray as $goodsSpecName) {
            if (!empty($goodsSpecName)) {
                break;
            }
            $this->addFlashMessage('商品属性名不能为空');
            goto out_fail_edit_spec;
        }

        // 商品选项中不能有特殊符号
        $valueArray = array_merge($goodsSpecValue1Array, $goodsSpecValue2Array, $goodsSpecValue3Array);
        foreach ($valueArray as $valueItem) {
            if (empty($valueItem)) {
                continue;
            }
            // 商品规格不允许有特殊符号
            if (preg_match('#[,\\\t\s\n\+\?\^~!%/$]+#', $valueItem)) {
                $this->addFlashMessage('商品选项不能有特殊符号: 逗号、空格、回车、\\、? 等 ...');
                goto out_validate_fail;
            }
        }

        // 做数据格式转换，商品库存
        foreach ($goodsNumberArray as &$number) {
            $number = abs(intval($number));
        }
        unset($number);

        // 做数据格式转换，商品规格对应的加价
        foreach ($goodsSpecAddPriceArray as &$add_price) {
            $add_price = Money::toStorage(abs(floatval($add_price)));
        }
        unset($add_price);


        if (!$this->validate($validator)) {
            goto out_reroute;
        }

        save_spec:

        $goodsSpecService = new GoodsSpecService();
        $goodsSpecService->initWithData(
            $goodsSpecNameArray,
            $goodsSpecValue1Array,
            $goodsSpecValue2Array,
            $goodsSpecValue3Array,
            $goodsNumberArray,
            $goodsSpecAddPriceArray,
            $goodsSnArray,
            $imgIdArray
        );

        // 保存数据
        $goodsSpecService->saveGoodsSpec($goods_id);

        $this->addFlashMessage('更新商品规格成功');

        out: // 正常退出
        $goodsSpecNameArray = is_array($goodsSpecNameArray) ? $goodsSpecNameArray : array();

        $goodsLogContent .= '属性名：' . implode(',', $goodsSpecNameArray) . "\n";
        $valueCount = count($goodsSpecValue1Array);
        for ($valueIndex = 0; $valueIndex < $valueCount; $valueIndex++) {
            $goodsLogContent .= '选择：'
                . @$goodsSpecValue1Array[$valueIndex] . ','
                    . @$goodsSpecValue2Array[$valueIndex] . ','
                        . @$goodsSpecValue3Array[$valueIndex] . ','
                            . '库存:' . @$goodsNumberArray[$valueIndex] . ','
                                . '加价:' . Money::toSmartyDisplay(@$goodsSpecAddPriceArray[$valueIndex]) . ','
                                . 'SN:' . @$goodsSnArray[$valueIndex] . ','
                                    . 'image:' . @$imgIdArray[$valueIndex] . ','
                                        . "\n";
        }

        $authAdminUser   = AuthHelper::getAuthUser();
        $goodsLogService = new GoodsLogService();
        $goodsLogService->addGoodsLog(
            $goods_id,
            $authAdminUser['user_id'],
            $authAdminUser['user_name'],
            '商品规格',
            $goodsLogContent
        );

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goods_id);

        out_reroute:

        RouteHelper::reRoute(
            $this,
            RouteHelper::makeUrl('/Goods/Edit/Spec', array('goods_id' => $goods_id), true)
        );
        return;

        out_fail_list_goods:
        RouteHelper::reRoute($this, '/Goods/Search');
        return;

        out_validate_fail:
        global $smarty;
        $smarty->display('goods_edit_spec.tpl');
        return;

        out_fail_edit_spec:
        RouteHelper::reRoute(
            $this,
            RouteHelper::makeUrl('/Goods/Edit/Spec', array('goods_id' => $goods->goods_id), true)
        );
    }
}

