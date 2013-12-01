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

