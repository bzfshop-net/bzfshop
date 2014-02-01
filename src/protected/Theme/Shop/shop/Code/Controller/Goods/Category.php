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
use Core\Service\Goods\Category as GoodsCategoryService;

class Category extends \Controller\BaseController
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
        $searchFormQuery = array();
        $searchFormQuery['category_id'] =
            $validator->required('商品分类不能为空')->digits('分类id非法')->min(1, true, '分类id非法')->filter('ValidatorIntValue')
                ->validate('category_id');

        // 价格区间查询
        $shopPriceMin = $validator->filter('ValidatorFloatValue')->validate('shop_price_min');
        $shopPriceMin = (null == $shopPriceMin) ? null : Money::toStorage($shopPriceMin);
        $shopPriceMax = $validator->filter('ValidatorFloatValue')->validate('shop_price_max');
        $shopPriceMax = (null == $shopPriceMax) ? null : Money::toStorage($shopPriceMax);

        $searchFormQuery['shop_price'] = array($shopPriceMin, $shopPriceMax);

        // 排序
        $orderBy = $validator->oneOf(array('', 'total_buy_number', 'shop_price', 'add_time'))->validate('orderBy');
        $orderDir = $validator->oneOf(array('', 'asc', 'desc'))->validate('orderDir');
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

        $pageNo = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = 45; // 每页固定显示 45 个商品

        // 生成 smarty 的缓存 id
        $smartyCacheId =
            'Goods|Category|' . md5(
                json_encode($searchFormQuery) . json_encode($orderByParam) . '_' . $pageNo . '_' . $pageSize
            );

        // 开启并设置 smarty 缓存时间
        enableSmartyCache(true, bzf_get_option_value('smarty_cache_time_goods_search'));

        if ($smarty->isCached('goods_category.tpl', $smartyCacheId)) {
            goto out_display;
        }

        $goodsCategoryService = new GoodsCategoryService();
        $category = $goodsCategoryService->loadCategoryById($searchFormQuery['category_id'], 1800);
        if ($category->isEmpty()) {
            $this->addFlashMessage('分类[' . $searchFormQuery['category_id'] . ']不存在');
            goto out_fail;
        }

        // 我们需要在左侧显示分类层级结构
        $goodsCategoryTreeArray = $goodsCategoryService->fetchCategoryTreeArray($category['parent_meta_id'], false, 1800);
        $smarty->assign('goodsCategoryTreeArray', $goodsCategoryTreeArray);

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

        // 赋值给模板
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('goodsArray', $goodsArray);

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
