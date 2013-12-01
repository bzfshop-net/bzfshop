<?php

/**
 *
 * @author QiangYu
 *
 * 订单的来源记录操作
 *
 * */

namespace Core\Service\Order;

use Core\Helper\Utility\Validator;

class Refer extends \Core\Service\BaseService
{
    /**
     * 通过 order_id 取得 order_refer 记录
     *
     * @param  int $order_id
     * @param int  $ttl
     *
     * @return object
     */
    public function loadOrderReferByOrderId($order_id, $ttl = 0)
    {
        return $this->_loadById('order_refer', 'order_id = ?', $order_id, $ttl);
    }

    /**
     * 取得 order_refer, order_info 两个联表查询的记录
     *
     * @param $condArray
     * @param $optionArray
     * @param $offset
     * @param $limit
     * @param $ttl
     */
    public function fetchOrderReferInfo($condArray, $optionArray, $offset, $limit, $ttl = 0)
    {

        $queryCondArray = array(array('orf.order_id = oi.order_id'));
        $queryCondArray = array_merge($queryCondArray, $condArray);

        return $this->_fetchArray(
            array('order_refer' => 'orf', 'order_info' => 'oi'), // table
            // fields
            'orf.login_type, orf.refer_host, orf.refer_param, orf.utm_source, orf.utm_medium, oi.*',
            $queryCondArray, //查询过滤条件
            $optionArray, // 排序
            $offset,
            $limit,
            $ttl
        );

    }
}

