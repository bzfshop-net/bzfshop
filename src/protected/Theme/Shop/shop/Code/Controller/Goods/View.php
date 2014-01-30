<?php

/**
 * @author QiangYu
 *
 * 查看商品的详情
 *
 * */

namespace Controller\Goods;

use Core\Cache\GoodsGalleryCache;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Plugin\PluginHelper;
use Core\Service\Goods\Category as GoodsCategoryService;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Goods\Spec as GoodsSpecService;
use Core\Service\Goods\Supplier as GoodsSupplierService;
use Core\Service\Goods\Type as GoodsTypeService;

class View extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('GET'));
        $goods_id = $validator->required('商品id不能为空')->digits('商品id非法')->min(1, true, '商品id非法')->validate('goods_id');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 生成 smarty 的缓存 id
        $smartyCacheId = 'Goods|' . $goods_id . '|View';

        // 开启并设置 smarty 缓存时间
        enableSmartyCache(true, bzf_get_option_value('smarty_cache_time_goods_view'));

        if ($smarty->isCached('goods_view.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // 查询商品信息
        $goodsBasicService = new GoodsBasicService();
        $goodsInfo = $goodsBasicService->loadGoodsById($goods_id);

        // 商品不存在，退出
        if ($goodsInfo->isEmpty() || !Utils::isTagExist(PluginHelper::SYSTEM_SHOP, $goodsInfo['system_tag_list'])) {
            $this->addFlashMessage('商品 [' . $goods_id . '] 不存在');
            goto out_fail;
        }

        // 取得商品的分类层级
        $goodsCategoryLevelArray = array();
        $goodsCategoryService = new GoodsCategoryService();
        $categoryLevel = 5; // 最多取 5 层分类
        $currentCategoryId = $goodsInfo['cat_id'];
        for (; $categoryLevel > 0; $categoryLevel--) {
            $category = $goodsCategoryService->loadCategoryById($currentCategoryId, 1800);
            if ($category->isEmpty()) {
                break;
            }
            array_unshift($goodsCategoryLevelArray, $category);
            if ($category['parent_meta_id'] <= 0) {
                break;
            }
            $currentCategoryId = $category['parent_meta_id'];
        }

        // 取商品推广信息设置
        $goodsPromote = $goodsBasicService->loadGoodsPromoteByGoodsId($goods_id);

        // 取商品图片集
        $goodsGalleryArray = GoodsGalleryCache::getGoodsGallery($goods_id);
        foreach ($goodsGalleryArray as &$galleryItem) {
            $galleryItem['img_original'] = RouteHelper::makeImageUrl($galleryItem['img_original']);
            $galleryItem['img_url'] = RouteHelper::makeImageUrl($galleryItem['img_url']);
            $galleryItem['thumb_url'] = RouteHelper::makeImageUrl($galleryItem['thumb_url']);
        }
        unset($galleryItem);

        // 取相互关联的商品
        $linkGoodsArray = $goodsBasicService->fetchLinkGoodsArray($goods_id);

        // 相同供货商的商品，一起购买只收一份邮费
        $goodsSupplierService = new GoodsSupplierService();
        // 取得供货商下面的商品总数，总数只缓存 10 分钟
        $supplierTotalGoodsCount = $goodsSupplierService->countSupplierGoodsArray($goodsInfo['suppliers_id'], 600);
        // 随机挑选 10 个商品
        $supplierGoodsSize = 10;
        $supplierGoodsOffset = ($supplierTotalGoodsCount <= $supplierGoodsSize)
            ? 0
            : mt_rand(
                0,
                $supplierTotalGoodsCount - $supplierGoodsSize
            );
        $supplierGoodsArray =
            $goodsSupplierService->fetchSupplierGoodsArray(
                $goodsInfo['suppliers_id'],
                $supplierGoodsOffset,
                $supplierGoodsSize
            );
        // 把自己去除掉
        $supplierGoodsKeyExcludeArray = array();
        foreach ($supplierGoodsArray as $supplierGoodsKey => $supplierGoodsItem) {
            if ($supplierGoodsItem['goods_id'] == $goods_id) {
                $supplierGoodsKeyExcludeArray[] = $supplierGoodsKey;
            }
        }
        foreach ($supplierGoodsKeyExcludeArray as $supplierGoodsKey) {
            unset($supplierGoodsArray[$supplierGoodsKey]);
        }

        // 设置商品页面的 SEO 信息
        $smarty->assign(
            'seo_title',
            $goodsInfo['seo_title'] . ',' . $f3->get('sysConfig[site_name]')
        );
        $smarty->assign('seo_description', $goodsInfo['seo_description']);
        $smarty->assign('seo_keywords', $goodsInfo['seo_keyword']);

        // 给模板赋值
        $smarty->assign('goodsInfo', $goodsInfo);
        $smarty->assign('goodsPromote', $goodsPromote);

        // 商品购买选择的规格
        if (!empty($goodsInfo['goods_spec'])) {
            $goodsSpecService = new GoodsSpecService();
            $goodsSpecService->initWithJson($goodsInfo['goods_spec']);
            // 只显示有库存的商品规格
            $goodsSpecData = $goodsSpecService->getBuyableData();
            $smarty->assign($goodsSpecData);
            $smarty->assign('goodsSpecJson', json_encode($goodsSpecData));
        }

        // 商品的类型属性
        if ($goodsInfo['type_id'] > 0) {
            $goodsTypeService = new GoodsTypeService();
            $goodsAttrTreeTable = $goodsTypeService->fetchGoodsAttrItemValueTreeTable($goodsInfo['goods_id'], $goodsInfo['type_id']);
            $smarty->assign('goodsAttrTreeTable', $goodsAttrTreeTable);
        }

        if (!empty($goodsCategoryLevelArray)) {
            $smarty->assign('goodsCategoryLevelArray', $goodsCategoryLevelArray);
        }
        if (!Utils::isEmpty($goodsGalleryArray)) {
            $smarty->assign('goodsGalleryArray', $goodsGalleryArray);
        }
        if (!Utils::isEmpty($linkGoodsArray)) {
            $smarty->assign('linkGoodsArray', $linkGoodsArray);
        }
        if (!Utils::isEmpty($supplierGoodsArray)) {
            $smarty->assign('supplierGoodsArray', $supplierGoodsArray);
        }

        // 滑动图片广告
        $goods_view_adv_slider = json_decode(bzf_get_option_value('goods_view_adv_slider'), true);
        if (!empty($goods_view_adv_slider)) {
            $smarty->assign('goods_view_adv_slider', $goods_view_adv_slider);
        }

        // 移动端对应的 URL，用于百度页面适配
        $smarty->assign(
            'currentPageMobileUrl',
            RouteHelper::makeShopSystemUrl(PluginHelper::SYSTEM_MOBILE, '/Goods/View', array('goods_id' => $goods_id))
        );

        out_display:
        $smarty->display('goods_view.tpl', $smartyCacheId);
        return;

        out_fail: // 失败从这里返回
        RouteHelper::reRoute($this, '/'); // 返回首页        
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
