<?php
/*
 * QQ用户登陆
 */

namespace Controller\Thirdpart\QQAuth;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\ClientData;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Service\User\User as UserBasicService;
use Plugin\Thirdpart\QQAuth\QQAuthPlugin;

class Callback extends \Controller\BaseController
{

    private function get_url_contents($url)
    {
        if (ini_get("allow_url_fopen") == "1") {
            return file_get_contents($url);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * QQ 登陆
     */
    public function get($f3)
    {
        global $logger;
        global $smarty;

        // 验证 state 参数，防止 csrf 攻击
        if ($_REQUEST['state'] != $f3->get('SESSION[qq_login_state]')) {
            $errorMessage = 'qq login state doest not match, GET[' . $f3->get('GET[state]') . '] SESSION[' . $f3->get(
                    'SESSION[qq_login_state]'
                ) . ']';
            $logger->addLogInfo(\Core\Log\Base::NOTICE, 'QQLOGIN', $errorMessage);
            goto out;
        }

        // 获取 access_token
        $callback = RouteHelper::makeUrl('/Thirdpart/QQAuth/Callback', null, false, true);

        $tokenUrl = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
            . "client_id=" . QQAuthPlugin::getOptionValue('qqauth_appid') . "&redirect_uri=" . urlencode($callback)
            . "&client_secret=" . QQAuthPlugin::getOptionValue('qqauth_appkey') . "&code=" . $_REQUEST["code"];

        $response = $this->get_url_contents($tokenUrl);
        if (strpos($response, "callback") !== false) {
            $lpos     = strpos($response, "(");
            $rpos     = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
            $msg      = json_decode($response);
            if (isset($msg->error)) {
                $errorMessage = 'error [' . $msg->error . '] msg [' . $msg->error_description . ']';
                $logger->addLogInfo(\Core\Log\Base::NOTICE, 'QQLOGIN', $errorMessage);
                goto out;
            }
        }

        $params = array();
        parse_str($response, $params);
        $logger->addLogInfo(\Core\Log\Base::DEBUG, 'QQLOGIN', print_r($params, true));
        $accessToken = $params["access_token"];

        // 取得 OpenID
        $graphUrl = "https://graph.qq.com/oauth2.0/me?access_token="
            . $accessToken;

        $response = $this->get_url_contents($graphUrl);
        if (strpos($response, "callback") !== false) {
            $lpos     = strpos($response, "(");
            $rpos     = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
        }

        $user = json_decode($response);
        if (isset($user->error)) {
            $errorMessage = 'error [' . $msg->error . '] msg [' . $msg->error_description . ']';
            goto out;
        }

        $openId = $user->openid;

        // 取得 userInfo
        $get_user_info = "https://graph.qq.com/user/get_user_info?"
            . "access_token=" . $accessToken
            . "&oauth_consumer_key=" . QQAuthPlugin::getOptionValue('qqauth_appid')
            . "&openid=" . $openId
            . "&format=json";

        $response   = $this->get_url_contents($get_user_info);
        $qqUserInfo = json_decode($response, true);

        $sns_login = "qq:{$openId}";

        // 用户登陆操作
        $userBasicService = new UserBasicService();
        $authUser         = $userBasicService->doAuthSnsUser($sns_login, null, null, false);

        if ($authUser) {
            goto out_login_user;
        }

        // 之前没有登陆过，自动注册用户
        $authUser = $userBasicService->doAuthSnsUser($sns_login, $openId . '@qq.com', $openId . '@qq.com', true);
        $logger->addLogInfo(\Core\Log\Base::INFO, 'QQLOGIN', '注册QQ用户：' . print_r($qqUserInfo, true));

        out_login_user:

        AuthHelper::saveAuthUser($authUser->toArray(), 'qqlogin');

        // 设置用户名在网页显示
        ClientData::saveClientData(\Controller\User\Login::$clientDataIsUserLoginKey, true);
        ClientData::saveClientData(
            \Controller\User\Login::$clientDataUserNameDisplayKey,
            'QQ用户：' . $qqUserInfo['nickname']
        );

        out:
        // 跳转到用户之前看的页面，如果之前没有看过的页面那就回到首页
        RouteHelper::jumpBack($this, '/', true);
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


