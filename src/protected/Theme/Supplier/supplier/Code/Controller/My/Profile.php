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
use Core\Service\User\Supplier as SupplierUserService;

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

        $input                = array();
        $input['phone']       = $validator->validate('phone');
        $input['address']     = $validator->validate('address');
        $input['oldpassword'] = $validator->validate('oldpassword');
        $input['password']    = $validator->validate('password');

        // 用户打算修改密码
        if (!Utils::isBlank($input['password'])) {
            $validator->required('必须提供旧密码才能修改密码')->validate('oldpassword');
        }

        // 提供的旧密码，但是新密码为空
        if (!Utils::isBlank($input['oldpassword'])) {
            $validator->required('新密码不能为空')->validate('password');
        }

        if (!$this->validate($validator)) {
            goto out;
        }

        $authSupplierUser    = AuthHelper::getAuthUser();
        $supplierUserService = new SupplierUserService();
        // 验证用户登陆
        $supplierUser = $supplierUserService->loadSupplierById($authSupplierUser['suppliers_id']);

        if ($supplierUser->isEmpty()) {
            $this->addFlashMessage("非法登陆用户");
            RouteHelper::reRoute($this, '/User/Logout', false);
        }

        // 用户打算修改密码，但是旧密码不对
        if (!empty($input['password'])
            && !$supplierUserService->verifyPassword(
                $authSupplierUser['suppliers_id'],
                $input['oldpassword']
            )
        ) {
            $this->addFlashMessage('旧密码不对');
            goto out;
        }

        // 更新数据
        unset($input['oldpassword']);
        $supplierUserService->updateSupplier($supplierUser, $input);

        // 记录用户的登陆信息
        $supplierUserInfo = $supplierUser->toArray();
        unset($supplierUserInfo['password']); // 不要记录密码

        AuthHelper::saveAuthUser($supplierUserInfo);

        $this->addFlashMessage("修改资料成功");
        $smarty->assign($supplierUserInfo);

        out: // 从这里出去
        $smarty->display('my_profile.tpl');
    }

}