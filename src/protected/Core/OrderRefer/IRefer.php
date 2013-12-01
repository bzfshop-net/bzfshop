<?php
/**
 * 记录订单来源的接口定义
 *
 */

namespace Core\OrderRefer;


interface IRefer
{

    /**
     * 设置 order_refer 信息，用于在用户真的下单的时候写入到数据库中
     *
     * @param object $f3
     * @param array  $orderRefer    order_refer 信息的数组
     * @param int    $duration      外部信息应该保持多久
     *
     * @return boolean 是否发生了变化
     */
    public function setOrderRefer($f3, &$orderRefer, &$duration);

    /**
     * 有些 order_refer 要求需要推送信息，比如 360CPS ，亿起发 之类需要推送订单
     * 这里返回推送地址
     *
     * @return string|array 返回推送地址，可能有多个推送地址
     *
     * @param object $f3
     * @param object $orderRefer 记录对象
     *
     */
    public function getOrderReferNotifyUrl($f3, $orderRefer);

    /**
     * 有些 order_refer 要求需要推送信息，比如 360CPS ，亿起发 之类需要推送订单
     *
     * @param object $f3
     * @param object $orderRefer 记录对象
     *
     */
    public function notifyOrderRefer($f3, $orderRefer);

    /**
     * 用于 CPS 结算，根据用户的订单情况，返回 cps的结算费率例如 10% ，或者固定费用例如 10元 一单
     *
     * @param $orderRefer
     * @param $orderInfo
     * @param $orderGoods
     *
     * @return array 例如 array('cps_rate' => 0.10, 'cps_fix_fee' => 10)，如果没有参数返回 false
     */
    public function getCpsParam($orderRefer, $orderInfo, $orderGoods);

}
