<?php
/**
 * @author QiangYu
 *
 * 所有需要用户登陆才可以访问的 Controller 的基类
 *
 * */

namespace Controller;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;

class AuthController extends BaseController
{

    public function beforeRoute($f3)
    {
        parent::beforeRoute($f3);

        // 由于我们使用 GET 来传递 session id，出于安全性考虑，我们需要检查来源 IP
        $userSessionIP = $f3->get('SESSION[user_session_ip]');
        if (empty($userSessionIP)) {
            $f3->set('SESSION[user_session_ip]', $f3->get('IP'));
        } else {
            if ($userSessionIP !== $f3->get('IP')) {
                // IP 非法，清空当前 session 数据
                $f3->clear('SESSION');
                session_destroy();
                session_write_close();
            }
        }

        // 用户没有登陆，让用户去登陆
        if (!AuthHelper::isAuthUser()) {
            // 如果已经记录了一个回跳 URL ，则不要再覆盖这个记录了
            RouteHelper::reRoute($this, '/User/Login', !RouteHelper::hasRememberUrl());
        }

    }

    public function afterRoute($f3)
    {
        parent::afterRoute($f3);
    }
}

