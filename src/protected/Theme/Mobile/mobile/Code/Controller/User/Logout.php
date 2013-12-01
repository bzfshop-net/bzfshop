<?php

/**
 * @author QiangYu
 *
 * 用户退出登陆
 *
 * */

namespace Controller\User;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;

class Logout extends \Controller\BaseController
{

    public function get($f3)
    {
        AuthHelper::removeAuthUser();
        $f3->clear('SESSION');

        $this->addFlashMessage('成功退出登陆');

        // 返回首页
        RouteHelper::reRoute($this, '/', false);
    }

    public function post($f3)
    {
        $this->get($f3);
    }
}
