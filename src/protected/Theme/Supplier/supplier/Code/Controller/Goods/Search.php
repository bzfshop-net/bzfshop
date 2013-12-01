<?php

/**
 * @author QiangYu
 *
 * 商品列表操作
 *
 * */

namespace Controller\Goods;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Search\SearchHelper;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Goods\Spec as GoodsSpecService;
use Core\Service\Goods\Statistics;
use Theme\Manage\ManageThemePlugin;

class Search extends \Controller\AuthController
{

    // 我们只取出 goods 表的部分信息
    private $searchFieldSelector = 'g.goods_id, g.goods_sn, g.system_tag_list, g.warehouse, g.shelf, g.goods_name_short, g.is_on_sale, g.goods_spec, g.goods_number, g.suppliers_id, g.suppliers_price, g.suppliers_shipping_fee, g.user_buy_number, g.user_pay_number';

    public function get($f3)
    {
        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        // 查询条件
        $searchFormQuery                 = array();
        $searchFormQuery['g.is_on_sale'] =
            $validator->digits()->min(0)->filter('ValidatorIntValue')->validate('is_on_sale');
        $searchFormQuery['g.goods_id']   =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('goods_id');
        $searchFormQuery['g.goods_name'] = $validator->validate('goods_name');
        $searchFormQuery['g.goods_sn']   = $validator->validate('goods_sn');
        $searchFormQuery['g.warehouse']  = $validator->validate('warehouse');
        $searchFormQuery['g.shelf']      = $validator->validate('shelf');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 构造查询条件
        $authSupplierUser                  = AuthHelper::getAuthUser();
        $searchFormQuery['g.suppliers_id'] = intval($authSupplierUser['suppliers_id']);

        // 建立查询条件
        $searchParamArray = QueryBuilder::buildSearchParamArray($searchFormQuery);

        // 查询商品列表
        $totalCount = SearchHelper::count(SearchHelper::Module_Goods, $searchParamArray);
        if ($totalCount <= 0) { // 没商品，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Goods/Search');
        }

        // 商品列表
        $goodsArray = SearchHelper::search(
            SearchHelper::Module_Goods,
            $this->searchFieldSelector,
            $searchParamArray,
            array(array('g.goods_id', 'desc')),
            $pageNo * $pageSize,
            $pageSize
        );

        $system_url_base_array = json_decode(ManageThemePlugin::getOptionValue('system_url_base_array'), true);

        foreach ($goodsArray as &$goodsItem) {
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

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('goodsArray', $goodsArray);

        out_display:
        $smarty->display('goods_search.tpl');
    }

    public function Statistics($f3)
    {
        global $smarty;

        $errorMessage  = '';
        $smartyCacheId = null;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $goods_id  = $validator->required()->digits()->min(1)->validate('goods_id');
        if (!$this->validate($validator)) {
            $errorMessage = '商品ID非法';
            goto out_fail;
        }

        // 生成 smarty 的缓存 id
        $smartyCacheId = 'Goods|' . $goods_id . '|Search|Statistics';

        enableSmartyCache(true, 300); // 缓存 5 分钟

        if ($smarty->isCached('goods_search_ajax_statistics.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // 权限检查
        $authUser          = AuthHelper::getAuthUser();
        $goodsBasicService = new GoodsBasicService();
        $goodsInfo         = $goodsBasicService->loadGoodsById($goods_id, 300); // 缓存 300 秒

        if ($goodsInfo['suppliers_id'] !== $authUser['suppliers_id']) {
            $errorMessage = '商品ID非法';
            goto out_fail;
        }

        // 查询统计结果
        $statisticsService = new Statistics();
        $statistics        = $statisticsService->goodsSaleStat($goods_id, 300); // 缓存 300 秒

        $smarty->assign('goodsInfo', $goodsInfo);
        $smarty->assign('statistics', $statistics);

        out_fail:
        $smarty->assign('errorMessage', $errorMessage);

        if (!empty($errorMessage)) { // 有错误，不缓存
            enableSmartyCache(false, 0);
        }

        out_display:
        $smarty->display('goods_search_ajax_statistics.tpl', $smartyCacheId);
    }

}
