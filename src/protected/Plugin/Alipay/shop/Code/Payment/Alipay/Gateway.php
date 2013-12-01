<?php

/**
 * 支付宝即时到帐交易支付网关的实现
 *
 * @author QiangYu
 *
 */

namespace Payment\Alipay;

use Core\Helper\Utility\Money;
use Core\Helper\Utility\Validator;
use Core\Log\Base;
use Core\Payment\AbstractBaseGateway;
use Core\Service\Order\Order as OrderBasicService;
use Core\Service\Order\Payment as OrderPaymentService;
use Plugin\Payment\Alipay\AlipayPlugin;

/**
 * 支付网关的通用接口定义
 *
 * @author QiangYu
 */
class Gateway extends AbstractBaseGateway
{

    private $orderId = null;
    private $partnerId = null; //商户号
    private $partnerKey = null; //密钥
    private $account = null; // 支付宝账号
    private $payId = null; //用于填充订单 order_info  中的 pay_id 字段
    private $alipayConfig = null; // 用于传递给 alipay 的类

    public function getGatewayType()
    {
        return 'alipay'; //注意大小写
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function init(array $paramArray = null)
    {
        // 参数验证
        $this->partnerId  = AlipayPlugin::getOptionValue('partner_id');
        $this->partnerKey = AlipayPlugin::getOptionValue('partner_key');
        $this->account    = AlipayPlugin::getOptionValue('account');
        $this->payId      = AlipayPlugin::getOptionValue('pay_id');

        $alipayConfig            = array();
        $alipayConfig['partner'] = $this->partnerId;
        $alipayConfig['key']     = $this->partnerKey;
        //签名方式 不需修改
        $alipayConfig['sign_type'] = 'MD5';
        //字符编码格式 目前支持 gbk 或 utf-8
        $alipayConfig['input_charset'] = 'utf-8';
        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        $alipayConfig['cacert'] = dirname(__FILE__) . '/cacert.pem';
        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $alipayConfig['transport'] = 'https';

        $this->alipayConfig = $alipayConfig;
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

        $desc = $orderInfo['order_id']
            . '|' . Money::toSmartyDisplay($orderInfo['order_amount'])
            . '|' . $orderInfo['system_id'];

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service"           => "create_direct_pay_by_user",
            "partner"           => $this->partnerId,
            "payment_type"      => 1, //支付类型
            "notify_url"        => $notifyUrl,
            "return_url"        => $returnUrl,
            "seller_email"      => $this->account,
            "out_trade_no"      => $orderInfo['order_sn'] . '_' . $orderInfo['order_id'],
            "subject"           => $desc,
            "total_fee"         => Money::toDisplay($orderInfo['order_amount'], 2),
            "body"              => $desc,
            "show_url"          => '',
            "anti_phishing_key" => '', //若要使用请调用类文件submit中的query_timestamp函数
            "exter_invoke_ip"   => '', //客户端的IP地址
            "_input_charset"    => 'utf-8',
        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($this->alipayConfig);
        return $alipaySubmit->buildRequestUrl($parameter);
    }

    public function doNotifyUrl($f3)
    {
        // 记录所有的参数，方便调试查找
        printLog(
            'Alipay Notify Paramters ： ' . print_r($_REQUEST, true),
            'PAYMENT',
            Base::INFO
        );

        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($this->alipayConfig);
        $verifyResult = $alipayNotify->verifyNotify();

        if (!$verifyResult) {
            printLog(
                'Alipay verifyNotify Error ',
                'PAYMENT',
                Base::ERROR
            );
            goto out_fail;
        }

        //验证成功
        //商户订单号
        $out_trade_no = $_POST['out_trade_no'];
        //支付宝交易号
        $trade_no = $_POST['trade_no'];
        //交易状态
        $trade_status = $_POST['trade_status'];
        //总金额
        $total_fee = $_POST['total_fee'];

        if ($_POST['trade_status'] != 'TRADE_FINISHED' && $_POST['trade_status'] != 'TRADE_SUCCESS') {
            printLog(
                'Alipay trade_status Error',
                'PAYMENT',
                Base::ERROR
            );
            goto out_fail;
        }
        //$_POST['trade_status'] == 'TRADE_FINISHED'
        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //如果有做过处理，不执行商户的业务程序
        //注意：
        //该种交易状态只在两种情况下出现
        //1、开通了普通即时到账，买家付款成功后。
        //2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
        //$_POST['trade_status'] == 'TRADE_SUCCESS'
        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //如果有做过处理，不执行商户的业务程序
        //注意：
        //该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
        //------------------------------
        //处理业务开始
        //------------------------------

        if (empty($out_trade_no)) {
            goto out_bad_trade_no;
        }

        $paramArray = explode('_', $out_trade_no);
        if (empty($paramArray) || count($paramArray) < 2 || !ctype_digit($paramArray[count($paramArray) - 1])) {
            goto out_bad_trade_no;
        }

        // 最后一个应该是订单 ID 号
        $orderId = intval($paramArray[count($paramArray) - 1]);

        if (empty($orderId)) {
            goto out_bad_trade_no;
        }

        // 设置订单 ID
        $this->orderId = $orderId;

        $orderBasicService = new OrderBasicService();
        $orderInfo         = $orderBasicService->loadOrderInfoById($orderId);

        //判断金额是否一致，我们用 分 做单位来比较
        if ($orderInfo->isEmpty()
            || Money::storageToCent($orderInfo['order_amount']) != Money::displayToCent($total_fee)
        ) {
            printLog(
                'Alipay total_fee error, order_amount :{' . Money::storageToCent($orderInfo['order_amount'])
                . '} total_fee : {' . Money::displayToCent($total_fee) . '}',
                'PAYMENT',
                Base::ERROR
            );
            goto out_fail;
        }

        //检查订单状态
        if (OrderBasicService::OS_UNCONFIRMED != $orderInfo['order_status']) {
            printLog(
                'Alipay order_status is not OS_UNCONFIRMED, order_status[' . $orderInfo['order_status'] . '] orderId['
                . $orderId . ']',
                'PAYMENT',
                Base::WARN
            );
            goto out_succ;
        }

        // 把订单设置为已付款状态
        $orderPaymentService = new OrderPaymentService();
        $orderPaymentService->markOrderInfoPay($orderId, $this->payId, $this->getGatewayType(), $trade_no);
        printLog(
            'Alipay orderId[' . $orderId . '] notifyUrl success',
            'PAYMENT',
            Base::INFO
        );

        //------------------------------
        //处理业务完毕
        //------------------------------        

        out_succ:
        echo "success"; //成功返回
        return true;

        out_bad_trade_no:
        printLog('Alipay bad out_trade_no', 'PAYMENT', Base::ERROR);

        out_fail: // 失败从这里返回
        echo "fail";
        return false;
    }

    public function doReturnUrl($f3)
    {
        // 把 GET 都赋值给 POST
        $_POST = $_GET;
        $ret   = $this->doNotifyUrl($f3);

        if ($ret) {
            printLog(
                'Alipay returnUrl success',
                'PAYMENT',
                Base::INFO
            );
        } else {
            printLog(
                'Alipay returnUrl fail',
                'PAYMENT',
                Base::INFO
            );
        }

        return $ret;
    }

}

