<?php

/**
 * @author QiangYu
 *
 * 网站的缺省控制器，网站首页
 *
 * */

namespace Controller\Mobile;

use Core\Helper\Utility\Route as RouteHelper;
use Core\Plugin\PluginHelper;
use Core\Service\Goods\Category as CategoryService;
use Core\Service\Goods\Gallery as GoodsGalleryService;
use Theme\Mobile\MobileThemePlugin;

class Index extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        // 生成 smarty 的缓存 id
        $smartyCacheId = 'Mobile|Index';

        // 开启并设置 smarty 缓存时间
        enableSmartyCache(true, MobileThemePlugin::getOptionValue('smarty_cache_time_goods_index')); // 缓存页面

        if ($smarty->isCached('mobile_index.tpl', $smartyCacheId)) {
            goto out_display;
        }

        $categoryLevel     = 3; // 取得分类下面 3 层的商品
        $categoryGoodsSzie = 4; // 每个分类下面取 4 个商品

        // 取得分类列表
        $categoryService = new CategoryService();
        $categoryArray   = $categoryService->fetchCategoryArray(0); //取得分类

        // 取得商品列表
        $categoryGoodsArray = array();
        $goodsIdArray       = array();
        foreach ($categoryArray as $category) {
            // 分类下面 3 层子分类， 取 4 个商品
            $goodsArray =
                $categoryService->fetchGoodsArray(
                    $category['meta_id'],
                    $categoryLevel,
                    PluginHelper::SYSTEM_MOBILE,
                    0,
                    $categoryGoodsSzie
                );
            if (!empty($goodsArray)) {
                $categoryGoodsArray[$category['meta_id']] = $goodsArray;
                foreach ($goodsArray as $goodsItem) {
                    $goodsIdArray[] = $goodsItem['goods_id'];
                }
            }
        }

        // 网站完全没有商品？ 不缓存
        if (empty($goodsIdArray)) {
            goto out_display;
        }

        // 取得商品的图片
        $goodsGalleryService  = new GoodsGalleryService();
        $goodsGalleryArray    = $goodsGalleryService->fetchGoodsGalleryArrayByGoodsIdArray($goodsIdArray);
        $currentGoodsId       = -1;
        $goodsThumbImageArray = array();
        $goodsImageArray      = array();
        foreach ($goodsGalleryArray as $goodsGalleryItem) {
            if ($currentGoodsId == $goodsGalleryItem['goods_id']) {
                continue; //每个商品我们只需要一张图片，跳过其它的图片
            }
            $currentGoodsId                        = $goodsGalleryItem['goods_id']; // 新的商品 id
            $goodsThumbImageArray[$currentGoodsId] = RouteHelper::makeImageUrl($goodsGalleryItem['thumb_url']);
            $goodsImageArray[$currentGoodsId]      = RouteHelper::makeImageUrl($goodsGalleryItem['img_url']);
        }

        // 赋值给模板
        $smarty->assign('categoryArray', $categoryArray);
        $smarty->assign('categoryGoodsArray', $categoryGoodsArray);
        $smarty->assign('goodsThumbImageArray', $goodsThumbImageArray);
        $smarty->assign('goodsImageArray', $goodsImageArray);

        out_display:
        $smarty->assign('seo_title', $smarty->getTemplateVars('seo_title') . ',' . $f3->get('HOST'));
        $smarty->display('mobile_index.tpl', $smartyCacheId);
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
