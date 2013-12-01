<?php

namespace Controller\Thirdpart\YiqifaCps;


use Core\Helper\Utility\Money;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Service\Order\Goods as OrderGoodsService;
use Core\Service\Order\Order as OrderBasicService;
use Core\Service\Order\Refer as OrderReferService;
use Core\Service\User\User as UserBasicService;

class QueryOrder extends \Controller\BaseController
{

    /**
     * 额外的查询条件，用于对不同的系统做特殊的查询
     *
     * @var string
     */
    public static $extraQueryCond = array('oi.system_id = ?', 'groupon');

    /**
     * 取得一个订单的输出 Txt
     *
     *
     */
    private function getOrderTxt($orderReferInfo, $orderGoodsArray)
    {
        $retTxt = '';

        $orderGoodsNameStr = '';
        $coupon            = $orderReferInfo['surplus'] + $orderReferInfo['bonus'];
        // 商品总价
        $orderAmount =
            $orderReferInfo['goods_amount'] - $orderReferInfo['discount'] - $orderReferInfo['extra_discount']
            - $orderReferInfo['refund'];

        // 商品总的额外退款金额
        $orderExtraRefund = 0;

        // 订单状态
        $orderStatus = 'refund';
        switch ($orderReferInfo['pay_status']) {
            case OrderBasicService::PS_UNPAYED:
                $orderStatus = 'unpay';
                break;
            case OrderBasicService::PS_PAYED:
                $orderStatus = 'pay';
                break;
            default:
                $orderStatus = 'refund';
        }

        // 对订单中每个商品单独计算
        foreach ($orderGoodsArray as $orderGoodsItem) {
            $orderGoodsNameStr .= '{(' . $orderGoodsItem['goods_id'] . ')' . $orderGoodsItem['goods_name'] . '['
                . $orderGoodsItem['goods_number'] . ' 件]},';
            if (OrderGoodsService::OGS_UNPAY != $orderGoodsItem['order_goods_status']
                && OrderGoodsService::OGS_PAY != $orderGoodsItem['order_goods_status']
            ) {
                // 有一个 order_goods 是退款状态，整个订单就是退款状态
                $orderStatus = 'refund';
            }

            // 累计额外退款的总金额
            $orderExtraRefund += $orderGoodsItem['extra_refund'];
        }

        $orderGoodsNameStr = str_replace('|', '_', $orderGoodsNameStr);
        $orderGoodsNameStr = mb_substr($orderGoodsNameStr, 0, 240);

        $referParamArray = json_decode($orderReferInfo['refer_param'], true);

        // CPS 应付总价
        $orderAmountOfCps = $orderAmount - $coupon - $orderExtraRefund;
        $orderAmountOfCps = ($orderAmountOfCps > 0) ? $orderAmountOfCps : 0;

        // QQ订单要多输出一条记录
        if ('qqlogin' == $orderReferInfo['login_type']) {

            // 取得QQ登陆用户的信息
            static $userBasicService = null;
            if (null == $userBasicService) {
                $userBasicService = new UserBasicService();
            }
            $userInfo = $userBasicService->loadUserById($orderReferInfo['user_id']);

            //取得 QQ 用户的 openId ，QQ登陆的用户 sns_login 例子  qq:476BA0B2332440759D485548637DFCDD
            $qqUserOpenId = $userInfo->sns_login;
            $qqUserOpenId = substr($qqUserOpenId, strpos($qqUserOpenId, ':') + 1);

            //输出 QQ 登陆的记录
            $retTxt .= $referParamArray['wi'] .
                "||" . date("Y-m-d H:i:s", Time::gmTimeToLocalTime($orderReferInfo['add_time'])) .
                "||" . $orderReferInfo['order_id'] .
                "||" . Money::toSmartyDisplay($orderAmountOfCps) .
                "||" . $orderGoodsNameStr .
                "||" . $orderStatus . //订单状态
                "||" . $orderStatus . //支付状态
                "||alipay" . //支付方式
                "||" . Money::toSmartyDisplay($orderReferInfo['shipping_fee']) . //运费
                "||" . Money::toSmartyDisplay($coupon) . //优惠金额
                "||0" . //优惠券卡号
                "||" . $qqUserOpenId .
                "||" . 'bangzhufu' .
                "||" . 'qqlogin003' .
                "||" . date("Y-m-d H:i:s", Time::gmTimeToLocalTime($orderReferInfo['update_time'])) .
                "\n";
        }

        if ('YIQIFACPS' != $orderReferInfo['utm_source']) {
            // 不是亿起发的订单
            goto out;
        }

        //输出 亿起发 的订单记录
        $retTxt .= $referParamArray['wi'] .
            "||" . date("Y-m-d H:i:s", Time::gmTimeToLocalTime($orderReferInfo['add_time'])) .
            "||" . $orderReferInfo['order_id'] .
            "||" . Money::toSmartyDisplay($orderAmountOfCps) .
            "||" . $orderGoodsNameStr .
            "||" . $orderStatus . //订单状态
            "||" . $orderStatus . //支付状态
            "||alipay" . //支付方式
            "||" . Money::toSmartyDisplay($orderReferInfo['shipping_fee']) . //运费
            "||" . Money::toSmartyDisplay($coupon) . //优惠金额
            "||0" . //优惠券卡号
            "||" .
            "||" .
            "||" .
            "||" . date("Y-m-d H:i:s", Time::gmTimeToLocalTime($orderReferInfo['update_time'])) .
            "\n";

        out:
        return $retTxt;
    }

