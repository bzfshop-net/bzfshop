<?php

/**
 * @author QiangYu
 *
 * 我的个人资料
 *
 * */

namespace Controller\My;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Service\Meta\Role as MetaRoleService;
use Core\Service\User\Admin as AdminUserService;

class Profile extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;
        $smarty->assign(AuthHelper::getAuthUser());
        $smarty->display('my_profile.tpl');
    }

    public function post($f3)
    {
        global $smarty;

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('POST'));

        $input                   = array();
        $input['user_real_name'] = $validator->required('管理员名称不能为空')->validate('user_real_name');
        $input['oldpassword']    = $validator->validate('oldpassword');
        $input['password']       = $validator->validate('password');
        $input['user_desc']      = $validator->validate('user_desc');

        // 用户打算修改密码
        if (!Utils::isBlank($input['password'])) {
            $validator->required('必须提供旧密码才能修改密码')->validate('oldpassword');

            if ($f3->get('sysConfig[is_demo]')) {
                $this->addFlashMessage('演示系统不允许修改密码');
                goto out;
            }
        }

        // 提供的旧密码，但是新密码为空
        if (!Utils::isBlank($input['oldpassword'])) {
            $validator->required('新密码不能为空')->validate('password');
        }

        if (!$this->validate($validator)) {
            goto out;
        }

        $authAdminUser    = AuthHelper::getAuthUser();
        $adminUserService = new AdminUserService();
        // 验证用户登陆
        $adminUser = $adminUserService->loadAdminById($authAdminUser['user_id']);

        if ($adminUser->isEmpty()) {
            $this->addFlashMessage("非法登陆用户");
            RouteHelper::reRoute($this, '/User/Logout', false);
        }

        // 用户打算修改密码，但是旧密码不对
        if (!empty($input['password'])
            && !$adminUserService->verifyPassword(
                $authAdminUser['user_id'],
                $input['oldpassword']
            )
        ) {
            $this->addFlashMessage('旧密码不对');
            goto out;
        }

        // 更新数据
        unset($input['oldpassword']);
        $adminUserService->updateAdmin($adminUser, $input);

        // 记录用户的登陆信息
        $adminUserInfo = $adminUser->toArray();
        unset($adminUserInfo['password']); // 不要记录密码

        // 取得用户的角色权限
        $adminUserInfo['role_action_list'] = '';
        if ($adminUserInfo['role_id'] > 0) {
            $metaRoleService = new MetaRoleService();
            $role            = $metaRoleService->loadRoleById($adminUserInfo['role_id']);
            if (!$role->isEmpty()) {
                // 赋值角色权限
                $adminUserInfo['role_action_list'] = $role['meta_data'];
            }
        }

        AuthHelper::saveAuthUser($adminUserInfo);

        $this->addFlashMessage("修改资料成功");
        $smarty->assign($adminUserInfo);

        out: // 从这里出去
        $smarty->display('my_profile.tpl');
    }

}