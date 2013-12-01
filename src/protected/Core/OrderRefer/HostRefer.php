<?php
/**
 * @author QiangYu
 *
 * 简单的记录订单来源信息，包括
 *
 * refer_url :  来源的 URL
 * refer_host : 来源的 域名
 * utm_source : 和 Google Analytics 中的 utm_source 一样的设计
 * utm_medium : 和 Google Analytics 中的 utm_medium 一样的设计
 *
 */

namespace Core\OrderRefer;


class HostRefer extends AbstractOrderRefer
{
    public function setOrderRefer($f3, &$orderRefer, &$duration)
    {
        if (!array_key_exists('HTTP_REFERER', $_SERVER) || empty($_SERVER['HTTP_REFERER'])) {
            goto out_no_change;
        }

        // 解析 refer
        $referUrl = $_SERVER['HTTP_REFERER'];
        $hostInfo = parse_url($referUrl);

        if (empty($referUrl) || empty($hostInfo) || empty($hostInfo['host'])) {
            goto out_no_change;
        }

        if (empty($orderRefer['refer_host'])) {
            // 没有设置过 refer 信息，我们需要设置
            goto out_set_refer;
        }

        if ($orderRefer['refer_host'] == $hostInfo['host']) {
            // 已经设置过了 refer_host，不用再设置了
            goto out_no_change;
        }

        if ($f3->get('HOST') == $hostInfo['host']) {
            // 是本网站自身的 host，不要覆盖之前的已有信息
            goto out_no_change;
        }

        if (in_array($hostInfo['host'], $f3->get('sysConfig[order_refer_host_refer_exclude_host_array]'))) {
            // 在排除列表之中，不要保存记录
            goto out_no_change;
        }

        out_set_refer: // 这里记录 orderRefer 信息
        $orderRefer               = array(); //重置整个 order_refer
        $orderRefer['refer_url']  = $referUrl;
        $orderRefer['refer_host'] = $hostInfo['host'];
        $orderRefer['utm_source'] = @$_GET['utm_source'];
        $orderRefer['utm_medium'] = @$_GET['utm_medium'];
        return true;

        out_no_change:
        return false;
    }

}