    public function get($f3)
    {
        global $smarty;

        // 参数验证
        $validator       = new Validator($_GET);
        $cid             = $validator->required()->validate('cid');
        $queryCreateDate = $validator->digits()->validate('d');
        $queryUpdateDate = $validator->digits()->validate('ud');

        // 参数有错误
        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 不能同时为空
        if (empty($queryCreateDate) && empty($queryUpdateDate)) {
            goto out_fail;
        }

        if (!empty($queryCreateDate)) {
            // 参数检查，格式只能为 20120304 或者 2012030410
            $dateStrLen = strlen($queryCreateDate);
            if (8 != $dateStrLen && 10 != $dateStrLen) {
                goto out_fail;
            }
        }

        if (!empty($queryUpdateDate)) {
            // 参数检查，格式只能为 20120304 或者 2012030410
            $dateStrLen = strlen($queryUpdateDate);
            if (8 != $dateStrLen && 10 != $dateStrLen) {
                goto out_fail;
            }
        }

        enableSmartyCache(true, 60); //缓存1分钟，防止访问很频繁从而变成对网站的攻击

        $smartyCacheId = 'YiqifaCps|'
            . md5(
                json_encode(QueryOrder::$extraQueryCond) . '_' . $cid . '_' . $queryCreateDate . '_' . $queryUpdateDate
            );

        // 判断是否有缓存
        if ($smarty->isCached('yiqifacps_empty.tpl', $smartyCacheId)) {
            goto out_display;
        }

        $txtOutput = '';

        // 构造查询条件
        $condArray   = array();
        $condArray[] = array(
            'orf.utm_source = ? or (orf.login_type = ? and orf.utm_source is null)',
            'YIQIFACPS',
            'qqlogin'
        );

        // add_time 查询
        if (!empty($queryCreateDate)) {

            $startTime = 0;
            $endTime   = 0;

            if (strlen($queryCreateDate) == 8) {
                $startTime = strtotime($queryCreateDate);
                $endTime   = $startTime + 24 * 60 * 60;
            }

            if (strlen($queryCreateDate) == 10) {
                $datePart  = substr($queryCreateDate, 0, 8);
                $hourPart  = substr($queryCreateDate, 8, 2);
                $startTime = strtotime($datePart . " " . $hourPart . ":00:00");
                $endTime   = $startTime + 60 * 60;
            }

            $condArray[] = array('oi.add_time >= ? and oi.add_time < ?', $startTime, $endTime);
        }

        // update_time 查询
        if (!empty($queryUpdateDate)) {

            $startTime = 0;
            $endTime   = 0;

            if (strlen($queryUpdateDate) == 8) {
                $startTime = strtotime($queryUpdateDate);
                $endTime   = $startTime + 24 * 60 * 60;
            }

            if (strlen($queryUpdateDate) == 10) {
                $datePart  = substr($queryUpdateDate, 0, 8);
                $hourPart  = substr($queryUpdateDate, 8, 2);
                $startTime = strtotime($datePart . " " . $hourPart . ":00:00");
                $endTime   = $startTime + 60 * 60;
            }

            $condArray[] = array('oi.update_time >= ? and oi.update_time < ?', $startTime, $endTime);
        }

        /**
         * 加入额外的查询条件
         */
        if (!empty(QueryOrder::$extraQueryCond)) {
            $condArray[] = QueryOrder::$extraQueryCond;
        }

        // 查询订单，每次最多 2000 条数据，防止拖死系统
        $orderReferService   = new OrderReferService();
        $orderReferInfoArray =
            $orderReferService->fetchOrderReferInfo(
                $condArray,
                array('order' => 'oi.order_id asc'),
                0,
                2000
            );

        // 过滤 cid，只留下需要查询的 cid 数据，同时收集 order_id 用于查询 order_goods 信息
        $cidOrderReferInfoArray = array();
        $orderIdArray           = array();

        foreach ($orderReferInfoArray as $orderReferInfoItem) {
            $referParamArray = json_decode($orderReferInfoItem['refer_param'], true);
            if (!empty($referParamArray['cid']) && $referParamArray['cid'] != $cid) {
                //不是要查询的订单，跳过
                continue; // ==== 为了测试临时注释的代码
            }
            $cidOrderReferInfoArray[] = $orderReferInfoItem;
            $orderIdArray[]           = $orderReferInfoItem['order_id'];
        }
        unset($orderReferInfoArray); //清除旧数据
        $orderReferInfoArray = $cidOrderReferInfoArray;
        unset($cidOrderReferInfoArray); //清除临时数据

        if (empty($orderReferInfoArray)) {
            // 没有数据，跳出
            goto out;
        }

        $orderGoodsArray = $orderReferService->_fetchArray(
            'order_goods', //table
            '*', // fields
            array(array(QueryBuilder::buildInCondition('order_id', $orderIdArray))),
            array('order' => 'order_id asc, rec_id asc'),
            0,
            10000
        ); //最多查询 10000 条记录，防止拖死系统

        // 建立 order_id --> array( order_goods ) 的反查表
        $orderIdToOrderGoodsArray = array();
        foreach ($orderGoodsArray as $orderGoodsItem) {
            if (!isset($orderIdToOrderGoodsArray[$orderGoodsItem['order_id']])) {
                $orderIdToOrderGoodsArray[$orderGoodsItem['order_id']] = array();
            }
            $orderIdToOrderGoodsArray[$orderGoodsItem['order_id']][] = $orderGoodsItem;
        }

        // 根据订单构建 CPS 返回记录
        foreach ($orderReferInfoArray as $orderReferItem) {
            if (isset($orderIdToOrderGoodsArray[$orderReferItem['order_id']])) {
                $txtOutput .= $this->getOrderTxt(
                    $orderReferItem,
                    $orderIdToOrderGoodsArray[$orderReferItem['order_id']]
                );
            }
        }

        out:
        $smarty->assign('outputContent', $txtOutput);

        out_display: //成功从这里返回
        header('Content-Type:text/plain;charset=utf-8');
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 //查询信息
        $smarty->display('yiqifacps_empty.tpl', $smartyCacheId);
        return;

        out_fail: // 失败从这里返回
        echo "paramters error";
    }

    public function post($f3)
    {
        // 把 post 的值都给 GET
        $get = $f3->get('GET');
        $get = array_merge($get, $f3->get('POST'));
        $f3->set('GET', $get);

        $this->get($f3);
    }
}

