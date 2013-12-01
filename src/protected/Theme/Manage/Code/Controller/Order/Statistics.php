<?php

/**
 * @author QiangYu
 *
 * order_goods 的统计
 *
 * */

namespace Controller\Order;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Search\SearchHelper;
use Core\Service\Order\Goods as OrderGoodsService;

class Statistics extends \Controller\AuthController
{

    public function ListGoods($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_statistics_listgoods');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));

        //订单表单查询
        $searchFormQuery = array();

        $systemId                        = $validator->validate('system_id');
        $searchFormQuery['oi.system_id'] = array('=', $systemId);

        //下单时间
        $createTimeStartStr                = $validator->validate('create_time_start');
        $createTimeStart                   = Time::gmStrToTime($createTimeStartStr) ? : null;
        $createTimeEndStr                  = $validator->validate('create_time_end');
        $createTimeEnd                     = Time::gmStrToTime($createTimeEndStr) ? : null;
        $searchFormQuery['og.create_time'] = array($createTimeStart, $createTimeEnd);

        if (empty($createTimeStart) || empty($createTimeEnd)) {
            goto out_display;
        }

        // 查询订单列表，最多查询 10000 条记录，防止拖死数据库
        $orderGoodsArray = SearchHelper::search(
            empty($systemId) ? SearchHelper::Module_OrderGoods : SearchHelper::Module_OrderGoodsOrderInfo,
            'og.rec_id, og.order_goods_status, og.goods_id, og.goods_admin_user_id, og.goods_admin_user_name, og.goods_name, og.goods_number',
            // fields
            QueryBuilder::buildSearchParamArray($searchFormQuery),
            array(array('og.rec_id', 'asc')),
            0,
            $f3->get('sysConfig[max_query_record_count]')
        );

        $goodsStatisticsArray = array();

        // 做商品统计
        foreach ($orderGoodsArray as $orderGoodsItem) {
            if (!isset($goodsStatisticsArray[$orderGoodsItem['goods_id']])) {
                $goodsStatisticsArray[$orderGoodsItem['goods_id']] = array();
                //商品信息
                $goodsStatisticsArray[$orderGoodsItem['goods_id']]['goods_id']   =
                    $orderGoodsItem['goods_id'];
                $goodsStatisticsArray[$orderGoodsItem['goods_id']]['goods_name'] =
                    $orderGoodsItem['goods_name'];

                // 商品对应的编辑信息
                $goodsStatisticsArray[$orderGoodsItem['goods_id']]['goods_admin_user_id']   =
                    $orderGoodsItem['goods_admin_user_id'];
                $goodsStatisticsArray[$orderGoodsItem['goods_id']]['goods_admin_user_name'] =
                    $orderGoodsItem['goods_admin_user_name'];

                //订单信息
                $goodsStatisticsArray[$orderGoodsItem['goods_id']]['total_order_number'] = 0;
                $goodsStatisticsArray[$orderGoodsItem['goods_id']]['unpay_order_number'] = 0;
                $goodsStatisticsArray[$orderGoodsItem['goods_id']]['pay_order_number']   = 0;
                //售出商品信息
                $goodsStatisticsArray[$orderGoodsItem['goods_id']]['total_goods_number'] = 0;
                $goodsStatisticsArray[$orderGoodsItem['goods_id']]['unpay_goods_number'] = 0;
                $goodsStatisticsArray[$orderGoodsItem['goods_id']]['pay_goods_number']   = 0;
            }

            $goodsStatisticsArray[$orderGoodsItem['goods_id']]['total_order_number'] += 1;
            $goodsStatisticsArray[$orderGoodsItem['goods_id']]['total_goods_number'] += $orderGoodsItem['goods_number'];

            switch ($orderGoodsItem['order_goods_status']) {
                case OrderGoodsService::OGS_UNPAY :
                    $goodsStatisticsArray[$orderGoodsItem['goods_id']]['unpay_order_number'] += 1;
                    $goodsStatisticsArray[$orderGoodsItem['goods_id']]['unpay_goods_number'] += $orderGoodsItem['goods_number'];
                    break;
                case OrderGoodsService::OGS_PAY :
                    $goodsStatisticsArray[$orderGoodsItem['goods_id']]['pay_order_number'] += 1;
                    $goodsStatisticsArray[$orderGoodsItem['goods_id']]['pay_goods_number'] += $orderGoodsItem['goods_number'];
                    break;
                default:
                    // 退款订单，不处理
                    break;
            }
        }

        // 按照订单数量排序
        $goodsIdTotalOrderNumberArray = array();
        foreach ($goodsStatisticsArray as $goodsStatisticsItem) {
            $goodsIdTotalOrderNumberArray[] =
                str_pad($goodsStatisticsItem['total_order_number'], 7, '0', STR_PAD_LEFT)
                . '-' .
                str_pad($goodsStatisticsItem['goods_id'], 7, '0', STR_PAD_LEFT);
        }
        // 排序
        rsort($goodsIdTotalOrderNumberArray);
        $sortGoodsStatisticsArray = array();
        foreach ($goodsIdTotalOrderNumberArray as $item) {
            $tmpArray                   = explode('-', $item);
            $sortGoodsStatisticsArray[] = $goodsStatisticsArray[intval($tmpArray[1])];
        }

        // 给模板赋值
        $smarty->assign('sortGoodsStatisticsArray', $sortGoodsStatisticsArray);

        out_display:
        $smarty->display('order_statistics_listgoods.tpl');
    }

}
