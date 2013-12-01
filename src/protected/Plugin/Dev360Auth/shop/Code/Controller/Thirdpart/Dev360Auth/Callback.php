<?php
/*
 * 360 用户认证后，获得的access_token以及用户信息
 */

namespace Controller\Thirdpart\Dev360Auth;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\ClientData;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Service\User\User as UserBasicService;
use Plugin\Thirdpart\Dev360Auth\Dev360AuthPlugin;

class Callback extends \Controller\BaseController
{
    public static $optionKeyPrefix = 'shop_';

    /**
     * 360 登陆
     */
    public function get($f3)
    {
        global $logger;

        if (empty($_GET['code'])) {
            $this->addFlashMessage('360联合登陆失败，Code 不存在');
            goto out;
        }

        require_once('sdk/QClient.php');

        // 获取access token
        $callback = RouteHelper::makeUrl('/Thirdpart/Dev360Auth/Callback', null, false, true);
        $oauth    =
            new \QOAuth2(Dev360AuthPlugin::getOptionValue(self::$optionKeyPrefix . 'dev360auth_app_key'),
                Dev360AuthPlugin::getOptionValue(self::$optionKeyPrefix . 'dev360auth_app_secrect'), '');
        $token    = $oauth->getAccessTokenByCode($_GET['code'], $callback);

        if (empty($token['access_token'])) {
            $this->addFlashMessage('360联合登陆失败，获取 access_token 失败');
            goto out;
        }

        // 调用API，获取用户信息
        $client = new \QClient(Dev360AuthPlugin::getOptionValue(self::$optionKeyPrefix . 'dev360auth_app_key'),
            Dev360AuthPlugin::getOptionValue(
                self::$optionKeyPrefix . 'dev360auth_app_secrect'
            ), $token['access_token']);
        $user   = $client->userMe();

        if (empty($user)) {
            $this->addFlashMessage('360联合登陆失败，用户信息为空');
            goto out;
        }

        $param = array(
            'user_id'  => $user['id'],
            'username' => !empty($user['name']) ? (string)$user['name'] : '网友',
            'token'    => $token['access_token'],
        );


        // put all values into $_POST[]
        $qid   = $param['user_id'];
        $qname = urldecode($param['username']);
        $qmail = '';

        if (empty($qid)) {
            // 没有 qid 没法登陆
            $this->addFlashMessage('360联合登陆失败，没有 qid');
            goto out;
        }

        $sns_login = "hao360:{$qid}";

        // 用户登陆操作
        $userBasicService = new UserBasicService();
        $authUser         = $userBasicService->doAuthSnsUser($sns_login, null, null, false);

        if ($authUser) {
            goto out_login_user;
        }

        // 用户不存在，自动注册一个用户
        if (empty($qmail)) {
            $qmail = '' . $qid . '@360.cn';
        }

        if (empty($qname)) {
            $qname = $qmail;
        }

        $retry       = 10; // 重试 10 次
        $regUserName = $qname;
        while ($userBasicService->isUserExist($regUserName, null) && $retry-- > 0) {
            $regUserName = $qname . '_' . rand(10000, 99999);
        }

        if ($retry <= 0) {
            $this->addFlashMessage('360联合登陆失败，用户名已经存在，无法自动注册');
            goto out;
        }

        $authUser = $userBasicService->doAuthSnsUser($sns_login, $qname, $qmail, true);
        $logger->addLogInfo(
            \Core\Log\Base::INFO,
            'DEV360AUTH',
            '注册360用户' . print_r(array('sns_login' => $sns_login, 'qname' => $qname, 'qmail' => $qmail), true)
        );

        out_login_user:

        AuthHelper::saveAuthUser($authUser->toArray(), 'dev360auth');

        // 设置用户名在网页显示
        ClientData::saveClientData(\Controller\User\Login::$clientDataIsUserLoginKey, true);
        ClientData::saveClientData(
            \Controller\User\Login::$clientDataUserNameDisplayKey,
            '360用户：' . $authUser['user_name']
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

