<?php

/**
 * 财付通支付网关的实现
 *
 * @author QiangYu
 *
 */

namespace Payment\Tenpay;

use Core\Helper\Utility\Money;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Log\Base;
use Core\Payment\AbstractBaseGateway;
use Core\Service\Order\Order as OrderBasicService;
use Core\Service\Order\Payment as OrderPaymentService;
use Payment\Tenpay\ClientResponseHandler;
use Payment\Tenpay\RequestHandler;
use Payment\Tenpay\ResponseHandler;
use Payment\Tenpay\TenpayHttpClient;
use Plugin\Payment\Tenpay\TenpayPlugin;


/**
 * 支付网关的通用接口定义
 *
 * @author QiangYu
 */
class Gateway extends AbstractBaseGateway
{

    private $orderId = null;
    private $partnerId = null; //财付通商户号
    private $partnerKey = null; //财付通密钥
    private $tradeMode = null; //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
    private $bankType = null; //如果是网银直连的话，这里是银行的编码，比如 1001 招商银行
    private $payId = null; //用于填充订单 order_info  中的 pay_id 字段

    public function getGatewayType()
    {
        return 'tenpay'; //注意大小写
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function init(array $paramArray = null)
    {
        $this->partnerId  = TenpayPlugin::getOptionValue('partner_id');
        $this->partnerKey = TenpayPlugin::getOptionValue('partner_key');
        $this->tradeMode  = TenpayPlugin::getOptionValue('trade_mode');
        $this->payId      = TenpayPlugin::getOptionValue('pay_id');

        // 如果是网银直连，设置网银参数
        $this->bankType = ($paramArray && isset($paramArray[1]) ? $paramArray[1] : 'DEFAULT');
        return true;
    }

    public function getRequestUrl($orderId, $returnUrl, $notifyUrl)
    {

        // 参数验证
        $validator = new Validator(array('orderId' => $orderId, 'returnUrl' => $returnUrl, 'notifyUrl' => $notifyUrl));
        $orderId   = $validator->required()->digits()->min(1)->validate('orderId');
        $returnUrl = $validator->required()->validate('returnUrl');
        $notifyUrl = $validator->required()->validate('notifyUrl');
        $this->validate($validator);

        //设置订单 ID
        $this->orderId = $orderId;

        // 取得订单
        $orderBasicService = new OrderBasicService();
        $orderInfo         = $orderBasicService->loadOrderInfoById($orderId);
        if (empty($orderInfo) || $orderInfo->isEmpty()) {
            throw new \InvalidArgumentException('invalid order_id [' . $orderId . ']');
        }

        $desc = $orderInfo['order_id'] . '|'
            . Money::toSmartyDisplay($orderInfo['order_amount']) . '|'
            . $orderInfo['system_id'];

        // 创建支付请求对象 
        $reqHandler = new RequestHandler();
        $reqHandler->init();
        $reqHandler->setKey($this->partnerKey);
        $reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");

        //设置支付参数 
        $reqHandler->setParameter("partner", $this->partnerId);
        $reqHandler->setParameter("out_trade_no", $orderInfo['order_sn'] . '_' . $orderId);
        $reqHandler->setParameter("total_fee", Money::storageToCent($orderInfo['order_amount'])); //总金额
        $reqHandler->setParameter("return_url", $returnUrl);
        $reqHandler->setParameter("notify_url", $notifyUrl);
        $reqHandler->setParameter("body", $desc);
        $reqHandler->setParameter("bank_type", $this->bankType); //银行类型，默认为财付通, DEFAULT
        //用户ip
        $reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']); //客户端IP
        $reqHandler->setParameter("fee_type", "1"); //币种
        $reqHandler->setParameter("subject", mb_substr($desc, 0, 32)); //商品名称，（中介交易时必填）
        //系统可选参数
        $reqHandler->setParameter("sign_type", "MD5"); //签名方式，默认为MD5，可选RSA
        $reqHandler->setParameter("service_version", "1.0"); //接口版本号
        $reqHandler->setParameter("input_charset", "utf-8"); //字符集
        $reqHandler->setParameter("sign_key_index", "1"); //密钥序号
        //业务可选参数
        $reqHandler->setParameter("attach", ""); //附件数据，原样返回就可以了
        $reqHandler->setParameter("product_fee", ""); //商品费用
        $reqHandler->setParameter("transport_fee", "0"); //物流费用
        $reqHandler->setParameter(
            "time_start",
            date("YmdHis", Time::gmTimeToChinaTime($orderInfo['add_time']))
        ); //订单生成时间
        $reqHandler->setParameter("time_expire", ""); //订单失效时间
        $reqHandler->setParameter("buyer_id", ""); //买方财付通帐号
        $reqHandler->setParameter("goods_tag", ""); //商品标记
        $reqHandler->setParameter("trade_mode", $this->tradeMode); //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
        $reqHandler->setParameter("transport_desc", ""); //物流说明
        $reqHandler->setParameter("trans_type", "1"); //交易类型
        $reqHandler->setParameter("agentid", ""); //平台ID
        $reqHandler->setParameter("agent_type", ""); //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
        $reqHandler->setParameter("seller_id", ""); //卖家的商户号
//请求的URL
        $reqUrl = $reqHandler->getRequestURL();

//获取debug信息,建议把请求和debug信息写入日志，方便定位问题

        $debugInfo = $reqHandler->getDebugInfo();

        return $reqUrl;
    }

    public function doNotifyUrl($f3)
    {
        // 记录所有的参数，方便调试查找
        printLog(
            'Tenpay Notify Paramters ： ' . print_r($_REQUEST, true),
            'PAYMENT',
            Base::INFO
        );

        /* 创建支付应答对象 */
        $resHandler = new ResponseHandler();
        $resHandler->setKey($this->partnerKey);

        //判断签名
        if (!$resHandler->isTenpaySign()) {
            //签名错误
            printLog(
                'Tenpay Notify sign error：' . $resHandler->getDebugInfo(),
                'PAYMENT',
                Base::ERROR
            );
            goto out_fail;
        }

        //通知id
        $notify_id = $resHandler->getParameter("notify_id");

        //通过通知ID查询，确保通知来至财付通
        //创建查询请求
        $queryReq = new RequestHandler();
        $queryReq->init();
        $queryReq->setKey($this->partnerKey);
        $queryReq->setGateUrl("https://gw.tenpay.com/gateway/simpleverifynotifyid.xml");
        $queryReq->setParameter("partner", $this->partnerId);
        $queryReq->setParameter("notify_id", $notify_id);

        //通信对象
        $httpClient = new TenpayHttpClient();
        $httpClient->setTimeOut(10);
        //设置请求内容
        $httpClient->setReqContent($queryReq->getRequestURL());

        //后台调用
        if (!$httpClient->call()) {
            //通信失败 后台调用通信失败,写日志，方便定位问题
            printLog(
                'Tenpay verify notify_id connect error ：responseCode[' . $httpClient->getResponseCode() . ']'
                . $httpClient->getErrInfo(),
                'PAYMENT',
                Base::ERROR
            );
            goto out_fail;
        }

        //设置结果参数
        $queryRes = new ClientResponseHandler();
        $queryRes->setContent($httpClient->getResContent());
        $queryRes->setKey($this->partnerKey);

        if ($resHandler->getParameter("trade_mode") == "1") {
            //判断签名及结果（即时到帐）
            //只有签名正确,retcode为0，trade_state为0才是支付成功
            if (!($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0"
                && $resHandler->getParameter("trade_state") == "0")
            ) {
                $logMsg =
                    "Tenpay sign error or trade_state error : trade_state=" . $resHandler->getParameter("trade_state")
                    . ",retcode=" .
                    $queryRes->getParameter("retcode") . ",retmsg=" . $queryRes->getParameter("retmsg");

                printLog($logMsg, 'PAYMENT', Base::ERROR);
                //更多的错误信息方便调试
                printLog('Tenpay QueryRequestUrl:' . $queryReq->getRequestURL(), 'PAYMENT', Base::ERROR);
                printLog('Tenpay QueryRequestDebugInfo:' . $queryReq->getDebugInfo(), 'PAYMENT', Base::ERROR);
                printLog('Tenpay QueryResponseContent:' . $queryRes->getContent(), 'PAYMENT', Base::ERROR);
                printLog('Tenpay QueryResponseDebugInfo:' . $queryRes->getDebugInfo(), 'PAYMENT', Base::ERROR);
                goto out_fail;
            }
            //取结果参数做业务处理
            $out_trade_no = $resHandler->getParameter("out_trade_no");
            //财付通订单号
            $transaction_id = $resHandler->getParameter("transaction_id");
            //金额,以分为单位
            $total_fee = $resHandler->getParameter("total_fee");
            //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
            $discount = $resHandler->getParameter("discount");

            //------------------------------
            //处理业务开始
            //------------------------------

            if (empty($out_trade_no)) {
                printLog('Tenpay bad out_trade_no', 'PAYMENT', Base::ERROR);
                goto out_fail;
            }

            $paramArray = explode('_', $out_trade_no);
            if (empty($paramArray) || count($paramArray) < 2 || !ctype_digit($paramArray[count($paramArray) - 1])) {
                printLog('Tenpay bad out_trade_no', 'PAYMENT', Base::ERROR);
                goto out_fail;
            }

            // 最后一个应该是订单 ID 号
            $orderId = intval($paramArray[count($paramArray) - 1]);
            if (empty($orderId)) {
                printLog('Tenpay bad out_trade_no', 'PAYMENT', Base::ERROR);
                goto out_fail;
            }

            // 设置订单 ID
            $this->orderId = $orderId;

            $orderBasicService = new OrderBasicService();
            $orderInfo         = $orderBasicService->loadOrderInfoById($orderId);

            // 判断金额是否一致，使用 分 做单位来比较
            if ($orderInfo->isEmpty() || Money::storageToCent($orderInfo['order_amount']) != intval($total_fee)) {
                printLog(
                    'Tenpay total_fee error, order_amount :{' . Money::storageToCent($orderInfo['order_amount'])
                    . '} total_fee :{' . $total_fee . '}',
                    'PAYMENT',
                    Base::ERROR
                );
                goto out_fail;
            }

            //检查订单状态
            if (OrderBasicService::OS_UNCONFIRMED != $orderInfo['order_status']) {
                printLog(
                    'Tenpay order_status is not OS_UNCONFIRMED, order_status[' . $orderInfo['order_status']
                    . '] orderId[' . $orderId . ']',
                    'PAYMENT',
                    Base::WARN
                );
                goto out_succ;
            }

            // 把订单设置为已付款状态
            $orderPaymentService = new OrderPaymentService();
            $orderPaymentService->markOrderInfoPay($orderId, $this->payId, $this->getGatewayType(), $transaction_id);
            printLog('Tenpay orderId[' . $orderId . '] notify success', 'PAYMENT', Base::INFO);

            //------------------------------
            //处理业务完毕
            //------------------------------
        } else {
            printLog('Tenpay trade_mode is not 1', 'PAYMENT', Base::ERROR);
        }

        out_succ: // 成功从这里返回
        echo "success";
        return true;

        out_fail: // 错误从这里返回
        echo "fail";
        return false;
    }

    public function doReturnUrl($f3)
    {
        // 把 GET 都赋值给 POST
        $_POST = $_GET;
        $ret   = $this->doNotifyUrl($f3);

        if ($ret) {
            printLog('Tenpay returnUrl success', 'PAYMENT', Base::INFO);
        } else {
            printLog('Tenpay returnUrl fail', 'PAYMENT', Base::ERROR);
        }

        return $ret;
    }

}

