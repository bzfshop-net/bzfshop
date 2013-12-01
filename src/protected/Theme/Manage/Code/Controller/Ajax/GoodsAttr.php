<?php

/**
 * @author QiangYu
 *
 * 加载商品类型列表
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\Validator;
use Core\Service\Meta\GoodsAttrGroup as GoodsAttrGroupService;
use Core\Service\Meta\GoodsAttrItem as GoodsAttrItemService;

class GoodsAttr extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    /**
     * 列出商品属性组
     *
     * @param $f3
     */
    public function ListGoodsAttrGroup($f3)
    {
        // 检查缓存
        $cacheKey            = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);
        $goodsAttrGroupArray = $f3->get($cacheKey);
        if (!empty($goodsAttrGroupArray)) {
            goto out;
        }

        $goodsAttrGroupService = new GoodsAttrGroupService();
        $goodsAttrGroupArray   = $goodsAttrGroupService->fetchGoodsAttrGroupArray();

        $f3->set($cacheKey, $goodsAttrGroupArray, 300); //缓存 5 分钟

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, $goodsAttrGroupArray);
    }

    /**
     * 列出属性组中的所有属性
     *
     * @param $f3
     */
    public function ListGoodsAttrItem($f3)
    {

        // 参数检查
        $validator = new Validator($f3->get('GET'));
        $groupId   = $validator->required()->digits()->min(1)->validate('groupId');

        $errorMessage = '';
        if (!$this->validate($validator)) {
            $errorMessage = implode('|', $this->flashMessageArray);
            goto out_fail;
        }

        // 检查缓存
        $cacheKey           = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__ . '\\' . $groupId);
        $goodsAttrItemArray = $f3->get($cacheKey);
        if (!empty($goodsAttrItemArray)) {
            goto out;
        }

        $goodsAttrItemService = new GoodsAttrItemService();
        $goodsAttrItemArray   = $goodsAttrItemService->fetchGoodsAttrItemArray($groupId);

        $f3->set($cacheKey, $goodsAttrItemArray, 300); //缓存 5 分钟

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, $goodsAttrItemArray);
        return;

        out_fail:
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }
}
