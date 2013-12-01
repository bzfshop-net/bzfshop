<?php
/*
 * 跳转到用户认证页面
 */

namespace Controller\Thirdpart\Dev360Auth;

use Core\Helper\Utility\Route as RouteHelper;
use Plugin\Thirdpart\Dev360Auth\Dev360AuthPlugin;

class Login extends \Controller\BaseController
{

    public static $optionKeyPrefix = 'shop_';

    /**
     * 360 一站通登陆
     */
    public function get($f3)
    {

        // 标准动态链接，不能伪静态地址
        $callback = RouteHelper::makeUrl('/Thirdpart/Dev360Auth/Callback', null, false, true, false);

        $params = array(
            'client_id'     => Dev360AuthPlugin::getOptionValue(self::$optionKeyPrefix . 'dev360auth_app_key'),
            'redirect_uri'  => $callback,
            'response_type' => 'code',
        );

        $url = 'https://openapi.360.cn/oauth2/authorize?' . http_build_query($params);

        header("Location: {$url}");
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}


