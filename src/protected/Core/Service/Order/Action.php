<?php

/**
 *
 * @author QiangYu
 *
 * 订单的日志记录操作
 *
 * */

namespace Core\Service\Order;

use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;

class Action extends \Core\Service\BaseService
{

    /**
     * @param int    $order_id             order_info 表的 id
     * @param int    $rec_id               order_goods 表的 id
     * @param int    $order_status         order_info 中的订单状态
     * @param int    $pay_status           order_info 中的支付状态
     * @param int    $order_goods_status   order_goods 中的状态
     * @param string $action_note          操作备注
     * @param string $action_user          操作人
     * @param int    $action_place         不清楚用途，暂时为 0
     * @param int    $shipping_status      order_info 中的快递状态
     */
    public function logOrderAction(
        $order_id,
        $rec_id,
        $order_status,
        $pay_status,
        $order_goods_status,
        $action_note = '',
        $action_user,
        $action_place,
        $shipping_status
    ) {

        $orderAction = $this->_loadById('order_action', 'action_id = ?', 0);

        $orderAction->order_id           = $order_id;
        $orderAction->rec_id             = $rec_id;
        $orderAction->order_status       = $order_status;
        $orderAction->shipping_status    = $shipping_status;
        $orderAction->pay_status         = $pay_status;
        $orderAction->order_goods_status = $order_goods_status;
        $orderAction->action_note        = $action_note;
        $orderAction->action_user        = $action_user;
        $orderAction->action_place       = $action_place;
        $orderAction->log_time           = Time::gmTime();

        $orderAction->save();
    }

    /**
     * 取得订单对应的所有日志信息
     *
     * @param int $orderId  order_info 的 id
     * @param int $recId    order_goods 的 rec_id
     *
     * @return array
     */
    public function fetchOrderLogArray($orderId, $recId)
    {
        $condArray   = array();
        $condArray[] = array('order_id = ?', $orderId);

        if ($recId > 0) {
            $condArray[] = array('rec_id = 0 or rec_id = ?', $recId);
        }

        return $this->_fetchArray(
            'order_action',
            '*',
            $condArray,
            array('order' => 'action_id desc'),
            0,
            0
        );
    }

}
