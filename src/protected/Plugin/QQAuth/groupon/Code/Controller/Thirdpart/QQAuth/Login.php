<?php
/*
 * QQ用户登陆
 */

namespace Controller\Thirdpart\QQAuth;

use Core\Helper\Utility\Route as RouteHelper;
use Plugin\Thirdpart\QQAuth\QQAuthPlugin;

class Login extends \Controller\BaseController
{

    /**
     * QQ 登陆
     */
    public function get($f3)
    {
        $callback = RouteHelper::makeUrl('/Thirdpart/QQAuth/Callback', null, false, true);

        $qqLoginState = md5(uniqid(rand(), true)); // 防止 csrf 攻击
        $f3->set('SESSION[qq_login_state]', $qqLoginState);

        $loginUrl = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id="
            . QQAuthPlugin::getOptionValue('qqauth_appid') . "&redirect_uri=" . urlencode($callback)
            . "&state=" . $qqLoginState
            . "&scope=get_user_info";

        header("Location:$loginUrl");
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}


