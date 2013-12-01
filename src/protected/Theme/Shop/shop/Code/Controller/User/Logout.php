<?php

/**
 * @author QiangYu
 *
 * 用户退出登陆
 *
 * */

namespace Controller\User;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\ClientData;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;

class Logout extends \Controller\BaseController
{

    public function get($f3)
    {

        // 清除客户端所有数据
        ClientData::clearClientData();

        // 清除服务器端数据
        AuthHelper::removeAuthUser();
        $f3->clear('SESSION');

        $this->addFlashMessage('成功退出登陆');

        $backUrl = RouteHelper::getRefer();
        if (Utils::isBlank($backUrl)) {
            // 没有来路域名则返回首页
            $backUrl = '/';
        }
        // 刷新当前页面
        RouteHelper::reRoute($this, $backUrl, false);
    }

    public function post($f3)
    {
        $this->get($f3);
    }
}
