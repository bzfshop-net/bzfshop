<?php

/**
 * @author QiangYu
 *
 * 商品类型
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Type as GoodsTypeService;

class GoodsType extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    public function ListType($f3)
    {
        // 检查缓存
        $cacheKey = md5(__FILE__ . '\\' . __METHOD__);
        $goodsTypeArray = $f3->get($cacheKey);
        if (!empty($goodsTypeArray)) {
            goto out;
        }

        $goodsTypeService = new GoodsTypeService();
        $goodsTypeArray = $goodsTypeService->fetchGoodsTypeArray(array(), 0, 0);

        $f3->set($cacheKey, $goodsTypeArray, 300); //缓存 5 分钟

        out:
        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $goodsTypeArray);
    }

    public function ListAttrGroup($f3)
    {
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id = $validator->required()->digits()->min(1)->validate('typeId');
        $errorMessage = '';

        if (!$this->validate($validator)) {
            $errorMessage = implode('|', $this->flashMessageArray);
            goto out_fail;
        }

        // 检查缓存
        $cacheKey = md5(__FILE__ . '\\' . __METHOD__ . '\\' . $meta_id);
        $attrGroupArray = $f3->get($cacheKey);
        if (!empty($attrGroupArray)) {
            goto out;
        }

        $goodsTypeService = new GoodsTypeService();
        $attrGroupArray = $goodsTypeService->fetchGoodsTypeAttrGroupArray($meta_id);

        $f3->set($cacheKey, $attrGroupArray, 300); //缓存 5 分钟

        out:
        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $attrGroupArray);
        return;

        out_fail:
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }

    public function ListAttrItem($f3)
    {
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id = $validator->required()->digits()->min(1)->validate('typeId');
        $errorMessage = '';

        if (!$this->validate($validator)) {
            $errorMessage = implode('|', $this->flashMessageArray);
            goto out_fail;
        }

        // 检查缓存
        $cacheKey = md5(__FILE__ . '\\' . __METHOD__ . '\\' . $meta_id);
        $attrItemArray = $f3->get($cacheKey);
        if (!empty($attrItemArray)) {
            goto out;
        }

        $goodsTypeService = new GoodsTypeService();
        $attrItemArray = $goodsTypeService->fetchGoodsTypeAttrItemArray($meta_id);

        $f3->set($cacheKey, $attrItemArray, 300); //缓存 5 分钟

        out:
        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $attrItemArray);
        return;

        out_fail:
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }
}
