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
use Core\Service\User\AdminLog;

class Logout extends \Controller\BaseController
{

    public function get($f3)
    {
        AdminLog::logAdminOperate('user.logout', '用户退出', 'IP:' . $f3->get('IP'));

        AuthHelper::removeAuthUser();
        $f3->clear('SESSION');

        $this->addFlashMessage('成功退出登陆');

        // 刷新当前页面
        RouteHelper::reRoute($this, '/', false);
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
