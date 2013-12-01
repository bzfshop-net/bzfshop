<?php

/**
 * @author QiangYu
 *
 * 商品搜索
 *
 * */

namespace Controller\Goods;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Plugin\ThemeHelper;
use Core\Search\SearchHelper;
use Core\Service\Goods\Gallery as GoodsGalleryService;
use Theme\Mobile\MobileThemePlugin;

class Search extends \Controller\BaseController
{

    private $searchExtraCondArray;
    private $searchFieldSelector;

    public function __construct()
    {
        $currentThemeInstance = ThemeHelper::getCurrentSystemThemeInstance();

        // 我们只搜索有效商品
        $this->searchExtraCondArray = array(
            array('is_delete = 0 AND is_on_sale = 1 AND is_alone_sale = 1'),
            array(QueryBuilder::buildGoodsFilterForSystem($currentThemeInstance->getGoodsFilterSystemArray())),
        );

        // 选择我们需要的字段
        $this->searchFieldSelector =
            'goods_id, cat_id, goods_sn, goods_name, brand_id, goods_number, market_price, shop_price, suppliers_id,'
            . 'virtual_buy_number, user_buy_number, user_pay_number, (virtual_buy_number + user_pay_number) as total_buy_number';
    }

    public function get($f3)
    {
        global $smarty;

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('GET'));

        $pageNo = $validator->digits('pageNo 参数非法')->min(0, true, 'pageNo 参数非法')->validate('pageNo');

        // 搜索参数数组
        $searchFormQuery                 = array();
        $searchFormQuery['category_id']  =
            $validator->digits('分类id非法')->min(1, true, '分类id非法')->filter('ValidatorIntValue')->validate('category_id');
        $searchFormQuery['suppliers_id'] =
            $validator->digits('供货商id非法')->min(1, true, '供货商id非法')->filter('ValidatorIntValue')->validate(
                'suppliers_id'
            );
        $searchFormQuery['goods_name']   = $validator->validate('goods_name');

        // 价格区间查询
        $shopPriceMin                  = $validator->filter('ValidatorFloatValue')->validate('shop_price_min');
        $shopPriceMax                  = $validator->filter('ValidatorFloatValue')->validate('shop_price_max');
        $searchFormQuery['shop_price'] = array($shopPriceMin, $shopPriceMax);

        // 排序
        $orderBy      = $validator->oneOf(array('', 'total_buy_number', 'shop_price', 'add_time'))->validate('orderBy');
        $orderDir     = $validator->oneOf(array('', 'asc', 'desc'))->validate('orderDir');
        $orderByParam = array();
        if (!empty($orderBy)) {
            $orderByParam = array(array($orderBy, $orderDir));
        }
        //增加一些我们的缺省排序
        $orderByParam[] = array('sort_order', 'desc');
        $orderByParam[] = array('goods_id', 'desc');

        // 参数验证
        if (!$this->validate($validator) || empty($searchFormQuery)) {
            goto out_fail;
        }

        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = 10; // 每页固定显示 10 个商品

        // 生成 smarty 的缓存 id
        $smartyCacheId =
            'Goods|Search|' . md5(
                json_encode($searchFormQuery) . json_encode($orderByParam) . '_' . $pageNo . '_' . $pageSize
            );

        // 开启并设置 smarty 缓存时间
        enableSmartyCache(true, MobileThemePlugin::getOptionValue('smarty_cache_time_goods_search'));

        if ($smarty->isCached('goods_search.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // 合并查询参数
        $searchParamArray =
            array_merge(QueryBuilder::buildSearchParamArray($searchFormQuery), $this->searchExtraCondArray);

        $totalCount = SearchHelper::count(SearchHelper::Module_Goods, $searchParamArray);
        if ($totalCount <= 0) {
            goto out_display; // 没有商品，直接显示
        }

        // 页号可能是用户乱输入的，我们需要检查
        if ($pageNo * $pageSize >= $totalCount) {
            goto out_fail; // 返回首页
        }

        $goodsArray =
            SearchHelper::search(
                SearchHelper::Module_Goods,
                $this->searchFieldSelector,
                $searchParamArray,
                $orderByParam,
                $pageNo * $pageSize,
                $pageSize
            );

        if (empty($goodsArray)) {
            goto out_display;
        }

        // 取得 商品ID 列表
        $goodsIdArray = array();
        foreach ($goodsArray as $goodsItem) {
            $goodsIdArray[] = $goodsItem['goods_id'];
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
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('goodsArray', $goodsArray);
        $smarty->assign('goodsThumbImageArray', $goodsThumbImageArray);
        $smarty->assign('goodsImageArray', $goodsImageArray);

        out_display:
        $smarty->display('goods_search.tpl', $smartyCacheId);

        return;

        out_fail: // 失败从这里返回
        RouteHelper::reRoute($this, '/'); // 返回首页
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
