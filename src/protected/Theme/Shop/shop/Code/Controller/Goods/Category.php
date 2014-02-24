<?php

/**
 * @author QiangYu
 *
 * 商品分类
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
use Core\Service\Goods\Type as GoodsTypeService;

class Category extends \Controller\BaseController
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
        $searchFormQuery                  = array();
        $searchFormQuery['g.category_id'] =
            $validator->required('商品分类不能为空')->digits('分类id非法')->min(1, true, '分类id非法')->filter('ValidatorIntValue')
                ->validate('category_id');

        // 这里支持多品牌查询
        $searchFormQuery['g.brand_id'] = array('=', $validator->validate('brand_id'));

        // 价格区间查询
        $shopPriceMin = $validator->filter('ValidatorFloatValue')->validate('shop_price_min');
        $shopPriceMin = (null == $shopPriceMin) ? null : Money::toStorage($shopPriceMin);
        $shopPriceMax = $validator->filter('ValidatorFloatValue')->validate('shop_price_max');
        $shopPriceMax = (null == $shopPriceMax) ? null : Money::toStorage($shopPriceMax);

        $searchFormQuery['g.shop_price'] = array($shopPriceMin, $shopPriceMax);

        // 属性过滤
        $filter = $validator->validate('filter');

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
            'Goods|Category|' . md5(
                json_encode($searchFormQuery) . json_encode($orderByParam)
                . '_' . $filter . '_' . $pageNo . '_' . $pageSize
            );

        // 开启并设置 smarty 缓存时间
        enableSmartyCache(true, bzf_get_option_value('smarty_cache_time_goods_search'));

        if ($smarty->isCached('goods_category.tpl', $smartyCacheId)) {
            goto out_display;
        }

        $goodsCategoryService = new GoodsCategoryService();
        $category             = $goodsCategoryService->loadCategoryById($searchFormQuery['g.category_id'], 1800);
        if ($category->isEmpty()) {
            $this->addFlashMessage('分类[' . $searchFormQuery['category_id'] . ']不存在');
            goto out_fail;
        }
        $smarty->assign('category', $category);

        $metaData        = json_decode($category['meta_data'], true);
        $metaFilterArray = @$metaData['filterArray'];

        // 1. 我们需要在左侧显示分类层级结构
        $goodsCategoryTreeArray =
            $goodsCategoryService->fetchCategoryTreeArray($category['parent_meta_id'], false, 1800);
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
        $goodsFilterQueryCond = array_merge(
            $this->searchExtraCondArray,
            array(array('g.category_id', '=', $searchFormQuery['g.category_id']))
        );

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
        $brandIdArray      = array_map(
            function ($elem) {
                return $elem['brand_id'];
            },
            $goodsBrandIdArray
        );

        if (!empty($brandIdArray)) {
            $goodsBrandService = new GoodsBrandService();
            $goodsBrandArray   =
                $goodsBrandService->fetchBrandArrayByIdArray(array_unique(array_values($brandIdArray)));
            $filterBrandArray  = array();
            foreach ($goodsBrandArray as $brand) {
                $filterBrandArray[] = array('value' => $brand['brand_id'], 'text' => $brand['brand_name']);
            }
            if (!empty($filterBrandArray)) {
                $goodsFilterArray['品牌'] = array('filterKey' => 'brand_id', 'filterValueArray' => $filterBrandArray);
            }
        }

        // 3. 查询属性过滤
        if (!empty($metaFilterArray)) {
            $goodsTypeService = new GoodsTypeService();
            foreach ($metaFilterArray as $filterItem) {
                $goodsTypeAttrItem = $goodsTypeService->loadGoodsTypeAttrItemById($filterItem['attrItemId']);
                if ($goodsTypeAttrItem->isEmpty()) {
                    continue;
                }
                // 取得商品属性值列表
                $goodsAttrItemValueArray =
                    SearchHelper::search(
                        SearchHelper::Module_GoodsAttrGoods,
                        'min(ga.goods_attr_id) as goods_attr_id, ga.attr_item_value',
                        array_merge(
                            $goodsFilterQueryCond,
                            array(array('ga.attr_item_id', '=', $filterItem['attrItemId']))
                        ),
                        null,
                        0,
                        0,
                        'ga.attr_item_value'
                    );
                if (!empty($goodsAttrItemValueArray)) {
                    $filterValueArray = array();
                    foreach ($goodsAttrItemValueArray as $itemValue) {
                        $filterValueArray[] =
                            array('value' => $itemValue['goods_attr_id'], 'text' => $itemValue['attr_item_value']);
                    }
                    $goodsFilterArray[$goodsTypeAttrItem['meta_name']] = array(
                        'filterKey'        => 'filter',
                        'filterValueArray' => $filterValueArray
                    );
                } else {
                    // 如果这个属性完全没有值（没有一个商品设过任何值），我们弄一个空的
                    $goodsFilterArray[$goodsTypeAttrItem['meta_name']] = array(
                        'filterKey'        => 'filter',
                        'filterValueArray' => array()
                    );
                }
            }
        }

        // 赋值给模板
        if (!empty($goodsFilterArray)) {
            $smarty->assign('goodsFilterArray', $goodsFilterArray);
        }


        // 4. 商品查询

        if (!empty($metaFilterArray)) {
            // 构造 attrItemId
            $metaFilterTypeIdArray = array();
            foreach ($metaFilterArray as $metaFilterItem) {
                $metaFilterTypeIdArray[] = $metaFilterItem['attrItemId'];
            }

            // 构造 filter 参数，注意 filter 参数在 GoodsGoodsAttr 中具体解析
            // 合并查询参数
            $searchParamArray =
                array_merge(
                    QueryBuilder::buildSearchParamArray($searchFormQuery),
                    $this->searchExtraCondArray,
                    array(array('ga.filter', implode('.', $metaFilterTypeIdArray), $filter))
                );
        } else {
            // 合并查询参数
            $searchParamArray =
                array_merge(
                    QueryBuilder::buildSearchParamArray($searchFormQuery),
                    $this->searchExtraCondArray
                );
        }


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
        $smarty->assign('seo_title', $category['meta_name'] . ',' . $smarty->getTemplateVars('seo_title'));

        out_display:

        // 滑动图片广告
        $goods_search_adv_slider = json_decode(bzf_get_option_value('goods_search_adv_slider'), true);
        if (!empty($goods_search_adv_slider)) {
            $smarty->assign('goods_search_adv_slider', $goods_search_adv_slider);
        }

        $smarty->display('goods_category.tpl', $smartyCacheId);

        return;

        out_fail: // 失败从这里返回
        RouteHelper::reRoute($this, '/'); // 返回首页
    }

    public function post($f3)
    {
        RouteHelper::reRoute($this, RouteHelper::makeUrl('/Goods/Category', $_POST, false, true));
    }

}
