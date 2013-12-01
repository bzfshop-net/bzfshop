<?php
/**
 *
 * 处理一组 order_refer 的工作类
 *
 */

namespace Core\OrderRefer;

use Core\Helper\Utility\Route as RouteHelper;
use Core\Service\Order\Refer;

class ReferHelper
{

    // 用于保存 order_refer 信息的 Key,目前我们以 json 格式保存在 cookie 里面
    public static $orderReferStorageKey = 'BZFOrderRefer';
    // 保存多长时间
    public static $orderReferSaveDuration = -1;

    // $orderReferCache 用于保存值，由于我们采用 Cookie 实现，如果 setcookie() 多次，然后再 getcookie 未必起作用
    private static $orderReferCache = null;

    // IRefer 数组，用于处理不同情况下的数据记录和推送
    protected static $IReferArray = array();

    /**
     * 添加一个 IRefer 的实例
     *
     * @param string $name
     * @param        $referItem  IRefer 对象
     */
    public static function addReferItem($name, $referItem)
    {

        if (!$referItem instanceof IRefer) {
            throw new \Exception('referItem is not an instance of IRefer');
        }

        static::$IReferArray[$name] = $referItem;
    }

    /**
     * 删除一个 referItem
     *
     * @param string $name
     */
    public static function removeReferItem($name)
    {
        if (array_key_exists($name, static::$IReferArray)) {
            unset(static::$IReferArray[$name]);
        }
    }

    /**
     * 从存储中解析出 $orderRefer
     *
     * @return array|mixed
     */
    public static function parseOrderRefer($f3)
    {
        if (!empty(static::$orderReferCache)) {
            return static::$orderReferCache;
        }

        // 从 Cookie 中解析 order_refer 数据
        $orderRefer = array_key_exists(static::$orderReferStorageKey, $_COOKIE) ?
            $orderRefer = json_decode($_COOKIE[static::$orderReferStorageKey], true) : array();

        if (!is_array($orderRefer) || empty($orderRefer)) {
            $orderRefer = array();
        }

        // 把值缓存起来
        static::$orderReferCache = $orderRefer;
        return static::$orderReferCache;
    }

    /**
     * 把 $orderRefer 保存到存储中
     * @param     $f3
     * @param     $orderRefer
     * @param int $duration 保存有效时间
     */
    public static function saveOrderRefer($f3, $orderRefer, $duration)
    {
        // 保存 Cache
        static::$orderReferCache = $orderRefer;

        if ($duration > 0) {
            $duration += time();
        } else {
            $duration = 0;
        }

        static::$orderReferSaveDuration = $duration;
    }

    /***
     * 同步数据到 Cookie
     *
     * @param $f3
     */
    public static function syncOrderReferStorage($f3)
    {
        if (static::$orderReferSaveDuration >= 0) {
            setcookie(
                static::$orderReferStorageKey,
                json_encode(static::$orderReferCache),
                static::$orderReferSaveDuration,
                $f3->get('BASE') . '/',
                RouteHelper::getCookieDomain(),
                false,
                true
            );
        }
    }

    /**
     * 设置 order_refer 信息
     *
     * @param $f3
     */
    public static function setOrderRefer($f3)
    {
        // 解析 order_refer
        $orderRefer = static::parseOrderRefer($f3);

        // 调用各个组件批处理
        $duration = 0;
        $isChange = false;
        foreach (static::$IReferArray as $IReferItem) {
            $isChange = ($IReferItem->setOrderRefer($f3, $orderRefer, $duration) || $isChange);
        }
        if ($isChange) {
            // 发生过变化才重新写入到 Cookie 中
            static::saveOrderRefer($f3, $orderRefer, $duration);
        }
    }

    /**
     * 指定设置 orderRefer 信息
     *
     * @param object  $f3
     * @param array   $orderReferArray
     * @param int     $durationValue
     * @param boolean $replace 是否替换已有的 order_refer 信息，缺省为合并
     */
    public static function setOrderReferSpecific($f3, $orderReferArray, $durationValue, $replace = false)
    {

        $orderRefer = array();
        if (!$replace) {
            // 解析 order_refer
            $orderRefer = static::parseOrderRefer($f3);
        }

        $orderRefer = array_merge($orderRefer, $orderReferArray);
        static::saveOrderRefer($f3, $orderRefer, $durationValue);
    }

    /**
     * 取得订单 notify url 数组
     *
     * @param $f3
     * @param $order_id
     *
     * @return array  如果没有推送地址则返回空的数组
     */
    public static function getOrderReferNotifyUrlArray($f3, $order_id)
    {
        $orderReferService = new Refer();
        $orderRefer        = $orderReferService->loadOrderReferByOrderId($order_id);

        $urlArray = array();

        foreach (static::$IReferArray as $IReferItem) {
            $notifyUrl = $IReferItem->getOrderReferNotifyUrl($f3, $orderRefer);
            if ($notifyUrl) {
                if (is_array($notifyUrl)) {
                    $urlArray = array_merge($urlArray, $notifyUrl);
                } else {
                    $urlArray[] = $notifyUrl;
                }
            }
        }

        return $urlArray;
    }

    /**
     * 对订单的 order_refer 信息做通知
     *
     * @param $f3
     * @param $order_id
     */
    public static function notifyOrderRefer($f3, $order_id)
    {
        $orderReferService = new Refer();
        $orderRefer        = $orderReferService->loadOrderReferByOrderId($order_id);

        foreach (static::$IReferArray as $IReferItem) {
            $IReferItem->notifyOrderRefer($f3, $orderRefer);
        }

    }

    /**
     * 用于 CPS 结算，根据用户的订单情况，返回 cps的结算费率例如 10% ，或者固定费用例如 10元 一单
     *
     * @param $orderRefer
     * @param $orderInfo
     * @param $orderGoods
     *
     * @return array 例如 array('cps_rate' => 0.10, 'cps_fee' => 10)，如果没有参数返回 false
     */
    public static function getCpsParam($orderRefer, $orderInfo, $orderGoods)
    {
        foreach (static::$IReferArray as $IReferItem) {
            $ret = $IReferItem->getCpsParam($orderRefer, $orderInfo, $orderGoods);
            // 只要有一个 CPS 组件返回了费率我们就不再查询后续的 CPS 组件了
            if ($ret && !empty($ret)) {
                return $ret;
            }
        }
    }

}