<?php

/**
 * @author QiangYu
 *
 * 所有需要用户登陆才可以访问的 Controller 的基类
 *
 * */

namespace Controller;

use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Service\Meta\Privilege as MetaPrivilegeService;
use Core\Service\User\Admin as AdminUserService;

class AuthController extends BaseController
{

    /**
     * 由于 KindEditor, UEditor 使用了 swfupload 用于文件上传，而 swfupload 无法使用 Session 认证
     * 我们这里只能使用 Post 来自己做认证了
     */
    public function beforeRoute($f3)
    {
        global $smarty;
        parent::beforeRoute($f3);

        // 用户没有登陆，让用户去登陆
        if (!AuthHelper::isAuthUser() && !AuthHelper::isPostCookieAuth()) {
            // 如果已经记录了一个回跳 URL ，则不要再覆盖这个记录了
            RouteHelper::reRoute($this, '/User/Login', !RouteHelper::hasRememberUrl());
            return;
        }

        //把认证用户放入到 smarty 中
        $smarty->assign('authAdminUser', AuthHelper::getAuthUser());
        $smarty->assign('WEB_COOKIE_AUTH_KEY', AuthHelper::$cookieAuthKey);
    }

    public function afterRoute($f3)
    {
        parent::afterRoute($f3);
    }

    /**
     * 判断当前用户是否有某个权限
     *
     * @param string $needPrivilege
     *
     * @return bool
     */
    protected function hasPrivilege($needPrivilege)
    {
        $authAdminUser = AuthHelper::getAuthUser();
        if (empty($authAdminUser)) {
            goto out_fail;
        }

        // 检查权限
        if (!AdminUserService::verifyPrivilege(
            $needPrivilege,
            $authAdminUser['action_list'] . ',' . $authAdminUser['role_action_list']
        )
        ) {
            goto out_fail;
        }

        return true;

        out_fail:
        return false;
    }

    /**
     * 要求某个权限才能执行
     *
     * @param string $needPrivilege
     * @param bool   $isAjax 是否是 ajax 请求
     *
     */
    protected function requirePrivilege($needPrivilege, $isAjax = false)
    {
        if (!$this->hasPrivilege($needPrivilege)) {
            goto out_fail;
        }

        return; // 成功从这里返回

        out_fail: // 失败，reroute 到错误页面

        if ($isAjax) {

            $needPrivilegeName = $needPrivilege;

            $metaPrivilegeService = new MetaPrivilegeService();
            $privilege            = $metaPrivilegeService->loadPrivilegeItem($needPrivilege, 600); //缓存 10 分钟
            if (!$privilege->isEmpty()) {
                $needPrivilegeName = $privilege['meta_name'] ? : $needPrivilegeName;
            }

            Ajax::header();
            echo Ajax::buildResult(-1, '没有权限：' . $needPrivilegeName, null);
            die();
        }

        RouteHelper::reRoute(
            $this,
            RouteHelper::makeUrl('/Error/Privilege', array('privilege' => $needPrivilege), true)
        );
    }

}

