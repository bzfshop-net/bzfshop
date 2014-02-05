<?php

/**
 * @author QiangYu
 *
 * 商品列表操作
 *
 * */

namespace Controller\Goods;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Search\SearchHelper;
use Core\Service\Goods\Category as CategoryBasicService;
use Core\Service\Goods\Spec as GoodsSpecService;
use Core\Service\Goods\Type as GoodsTypeService;
use Core\Service\User\Supplier as UserSupplierService;
use Theme\Manage\ManageThemePlugin;

class Search extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_search');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        // 搜索参数数组
        $searchFormQuery                    = array();
        $searchFormQuery['g.is_on_sale']    =
            $validator->digits()->min(0)->filter('ValidatorIntValue')->validate('is_on_sale');
        $searchFormQuery['g.goods_id']      =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('goods_id');
        $searchFormQuery['g.suppliers_id']  =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('suppliers_id');
        $searchFormQuery['g.goods_name']    = $validator->validate('goods_name');
        $searchFormQuery['g.cat_id']        =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('cat_id');
        $searchFormQuery['g.type_id']       =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('type_id');
        $searchFormQuery['g.brand_id']      =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('brand_id');
        $searchFormQuery['g.goods_sn']      = $validator->validate('goods_sn');
        $searchFormQuery['g.warehouse']     = $validator->validate('warehouse');
        $searchFormQuery['g.shelf']         = $validator->validate('shelf');
        $searchFormQuery['g.admin_user_id'] =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('admin_user_id');

        $systemTag = $validator->validate('system_tag');
        if (!empty($systemTag)) {
            $searchFormQuery['g.system_tag_list'] = Utils::makeTagString(array($systemTag));
        }

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 查询条件
        $searchParamArray = array();

        // 根据推广渠道做搜索
        $goods_promote = $validator->validate('goods_promote');
        if (!empty($goods_promote)) {
            switch ($goods_promote) {
                case '360tequan':
                    $searchParamArray[] = array('gp.360tequan_price > 0');
                    break;
                case '360tegong':
                    $searchParamArray[] = array('gp.360tegong_enable = 1');
                    break;
                default:
                    // do nothing
                    break;
            }
        }

        // 建立查询条件
        $searchParamArray = array_merge($searchParamArray, QueryBuilder::buildSearchParamArray($searchFormQuery));

        // 查询商品列表
        $totalCount = SearchHelper::count(SearchHelper::Module_GoodsGoodsPromote, $searchParamArray);
        if ($totalCount <= 0) { // 没商品，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Goods/Search');
        }

        // 商品列表
        $goodsArray = SearchHelper::search(
            SearchHelper::Module_GoodsGoodsPromote,
            'g.goods_id, g.system_tag_list, g.cat_id, g.admin_user_name, g.goods_name, g.goods_number'
            . ', g.goods_spec, g.is_on_sale, g.type_id'
            . ', g.market_price, g.shop_price, g.shipping_fee, g.shipping_free_number'
            . ', g.suppliers_id, g.suppliers_price, g.suppliers_shipping_fee, g.warehouse, g.shelf',
            $searchParamArray,
            array(array('g.goods_id', 'desc')),
            $pageNo * $pageSize,
            $pageSize
        );

        // 取得供货商 id 列表，商品分类 id
        $supplierIdArray = array();
        $categoryIdArray = array();
        $typeIdArray     = array();
        foreach ($goodsArray as $goodsItem) {
            $supplierIdArray[] = $goodsItem['suppliers_id'];
            $categoryIdArray[] = $goodsItem['cat_id'];
            $typeIdArray[]     = $goodsItem['type_id'];
        }
        $supplierIdArray = array_unique($supplierIdArray);
        $categoryIdArray = array_unique($categoryIdArray);

        //取得供货商信息
        $userSupplierService = new UserSupplierService();
        $supplierArray       = $userSupplierService->fetchSupplierArrayBySupplierIdArray($supplierIdArray);

        // 建立 suppliers_id --> supplier 的反查表，方便快速查询
        $supplierIdToSupplierArray = array();
        foreach ($supplierArray as $supplier) {
            $supplierIdToSupplierArray[$supplier['suppliers_id']] = $supplier;
        }

        $system_url_base_array = json_decode(ManageThemePlugin::getOptionValue('system_url_base_array'), true);

        // 放入供货商信息
        foreach ($goodsArray as &$goodsItem) {
            if (isset($supplierIdToSupplierArray[$goodsItem['suppliers_id']])) {
                // 很老的订单，用户可能被删除了
                $goodsItem['suppliers_name'] = $supplierIdToSupplierArray[$goodsItem['suppliers_id']]['suppliers_name'];
            }

            // 解析 system_tag_list，放入 system_array 的信息
            $systeArray                = Utils::parseTagString($goodsItem['system_tag_list']);
            $goodsItem['system_array'] = array();
            foreach ($systeArray as $systemItem) {
                $goodsItem['system_array'][] = @$system_url_base_array[$systemItem]['name'];
            }

            // 商品规格
            if (!empty($goodsItem['goods_spec'])) {
                $goodsSpecService = new GoodsSpecService();
                $goodsSpecService->initWithJson($goodsItem['goods_spec']);
                $goodsItem['goods_spec'] = $goodsSpecService->getGoodsSpecDataArray();
            }
        }
        unset($goodsItem);

        // 取得分类信息
        $categoryBasicService = new CategoryBasicService();
        $categoryArray        = $categoryBasicService->fetchCategoryArrayByIdArray($categoryIdArray);

        // 建立 cat_id  ---> cateogry 信息的反查表
        $categoryIdToCategoryArray = array();
        foreach ($categoryArray as $categoryItem) {
            $categoryIdToCategoryArray[$categoryItem['meta_id']] = $categoryItem;
        }

        // 放入分类信息
        foreach ($goodsArray as &$goodsItem) {
            if (isset($categoryIdToCategoryArray[$goodsItem['cat_id']])) {
                // 很老的商品，分类信息可能已经不存在了
                $goodsItem['cat_name'] = $categoryIdToCategoryArray[$goodsItem['cat_id']]['meta_name'];
            }
        }
        unset($goodsItem);

        // 取得商品类型信息
        $goodsTypeService = new GoodsTypeService();
        $goodsTypeArray   = $goodsTypeService->fetchGoodsTypeArrayByTypeIdArray($typeIdArray);

        // 建立 type_id ---> type 信息的反查表
        $typeIdToTypeArray = array();
        foreach ($goodsTypeArray as $goodsType) {
            $typeIdToTypeArray[$goodsType['meta_id']] = $goodsType;
        }

        // 放入类型信息
        foreach ($goodsArray as &$goodsItem) {
            if (isset($typeIdToTypeArray[$goodsItem['type_id']])) {
                $goodsItem['type_name'] = $typeIdToTypeArray[$goodsItem['type_id']]['meta_name'];
            }
        }
        unset($goodsItem);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('goodsArray', $goodsArray);
        $smarty->assign(
            'system_url_base_array',
            json_decode(ManageThemePlugin::getOptionValue('system_url_base_array'), true)
        );

        out_display:
        $smarty->display('goods_search.tpl');
    }

}
