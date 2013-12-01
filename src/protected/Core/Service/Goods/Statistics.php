<?php

/**
 *
 * @author QiangYu
 *
 * 商品的统计操作类
 *
 * */

namespace Core\Service\Goods;

use Core\Helper\Utility\Validator;
use Core\Service\Order\Goods as OrderGoodsService;

class Statistics extends \Core\Service\BaseService
{

    /**
     * 统计商品的销售情况，包括有多少订单，卖了多少商品等
     *
     * @return array 格式如下：
     * array (
     *      'total_order' => 100,
     *      'pay_order' => 80,
     *      'unpay_order' => 10,
     *      'refund_order'  => 15,
     *
     *      'total_goods' => 200,
     *      'pay_goods' => 120,
     *      'unpay_goods' => 40,
     *      'refund_goods'  => 40,
     *
     *      'total_goods_price' => 10000,
     *      'total_suppliers_price' => 10000,
     *      'total_suppliers_shipping_fee' => 10000,
     *
     *      'goods_attr' => array(
     *           // 结构等同的统计，针对不同的商品组合选择
     *       )
     *
     * )
     *
     * @param int $goodsId 商品ID
     * @param int $ttl     缓存时间
     *
     */
    public function goodsSaleStat($goodsId, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('goodsId' => $goodsId, 'ttl' => $ttl));
        $goodsId   = $validator->required()->digits()->min(1)->validate('goodsId');
        $ttl       = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        $queryResult = $this->_fetchArray(
            'order_goods', // tables
            'order_id, order_goods_status, goods_number, goods_price, goods_attr, suppliers_price, suppliers_shipping_fee',
            // fields
            array(array('goods_id = ?', $goodsId)), // condArray
            array('order' => 'order_id asc'),
            0,
            0,
            $ttl
        );

        $statResult = array();

        $statResult['total_order']  = 0;
        $statResult['unpay_order']  = 0;
        $statResult['refund_order'] = 0;
        $statResult['pay_order']    = 0;

        $statResult['total_goods']  = 0;
        $statResult['unpay_goods']  = 0;
        $statResult['refund_goods'] = 0;
        $statResult['pay_goods']    = 0;

        $statResult['total_goods_price']            = 0;
        $statResult['total_suppliers_price']        = 0;
        $statResult['total_suppliers_shipping_fee'] = 0;

        $statResult['goods_attr'] = array();

        $lastOrderId = 0;
        foreach ($queryResult as $record) {

            // 商品属性统计
            if (!empty($record['goods_attr']) && !isset($statResult['goods_attr'][$record['goods_attr']])) {
                $statResult['goods_attr'][$record['goods_attr']] = array();

                $statResult['goods_attr'][$record['goods_attr']]['total_order']  = 0;
                $statResult['goods_attr'][$record['goods_attr']]['unpay_order']  = 0;
                $statResult['goods_attr'][$record['goods_attr']]['refund_order'] = 0;
                $statResult['goods_attr'][$record['goods_attr']]['pay_order']    = 0;

                $statResult['goods_attr'][$record['goods_attr']]['total_goods']  = 0;
                $statResult['goods_attr'][$record['goods_attr']]['unpay_goods']  = 0;
                $statResult['goods_attr'][$record['goods_attr']]['refund_goods'] = 0;
                $statResult['goods_attr'][$record['goods_attr']]['pay_goods']    = 0;
            }

            // 总体订单统计
            if ($lastOrderId != $record['order_id']) {
                $lastOrderId = $record['order_id'];
                $statResult['total_order'] += 1;
                switch ($record['order_goods_status']) {
                    case OrderGoodsService::OGS_UNPAY: // 未付款订单
                        $statResult['unpay_order'] += 1;
                        break;
                    case OrderGoodsService::OGS_PAY: // 付款订单
                        $statResult['pay_order'] += 1;
                        break;
                    default:
                        // 其它都是退款订单
                        $statResult['refund_order'] += 1;
                }
            }

            // 各个组合订单统计
            if (!empty($record['goods_attr'])) {
                $statResult['goods_attr'][$record['goods_attr']]['total_order'] += 1;
                switch ($record['order_goods_status']) {
                    case OrderGoodsService::OGS_UNPAY: // 未付款订单
                        $statResult['goods_attr'][$record['goods_attr']]['unpay_order'] += 1;
                        break;
                    case OrderGoodsService::OGS_PAY: // 付款订单
                        $statResult['goods_attr'][$record['goods_attr']]['pay_order'] += 1;
                        break;
                    default: // 其它都是退款订单
                        $statResult['goods_attr'][$record['goods_attr']]['refund_order'] += 1;
                }
            }

            // 商品统计
            $statResult['total_goods'] += $record['goods_number'];
            if (!empty($record['goods_attr'])) {
                $statResult['goods_attr'][$record['goods_attr']]['total_goods'] += $record['goods_number'];
            }

            switch ($record['order_goods_status']) {

                case OrderGoodsService::OGS_UNPAY: // 未付款订单
                    $statResult['unpay_goods'] += $record['goods_number'];
                    if (!empty($record['goods_attr'])) {
                        $statResult['goods_attr'][$record['goods_attr']]['unpay_goods'] += $record['goods_number'];
                    }
                    break;

                case OrderGoodsService::OGS_PAY: // 付款订单
                    $statResult['pay_goods'] += $record['goods_number'];
                    if (!empty($record['goods_attr'])) {
                        $statResult['goods_attr'][$record['goods_attr']]['pay_goods'] += $record['goods_number'];
                    }
                    // 金额统计
                    $statResult['total_goods_price'] += $record['goods_price'];
                    $statResult['total_suppliers_price'] += $record['suppliers_price'] * $record['goods_number'];
                    $statResult['total_suppliers_shipping_fee'] += $record['suppliers_shipping_fee'];
                    break;

                default:
                    $statResult['refund_goods'] += $record['goods_number'];
                    if (!empty($record['goods_attr'])) {
                        $statResult['goods_attr'][$record['goods_attr']]['refund_goods'] += $record['goods_number'];
                    }
            }
        }

        return $statResult;
    }

}
