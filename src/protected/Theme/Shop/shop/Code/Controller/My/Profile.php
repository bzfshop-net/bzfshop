<?php

/**
 * @author QiangYu
 *
 * 我的订单控制器
 *
 * */

namespace Controller\My;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Service\User\User as UserService;

class Profile extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;

        $userInfo = AuthHelper::getAuthUser();

        // 出于安全的考虑，一些字段不应该输出
        unset($userInfo['id']);
        unset($userInfo['user_id']);
        unset($userInfo['password']);

        $smarty->assign($userInfo);

        out_display:
        $smarty->display('my_profile.tpl', 'get');
    }

    public function post($f3)
    {
        global $smarty;

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('POST'));

        $input = array();

        $input['oldpassword']  = $validator->validate('oldpassword');
        $input['password']     = $validator->validate('password');
        $input['email']        = $validator->validate('email');
        $input['mobile_phone'] = $validator->digits('手机号格式不对')->validate('mobile_phone');

        // 用户打算修改密码
        if (!Utils::isBlank($input['password'])) {
            $validator->required('必须提供旧密码才能修改密码')->validate('oldpassword');
        }

        // 提供的旧密码，但是新密码为空
        if (!Utils::isBlank($input['oldpassword'])) {
            $validator->required('新密码不能为空')->validate('password');
        }

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        $userInfo = AuthHelper::getAuthUser();

        $userService = new UserService();
        $user        = $userService->loadUserById($userInfo['user_id']);

        if (!$user) {
            // 非法用户，应该让它自动登陆出去
            $this->addFlashMessage('非法登陆用户');
            RouteHelper::reRoute($this, '/User/Logout', false);
        }

        // 用户打算修改密码，但是旧密码不对
        if (!empty($input['password']) && !$userService->verifyPassword($userInfo['user_id'], $input['oldpassword'])) {
            $this->addFlashMessage('旧密码不对');
            goto out_fail;
        }

        // 更新数据
        unset($input['oldpassword']);
        $userService->updateUser($user, $input);

        // 更新认证记录
        AuthHelper::removeAuthUser();
        AuthHelper::saveAuthUser($user->toArray());

        $this->addFlashMessage('资料更新成功');

        RouteHelper::reRoute($this, '/My/Profile');
        return; // 这里正常返回

        out_fail: // 失败返回
        $smarty->display('my_profile.tpl', 'post');
    }

}
