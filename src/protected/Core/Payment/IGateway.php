<?php

namespace Core\Payment;

/**
 * 支付网关的通用接口定义
 *
 * @author QiangYu
 */
interface IGateway
{

    /**
     * 返回网关类型，用于标识到底是哪个网关
     *
     * @return string 网关类型
     */
    public function getGatewayType();

    /**
     * 返回处理的订单 ID
     *
     * @return int 订单ID
     */
    public function getOrderId();

    /**
     * 完成初始化工作，包括配置参数，检查环境等
     *
     * @return boolean
     *
     * @param array $paramArray 初始化参数数组
     */
    public function init(array $paramArray = null);

    /**
     * 获得支付的跳转链接，包括所有的加密参数
     *
     * @return string url 链接
     *
     * @param int    $orderId   订单数字ID
     * @param string $returnUrl 订单完成之后的返回 URL
     * @param string $notifyUrl 异步通知的Url
     */
    public function getRequestUrl($orderId, $returnUrl, $notifyUrl);

    /**
     * 处理从支付系统返回，调用 returnUrl 的处理
     *
     * @return boolean
     *
     * @param object $f3 系统 $f3 框架
     */
    public function doReturnUrl($f3);

    /**
     * 处理从支付系统返回，调用 notifyUrl 的处理
     *
     * @return boolean
     *
     * @param object $f3 系统 $f3 框架
     */
    public function doNotifyUrl($f3);
}

