<?php

namespace Plugin\Thirdpart\YiqifaCps;

use Core\Helper\Utility\Money;
use Core\Helper\Utility\Time;
use Core\OrderRefer\AbstractOrderRefer;
use Core\Service\Order\Order as OrderBasicService;
use Core\Service\User\User as UserBasicService;

class YiqifaCpsRefer extends AbstractOrderRefer
{
    /**
     * 亿起发的推送参数
     *
     * @var string
     */
    public static $notifyParamDt = 'web';

    /**
     * 取哪个 cps rate
     *
     * @var string
     */
    public static $cpsRateKey = 'yiqifacps_rate_web';

    //const yiqifaNotifyGateway="http://o.test.yiqifa.com/servlet/handleCpsIn?";  // for testing purpose
    const yiqifaNotifyGateway = 'http://o.yiqifa.com/servlet/handleCpsIn?';

    public function setOrderRefer($f3, &$orderRefer, &$duration)
    {
        // 我们在 Redirect 中实现设置 order_refer 信息，所以这里就不用设置了
        return false;
    }

    public function getOrderReferNotifyUrl($f3, $orderRefer)
    {

        if (!('YIQIFACPS' == $orderRefer->utm_source
            || ('qqlogin' == $orderRefer->login_type && empty($orderRefer->utm_source)))
        ) {
            // 不是亿起发的订单，或者不是QQ登陆订单
            return null;
        }

        $notifyUrlArray = array();

        // 取得记录的 亿起发 参数
        $referParamArray = json_decode($orderRefer->refer_param, true);
        // 取得订单信息
        $orderBasicService = new OrderBasicService();
        $orderInfo         = $orderBasicService->loadOrderInfoById($orderRefer->order_id);
        // 取得订单商品详情
        $orderGoodsArray = $orderBasicService->fetchOrderGoodsArray($orderRefer->order_id);
        // 计算佣金
        $orderGoodsNameStr = '';
        $coupon            = $orderInfo->surplus + $orderInfo->bonus;
        // 商品总价
        $orderAmount =
            $orderInfo->goods_amount - $orderInfo->discount - $orderInfo->extra_discount - $orderInfo->refund;

        // 商品总的额外退款金额
        $orderExtraRefund = 0;

        // 对订单中每个商品单独计算
        foreach ($orderGoodsArray as $orderGoodsItem) {
            $orderGoodsNameStr .= '{(' . $orderGoodsItem['goods_id'] . ')' . $orderGoodsItem['goods_name'] . '['
                . $orderGoodsItem['goods_number'] . ' 件]},';
            // 累计额外退款的总金额
            $orderExtraRefund += $orderGoodsItem['extra_refund'];
        }

        // CPS 应付总价
        $orderAmountOfCps = $orderAmount - $coupon - $orderExtraRefund;
        $orderAmountOfCps = ($orderAmountOfCps > 0) ? $orderAmountOfCps : 0;

        $orderGoodsNameStr = str_replace('|', '_', $orderGoodsNameStr);
        $orderGoodsNameStr = mb_substr($orderGoodsNameStr, 0, 240);

        // 推送QQ登陆订单
        if ('qqlogin' == $orderRefer->login_type) {

            // 取得QQ登陆用户的信息
            $userBasicService = new UserBasicService();
            $userInfo         = $userBasicService->loadUserById($orderInfo->user_id);

            //取得 QQ 用户的 openId ，QQ登陆的用户 sns_login 例子  qq:476BA0B2332440759D485548637DFCDD
            $qqUserOpenId = $userInfo->sns_login;
            $qqUserOpenId = substr($qqUserOpenId, strpos($qqUserOpenId, ':') + 1);

            // QQ 登陆需要额外推单
            $param = "cid=6406" .
                "&wid=435983" .
                "&qqoid=" . $qqUserOpenId .
                "&qqmid=bangzhufu" .
                "&ct=qqlogin003" .
                "&on=" . $orderInfo->order_id .
                "&ta=1" .
                "&dt=" . YiqifaCpsRefer::$notifyParamDt .
                "&pp=" . Money::toSmartyDisplay($orderAmountOfCps) .
                "&sd=" . urlencode(date("Y-m-d H:i:s", Time::gmTimeToLocalTime($orderInfo->add_time))) .
                "&os=pay" .
                "&ps=pay" .
                "&pw=alipay" .
                "&far=" . Money::toSmartyDisplay($orderInfo->shipping_fee) .
                "&fav=" . Money::toSmartyDisplay($coupon) .
                "&fac=0" .
                "&encoding=utf-8";

            // QQ 登陆订单推送
            $notifyUrlArray[] = YiqifaCpsRefer::yiqifaNotifyGateway . $param;
        }

        if ('YIQIFACPS' != $orderRefer->utm_source) {
            // 不是亿起发的订单
            goto out;
        }

        // 亿起发 正常 CPS 推单
        $param = "cid=" . $referParamArray['cid'] .
            "&wi=" . $referParamArray['wi'] .
            "&on=" . $orderInfo->order_id .
            "&ta=1" .
            "&pna=" . urlencode($orderGoodsNameStr) .
            "&dt=" . YiqifaCpsRefer::$notifyParamDt .
            "&pp=" . Money::toSmartyDisplay($orderAmountOfCps) .
            "&sd=" . urlencode(date("Y-m-d H:i:s", Time::gmTimeToLocalTime($orderInfo->add_time))) .
            "&os=pay" .
            "&ps=pay" .
            "&pw=alipay" .
            "&far=" . Money::toSmartyDisplay($orderInfo->shipping_fee) .
            "&fav=" . Money::toSmartyDisplay($coupon) .
            "&fac=0" .
            "&encoding=utf-8";

        // 亿起发订单推送
        $notifyUrlArray[] = YiqifaCpsRefer::yiqifaNotifyGateway . $param;

        out:
        return $notifyUrlArray;
    }

    public function notifyOrderRefer($f3, $orderRefer)
    {
        global $logger;

        $notifyUrlArray = $this->getOrderReferNotifyUrl($f3, $orderRefer);
        if (empty($notifyUrlArray)) {
            return;
        }

        // 调用 notifyUrl
        foreach ($notifyUrlArray as $urlCall) {

            $logger->addLogInfo(\Core\Log\Base::INFO, 'YIQIFACPS', $urlCall);

            $curlHandle = curl_init($urlCall);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

            try {
                $ret = curl_exec($curlHandle);
                $logger->addLogInfo(
                    \Core\Log\Base::INFO,
                    'YIQIFACPS',
                    "notify orderId:[" . $orderRefer->order_id . "] success, with ret = [" . $ret . "]"
                );
            } catch (Exception $e) {
                $logger->addLogInfo(
                    \Core\Log\Base::INFO,
                    'YIQIFACPS',
                    "notify orderId:[" . $orderRefer->order_id . "] fail, with exception [" . $e . "]"
                );
            }
        }

    }

    public function getCpsParam($orderRefer, $orderInfo, $orderGoods)
    {
        if (empty($orderRefer->utm_source) || 'YIQIFACPS' != $orderRefer->utm_source) {
            return false;
        }

        // 返回 yiqifacps 的费率
        return array('cps_rate' => YiqifaCpsPlugin::getOptionValue(YiqifaCpsRefer::$cpsRateKey));
    }

}
