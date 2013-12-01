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
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Goods\Spec as GoodsSpecService;
use Theme\Mobile\MobileThemePlugin;

class View extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('GET'));
        $goods_id  = $validator->required('商品id不能为空')->digits('商品id非法')->min(1, true, '商品id非法')->validate('goods_id');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 生成 smarty 的缓存 id
        $smartyCacheId = 'Goods|' . $goods_id . '|View';

        // 开启并设置 smarty 缓存时间
        enableSmartyCache(true, MobileThemePlugin::getOptionValue('smarty_cache_time_goods_view')); // 缓存页面

        if ($smarty->isCached('goods_view.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // 查询商品信息
        $goodsBasicService = new GoodsBasicService();
        $goodsInfo         = $goodsBasicService->loadGoodsById($goods_id);

        // 商品不存在，退出
        if ($goodsInfo->isEmpty() || !Utils::isTagExist(PluginHelper::SYSTEM_MOBILE, $goodsInfo['system_tag_list'])) {
            $this->addFlashMessage('商品 [' . $goods_id . '] 不存在');
            goto out_fail;
        }

        // 取商品推广信息设置
        $goodsPromote = $goodsBasicService->loadGoodsPromoteByGoodsId($goods_id);

        // 取商品图片集
        $goodsGalleryArray = GoodsGalleryCache::getGoodsGallery($goods_id);
        foreach ($goodsGalleryArray as &$galleryItem) {
            $galleryItem['img_url']   = RouteHelper::makeImageUrl($galleryItem['img_url']);
            $galleryItem['thumb_url'] = RouteHelper::makeImageUrl($galleryItem['thumb_url']);
        }
        unset($galleryItem);

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

        if (!Utils::isEmpty($goodsGalleryArray)) {
            $smarty->assign('goodsGalleryArray', $goodsGalleryArray);
        }

        // 设置商品规格
        if (!empty($goodsInfo['goods_spec'])) {
            $goodsSpecService = new GoodsSpecService();
            $goodsSpecService->initWithJson($goodsInfo['goods_spec']);
            $smarty->assign('goodsSpec', $goodsSpecService->getGoodsSpecDataArray());
        }

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
