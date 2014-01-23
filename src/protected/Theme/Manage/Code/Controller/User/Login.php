<?php

/**
 * @author QiangYu
 *
 * 用户登陆
 *
 * */

namespace Controller\User;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Meta\Role as MetaRoleService;
use Core\Service\User\Admin as AdminUserService;
use Core\Service\User\AdminLog;

class Login extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        // 生成 smarty 的缓存 id
        $smartyCacheId = 'User|Login|get';

        enableSmartyCache(true, 3600); // 缓存 1 小时

        if ($smarty->isCached('user_login.tpl', $smartyCacheId)) {
            goto out_display;
        }

        out_display:
        $smarty->display('user_login.tpl', $smartyCacheId);
    }

    public function post($f3)
    {
        global $smarty;

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('POST'));

        $input = array();
        $input['user_name'] = $validator->required('用户名不能为空')->validate('user_name');
        $input['password'] = $validator->required('密码不能为空')->validate('password');
        $p_captcha = $validator->required('验证码不能为空')->validate('captcha');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 检查验证码是否有效
        $captchaController = new \Controller\Image\Captcha();
        if (!$captchaController->validateCaptcha($p_captcha)) {
            $this->addFlashMessage("验证码错误");
            goto out_fail;
        }

        $adminService = new AdminUserService();
        // 验证用户登陆
        $admin = $adminService->doAuthAdmin($input['user_name'], $input['user_name'], $input['password']);

        if (!$admin) {
            $this->addFlashMessage("登陆失败，用户名、密码错误");
            goto out_fail;
        }

        // 记录用户的登陆信息
        $adminUserInfo = $admin->toArray();
        unset($adminUserInfo['password']); // 不要记录密码

        // 取得用户的角色权限
        $adminUserInfo['role_action_list'] = '';
        if ($adminUserInfo['role_id'] > 0) {
            $metaRoleService = new MetaRoleService();
            $role = $metaRoleService->loadRoleById($adminUserInfo['role_id']);
            if (!$role->isEmpty()) {
                // 赋值角色权限
                $adminUserInfo['role_action_list'] = $role['meta_data'];
            }
        }

        AuthHelper::saveAuthUser($adminUserInfo);

        try {
            // 记录用户登录日志
            AdminLog::logAdminOperate('user.login', '用户登录', 'IP:' . $f3->get('IP'));
        } catch (\Exception $e) {
            // do nothing
        }

        $this->addFlashMessage("登陆成功");

        // 跳转到用户之前看的页面，如果之前没有看过的页面那就回到首页
        RouteHelper::jumpBack($this, '/', true);

        return; // 这里正常返回

        out_fail: // 失败从这里入口
        $smarty->display('user_login.tpl', 'User|Login|post');
    }

}