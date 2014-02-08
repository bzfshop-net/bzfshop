<?php

/**
 * @author QiangYu
 *
 * 商品搜索
 *
 * */

namespace Controller\Goods;

use Core\Helper\Utility\Money;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Plugin\ThemeHelper;
use Core\Search\SearchHelper;
use Core\Service\Goods\Brand as GoodsBrandService;
use Core\Service\Goods\Category as GoodsCategoryService;

class Search extends \Controller\BaseController
{

    private $searchExtraCondArray;

    public function __construct()
    {
        $currentThemeInstance = ThemeHelper::getCurrentSystemThemeInstance();

        // 我们只搜索有效商品
        $this->searchExtraCondArray = array(
            array('g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1'),
            array(QueryBuilder::buildGoodsFilterForSystem($currentThemeInstance->getGoodsFilterSystemArray(), 'g')),
        );
    }

    public function get($f3)
    {
        global $smarty;

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('GET'));

        $pageNo = $validator->digits('pageNo 参数非法')->min(0, true, 'pageNo 参数非法')->validate('pageNo');

        // 搜索参数数组
        $searchFormQuery                 = array();
        $searchKeywords                  = $validator->validate('keywords');
        $searchFormQuery['g.goods_name'] = $searchKeywords;

        // 这里支持多品牌查询
        $searchFormQuery['g.brand_id'] = array('=', $validator->validate('brand_id'));

        // 价格区间查询
        $shopPriceMin = $validator->filter('ValidatorFloatValue')->validate('shop_price_min');
        $shopPriceMin = (null == $shopPriceMin) ? null : Money::toStorage($shopPriceMin);
        $shopPriceMax = $validator->filter('ValidatorFloatValue')->validate('shop_price_max');
        $shopPriceMax = (null == $shopPriceMax) ? null : Money::toStorage($shopPriceMax);

        $searchFormQuery['g.shop_price'] = array($shopPriceMin, $shopPriceMax);

        // 排序
        $orderBy      = $validator->oneOf(array('', 'total_buy_number', 'shop_price', 'add_time'))->validate('orderBy');
        $orderDir     = $validator->oneOf(array('', 'asc', 'desc'))->validate('orderDir');
        $orderByParam = array();
        if (!empty($orderBy)) {
            $orderByParam = array(array($orderBy, $orderDir));
        }
        //增加一些我们的缺省排序
        $orderByParam[] = array('g.sort_order', 'desc');
        $orderByParam[] = array('g.goods_id', 'desc');

        // 参数验证
        if (!$this->validate($validator) || empty($searchFormQuery)) {
            goto out_fail;
        }

        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = 45; // 每页固定显示 45 个商品

        // 生成 smarty 的缓存 id
        $smartyCacheId =
            'Goods|Search|' . md5(
                json_encode($searchFormQuery) . json_encode($orderByParam)
                . '_' . $pageNo . '_' . $pageSize
            );

        // 开启并设置 smarty 缓存时间
        enableSmartyCache(true, bzf_get_option_value('smarty_cache_time_goods_search'));

        if ($smarty->isCached('goods_search.tpl', $smartyCacheId)) {
            goto out_display;
        }

        $goodsCategoryService = new GoodsCategoryService();

        // 1. 我们需要在左侧显示分类层级结构
        $goodsCategoryTreeArray = $goodsCategoryService->fetchCategoryTreeArray(0, false, 1800);
        $smarty->assign('goodsCategoryTreeArray', $goodsCategoryTreeArray);

        /**
         * 构造 Filter 数组，结构如下
         *
         * array(
         *      '商品品牌' => array(
         *              filterKey => 'brand_id'
         *              filterValueArray => array( array(value=>'13', text=>'品牌1'), ...)
         *              ),
         *      '颜色' => array(
         *              filterKey => 'filter',
         *              filterValueArray => array( array(value=>'13', text=>'品牌1'), ...)
         *              )
         * )
         *
         */
        $goodsFilterArray = array();
        // filter 查询在这个条件下进行
        $goodsFilterQueryCond =
            array_merge(QueryBuilder::buildSearchParamArray(array('g.goods_name' => $searchKeywords)),
                $this->searchExtraCondArray);

