<?php

/**
 * @author QiangYu
 *
 * 商品品牌
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Service\Goods\Brand as BrandService;

class Brand extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    public function ListBrand($f3)
    {
        // 检查缓存
        $cacheKey = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);
        $brandArray = $f3->get($cacheKey);
        if (!empty($brandArray)) {
            goto out;
        }

        $brandService = new BrandService();
        $brandArray = $brandService->fetchBrandIdNameArray();

        $f3->set($cacheKey, $brandArray, 300); //缓存 5 分钟

        out:
        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $brandArray);
    }

}
