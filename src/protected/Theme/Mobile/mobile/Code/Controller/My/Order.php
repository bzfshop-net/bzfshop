<?php

/**
 * @author QiangYu
 *
 * 我的订单控制器
 *
 * */

namespace Controller\My;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Plugin\ThemeHelper;
use Core\Search\SearchHelper;
use Core\Service\Order\Goods as OrderGoodsService;

class Order extends \Controller\AuthController
{
    private $searchExtraCondArray;

    public function __construct()
    {
        $currentThemeInstance = ThemeHelper::getCurrentSystemThemeInstance();

        // 我们只搜索特定的系统的订单
        $this->searchExtraCondArray = array(
            array(QueryBuilder::buildOrderFilterForSystem($currentThemeInstance->getOrderFilterSystemArray())),
        );
    }

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

        if (!$this->validate($validator)) {
            goto out_display;
        }

        $userInfo = AuthHelper::getAuthUser();

        // 构造查询条件
        $searchFormQuery               = array();
        $searchFormQuery['oi.user_id'] = $userInfo['user_id'];

        // 合并查询参数
        $searchParamArray =
            array_merge(QueryBuilder::buildSearchParamArray($searchFormQuery), $this->searchExtraCondArray);

        // 查询订单
        $totalCount = SearchHelper::count(SearchHelper::Module_OrderGoodsOrderInfo, $searchParamArray);
        if ($totalCount <= 0) { // 没订单，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/My/Order');
        }

        // 订单排序
        $orderByParam   = array();
        $orderByParam[] = array('og.rec_id', 'desc');

        // 订单列表
        $orderGoodsArray =
            SearchHelper::search(
                SearchHelper::Module_OrderGoodsOrderInfo,
                'og.order_id, og.goods_id, og.goods_attr, og.goods_number, og.goods_price, og.shipping_fee'
                . ', og.create_time, og.order_goods_status, oi.order_sn, oi.pay_time',
                $searchParamArray,
                $orderByParam,
                $pageNo * $pageSize,
                $pageSize
            );

        foreach ($orderGoodsArray as &$orderGoodsItem) {
            $orderGoodsItem['order_goods_status_desc'] =
                OrderGoodsService::$orderGoodsStatusDesc[$orderGoodsItem['order_goods_status']];
        }
        unset($orderGoodsItem);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('orderGoodsArray', $orderGoodsArray);

        out_display:
        $smarty->display('my_order.tpl', 'get');
    }

}