        // 2. 商品品牌查询
        $goodsBrandIdArray =
            SearchHelper::search(
                SearchHelper::Module_Goods,
                'distinct(g.brand_id)',
                array_merge($goodsFilterQueryCond, array(array('g.brand_id > 0'))),
                null,
                0,
                0
            );
        $brandIdArray      = array_map(function ($elem) {
            return $elem['brand_id'];
        }, $goodsBrandIdArray);

        if (!empty($brandIdArray)) {
            $goodsBrandService = new GoodsBrandService();
            $goodsBrandArray   = $goodsBrandService->fetchBrandArrayByIdArray(array_unique(array_values($brandIdArray)));
            $filterBrandArray  = array();
            foreach ($goodsBrandArray as $brand) {
                $filterBrandArray[] = array('value' => $brand['brand_id'], 'text' => $brand['brand_name']);
            }
            if (!empty($filterBrandArray)) {
                $goodsFilterArray['品牌'] = array('filterKey' => 'brand_id', 'filterValueArray' => $filterBrandArray);
            }
        }

        if (!empty($goodsFilterArray)) {
            $smarty->assign('goodsFilterArray', $goodsFilterArray);
        }

        // 3. 商品属性过滤   TODO: 等以后扩展，看看 Search 怎么做属性过滤

        // 4. 商品查询

        // 构造 filter 参数，注意 filter 参数在 GoodsGoodsAttr 中具体解析
        // 合并查询参数
        $searchParamArray =
            array_merge(QueryBuilder::buildSearchParamArray($searchFormQuery),
                $this->searchExtraCondArray
            );

        $totalCount = SearchHelper::count(SearchHelper::Module_GoodsGoodsAttr, $searchParamArray);
        if ($totalCount <= 0) {
            goto out_display; // 没有商品，直接显示
        }

        // 页号可能是用户乱输入的，我们需要检查
        if ($pageNo * $pageSize >= $totalCount) {
            goto out_fail; // 返回首页
        }

        $goodsArray =
            SearchHelper::search(
                SearchHelper::Module_GoodsGoodsAttr,
                'g.goods_id, g.cat_id, g.goods_sn, g.goods_name, g.brand_id, g.goods_number, g.market_price'
                . ', g.shop_price, g.suppliers_id, g.virtual_buy_number, g.user_buy_number, g.user_pay_number'
                . ', (g.virtual_buy_number + g.user_pay_number) as total_buy_number',
                $searchParamArray,
                $orderByParam,
                $pageNo * $pageSize,
                $pageSize
            );

        if (empty($goodsArray)) {
            goto out_display;
        }
        $smarty->assign('goodsArray', $goodsArray);

        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);

        // SEO 考虑，网页标题加上分类的名称
        $smarty->assign('seo_title', '商品搜索,' . $searchKeywords . ',' . $smarty->getTemplateVars('seo_title'));

        out_display:

        // 滑动图片广告
        $goods_search_adv_slider = json_decode(bzf_get_option_value('goods_search_adv_slider'), true);
        if (!empty($goods_search_adv_slider)) {
            $smarty->assign('goods_search_adv_slider', $goods_search_adv_slider);
        }

        $smarty->display('goods_search.tpl', $smartyCacheId);

        return;

        out_fail: // 失败从这里返回
        RouteHelper::reRoute($this, '/'); // 返回首页
    }

    public function post($f3)
    {
        // Search 采用 post，这样可以保证生成唯一的 URL 然后跳转到 GET， Search 不做静态化地址
        RouteHelper::reRoute($this, RouteHelper::makeUrl('/Goods/Search', $_POST, false, true, false));
    }

}
