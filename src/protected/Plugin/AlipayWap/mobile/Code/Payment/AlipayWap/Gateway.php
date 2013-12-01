<?php

/**
 * 支付宝 Wap 支付网关的实现
 *
 * @author QiangYu
 *
 */

namespace Payment\AlipayWap;

use Core\Helper\Utility\Money;
use Core\Helper\Utility\Validator;
use Core\Log\Base;
use Core\Payment\AbstractBaseGateway;
use Core\Service\Order\Order as OrderBasicService;
use Core\Service\Order\Payment as OrderPaymentService;
use Plugin\Payment\AlipayWap\AlipayWapPlugin;

require_once(dirname(__FILE__) . "/function.php");

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


    /******* 这里是支付宝的配置信息，写死，不要随便动 *********/
    private $configGateway = "http://wappaygw.alipay.com/service/rest.htm?";
    private $configServiceCreate = "alipay.wap.trade.create.direct";
    private $configServiceExecute = "alipay.wap.auth.authAndExecute";
    private $configFormat = "xml";
    private $configSecId = "MD5";
    private $configVersion = "2.0";

    public function getGatewayType()
    {
        return 'alipaywap'; //注意大小写
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function init(array $paramArray = null)
    {
        $this->partnerId  = AlipayWapPlugin::getOptionValue('partner_id');
        $this->partnerKey = AlipayWapPlugin::getOptionValue('partner_key');
        $this->account    = AlipayWapPlugin::getOptionValue('account');
        $this->payId      = AlipayWapPlugin::getOptionValue('pay_id');

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
            . $orderInfo['system_id'] . '|WAP';

        // 构造要请求的参数数组，无需改动
        $parameterCreate = array(
            "req_data" =>
            '<direct_trade_create_req><subject>' . $desc . '</subject><out_trade_no>' . $orderInfo['order_sn'] . '_'
            . $orderInfo['order_id']
            . '</out_trade_no><total_fee>'
            . Money::toDisplay($orderInfo['order_amount'], 2)
            . "</total_fee><seller_account_name>"
            . $this->account
            . "</seller_account_name><notify_url>" . $notifyUrl . "</notify_url><out_user>" . $orderInfo['user_id']
            . "</out_user><merchant_url></merchant_url>" . "<call_back_url>" . $returnUrl
            . "</call_back_url></direct_trade_create_req>",
            "service"  => $this->configServiceCreate,
            "sec_id"   => $this->configSecId,
            "partner"  => $this->partnerId,
            "req_id"   => date("Ymdhms"),
            "format"   => $this->configFormat,
            "v"        => $this->configVersion
        );

        // 首先申请 Token
        $result = $this->callAlipayWapGateway($this->buildRequestLinkData($parameterCreate));

        // 调用GetToken方法，并返回token
        $token = $this->getToken($result);

        if (!$token) {
            printLog($this->getGatewayType() . ' 获取 token 失败');
            return null;
        }

        // 构造要请求的参数数组，无需改动
        $parameterExecute = array(
            "req_data"      =>
            "<auth_and_execute_req><request_token>" . $token . "</request_token></auth_and_execute_req>",
            "service"       => $this->configServiceExecute,
            "sec_id"        => $this->configSecId,
            "partner"       => $this->partnerId,
            "call_back_url" => $returnUrl,
            "format"        => $this->configFormat,
            "v"             => $this->configVersion
        );

        return $this->configGateway . $this->buildRequestLinkData($parameterExecute);
    }

    public function doNotifyUrl($f3)
    {
        // 记录所有的参数，方便调试查找
        printLog(
            $this->getGatewayType() . ' Notify Paramters ： ' . print_r($_REQUEST, true),
            'PAYMENT',
            Base::INFO
        );

        //计算得出通知验证结果
        $notifyArray = array(
            "service"     => $_POST['service'],
            "v"           => $_POST['v'],
            "sec_id"      => $_POST['sec_id'],
            "notify_data" => $_POST['notify_data']
        );

        $calcSign = build_mysign($notifyArray, $this->partnerKey, $this->configSecId);

        // 如果签名验证失败则直接退出
        if ($calcSign != $_POST["sign"]) {
            printLog(
                $this->getGatewayType() . ' sign error calcSign[' . $calcSign . '] sign[' . $_POST["sign"] . ']',
                'PAYMENT',
                Base::ERROR
            );
            goto out_fail;
        }

        //交易状态
        $trade_status = getDataForXML($_POST ['notify_data'], '/notify/trade_status');

        if ($trade_status != 'TRADE_FINISHED' && $trade_status != 'TRADE_SUCCESS') {
            printLog(
                $this->getGatewayType() . ' trade_status Error [' . $trade_status . ']',
                'PAYMENT',
                Base::ERROR
            );
            goto out_fail;
        }

        //商户订单号
        $out_trade_no = getDataForXML($_POST ['notify_data'], '/notify/out_trade_no');
        //支付宝交易号
        $trade_no = getDataForXML($_POST ['notify_data'], '/notify/trade_no');
        //总金额
        $total_fee = getDataForXML($_POST ['notify_data'], '/notify/total_fee');

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

        //判断金额是否一致，我们用 分 做单位比较
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
                $this->getGatewayType() . ' order_status is not OS_UNCONFIRMED, order_status['
                . $orderInfo['order_status'] . '] orderId['
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
            $this->getGatewayType() . ' orderId[' . $orderId . '] notifyUrl success',
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
        printLog(
            $this->getGatewayType() . ' bad out_trade_no',
            'PAYMENT',
            Base::ERROR
        );

        out_fail: // 失败从这里返回
        echo "fail";
        return false;
    }

    public function doReturnUrl($f3)
    {
        printLog(
            $this->getGatewayType() . ' Return Paramters ： ' . print_r($_REQUEST, true),
            'PAYMENT',
            Base::INFO
        );

        // GET 特殊处理
        $out_trade_no = $_GET['out_trade_no'];

        if (empty($out_trade_no)) {
            goto out_fail;
        }

        $paramArray = explode('_', $out_trade_no);
        if (empty($paramArray) || count($paramArray) < 2 || !ctype_digit($paramArray[count($paramArray) - 1])) {
            goto out_fail;
        }

        // 最后一个应该是订单 ID 号
        $orderId = intval($paramArray[count($paramArray) - 1]);

        if (empty($orderId)) {
            goto out_fail;
        }

        // 设置订单 ID , WAP 页面目前没有 /My/Order/Detail 页面，所以不能跳转到 detail 页面
        // $this->orderId = $orderId;

        $orderBasicService = new OrderBasicService();
        $orderInfo         = $orderBasicService->loadOrderInfoById($orderId);

        if (OrderBasicService::PS_PAYED == $orderInfo['pay_status']) {
            printLog(
                $this->getGatewayType() . 'return order [' . $orderInfo['order_id'] . '] already pay',
                'PAYMENT',
                Base::INFO
            );
        } else {
            printLog(
                $this->getGatewayType() . 'return order [' . $orderInfo['order_id'] . '] does not pay',
                'PAYMENT',
                Base::INFO
            );
        }

        return (OrderBasicService::PS_PAYED == $orderInfo['pay_status']);

        out_fail:
        return false;
    }


    /*************************************** 这里是支付宝的专有实现 ********************************************/

    private function buildRequestLinkData($parameter)
    {
        $paramArray = para_filter($parameter); // 除去数组中的空值和签名参数
        $sort_array = arg_sort($paramArray); // 得到从字母a到z排序后的签名参数数组

        // 生成签名
        $calcSign = build_mysign($sort_array, $this->partnerKey, $this->configSecId);
        return create_linkstring($paramArray) . '&sign=' . urlencode($calcSign);
    }

    /**
     * 返回token参数
     * 参数 result 需要先urldecode
     */
    private function getToken($result)
    {
        $result = urldecode($result); // URL转码
        $Arr    = explode('&', $result); // 根据 & 符号拆分

        $temp    = array(); // 临时存放拆分的数组
        $myArray = array(); // 待签名的数组
        // 循环构造key、value数组
        for ($i = 0; $i < count($Arr); $i++) {
            $temp                = explode('=', $Arr [$i], 2);
            $myArray [$temp [0]] = $temp [1];
        }

        $sign    = $myArray ['sign']; // 支付宝返回签名
        $myArray = para_filter($myArray); // 拆分完毕后的数组

        $sort_array = arg_sort($myArray); // 排序数组
        $calcSign   = build_mysign($sort_array, $this->partnerKey, $this->configSecId); // 构造本地参数签名，用于对比支付宝请求的签名

        if ($calcSign != $sign) // 判断签名是否正确
        {
            // 当判断出签名不正确，请不要验签通过
            printLog(
                'alipayWap Token 签名不正确 sign[' . $sign . '] calcSign[' . $calcSign . ']',
                'PAYMENT',
                \Core\Log\Base::ERROR
            );
            return null;
        }

        return getDataForXML($myArray ['res_data'], '/direct_trade_create_res/request_token'); // 返回token
    }

    /**
     * PHP Crul库 模拟Post提交至支付宝网关
     * 如果使用Crul 你需要改一改你的php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 返回 $data
     */
    private function callAlipayWapGateway($requestData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->configGateway); // 配置网关地址
        curl_setopt($ch, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1); // 设置post提交
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData); // post传输数据
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

}

