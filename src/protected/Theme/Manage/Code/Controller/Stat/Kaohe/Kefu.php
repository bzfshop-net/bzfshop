<?php

/**
 * @author QiangYu
 *
 * 业绩考核统计
 *
 * */

namespace Controller\Stat\Kaohe;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Search\SearchHelper;
use Core\Service\Order\Goods as OrderGoodsService;

class Kefu extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;

        if (!$f3->get('GET')) {
            // 没有任何查询，直接显示空页面
            goto out;
        }

        // 参数验证
        $validator = new Validator($f3->get('GET'));

        //订单表单查询
        $searchFormQuery = array();

        //下单时间
        $addTimeStartStr                = $validator->validate('add_time_start');
        $addTimeStart                   = Time::gmStrToTime($addTimeStartStr) ? : null;
        $addTimeEndStr                  = $validator->validate('add_time_end');
        $addTimeEnd                     = Time::gmStrToTime($addTimeEndStr) ? : null;
        $searchFormQuery['oi.add_time'] = array($addTimeStart, $addTimeEnd);

        //付款时间
        $payTimeStartStr                = $validator->validate('pay_time_start');
        $payTimeStart                   = Time::gmStrToTime($payTimeStartStr) ? : null;
        $payTimeEndStr                  = $validator->validate('pay_time_end');
        $payTimeEnd                     = Time::gmStrToTime($payTimeEndStr) ? : null;
        $searchFormQuery['oi.pay_time'] = array($payTimeStart, $payTimeEnd);

        if (!($addTimeStart || $addTimeEnd || $payTimeStart || $payTimeEnd)) {
            $this->addFlashMessage('必须最少选择一个时间');
            goto out;
        }

        $searchFormQuery['oi.kefu_user_id'] =
            $validator->required('必须选择一个客服')->digits()->filter('ValidatorIntValue')->validate('kefu_user_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        // 权限检查
        $authAdminUser = AuthHelper::getAuthUser();
        // 客服可以查看自己的页面
        if ($searchFormQuery['oi.kefu_user_id'] !== $authAdminUser['user_id']) {
            // 用户查看的不是自己的业绩，需要特别的权限
            if (!$this->hasPrivilege('manage_stat_kaohe_kefu')) {
                $this->addFlashMessage('你没有权限查看别人的业绩统计');
                goto out;
            }
        }

        // 统计订单金额总和
        $orderGoodsStat = SearchHelper::search(
            SearchHelper::Module_OrderGoodsOrderInfo,
            'og.order_goods_status' .
            ',count(1) as total_order_goods_count' .
            ',sum(og.goods_price) as total_goods_price' .
            ',sum(og.shipping_fee) as total_shipping_fee' .
            ',sum(og.discount) as total_discount' .
            ',sum(og.extra_discount) as total_extra_discount' .
            ',sum(og.refund) as total_refund' .
            ',sum(og.extra_refund) as total_extra_refund',
            // fields
            QueryBuilder::buildSearchParamArray($searchFormQuery),
            array(array('og.order_goods_status', 'asc')),
            0,
            $f3->get('sysConfig[max_query_record_count]'),
            'og.order_goods_status' // group by
        );

        // 订单状态显示
        foreach ($orderGoodsStat as &$orderGoodsStatItem) {
            $orderGoodsStatItem['order_goods_status_desc'] =
                OrderGoodsService::$orderGoodsStatusDesc[$orderGoodsStatItem['order_goods_status']];
        }
        unset($orderGoodsStatItem);

        // 统计总结果
        $orderGoodsStatTotal                            = array();
        $orderGoodsStatTotal['total_order_goods_count'] = 0;
        $orderGoodsStatTotal['total_goods_price']       = 0;
        $orderGoodsStatTotal['total_shipping_fee']      = 0;
        $orderGoodsStatTotal['total_discount']          = 0;
        $orderGoodsStatTotal['total_extra_discount']    = 0;
        $orderGoodsStatTotal['total_refund']            = 0;
        $orderGoodsStatTotal['total_extra_refund']      = 0;

        foreach ($orderGoodsStat as $orderGoodsStatItem) {

            // 未付款的订单不计入统计
            if ($orderGoodsStatItem['order_goods_status'] < OrderGoodsService::OGS_PAY) {
                continue;
            }

            // 统计计算
            $orderGoodsStatTotal['total_order_goods_count'] += $orderGoodsStatItem['total_order_goods_count'];
            $orderGoodsStatTotal['total_goods_price'] += $orderGoodsStatItem['total_goods_price'];
            $orderGoodsStatTotal['total_shipping_fee'] += $orderGoodsStatItem['total_shipping_fee'];
            $orderGoodsStatTotal['total_discount'] += $orderGoodsStatItem['total_discount'];
            $orderGoodsStatTotal['total_extra_discount'] += $orderGoodsStatItem['total_extra_discount'];
            $orderGoodsStatTotal['total_refund'] += $orderGoodsStatItem['total_refund'];
            $orderGoodsStatTotal['total_extra_refund'] += $orderGoodsStatItem['total_extra_refund'];
        }

        // 给 smarty 赋值
        $smarty->assign('orderGoodsStat', $orderGoodsStat);
        $smarty->assign('orderGoodsStatTotal', $orderGoodsStatTotal);

        out:
        $smarty->display('stat_kaohe_kefu.tpl');
    }

}
