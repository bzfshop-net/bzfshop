<?php

/**
 * @author QiangYu
 *
 * 操作用户角色，用于更好的做权限控制
 *
 * */

namespace Controller\Account;

use Core\Helper\Utility\Request;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Meta\Privilege as MetaPrivilegeService;
use Core\Service\Meta\Role as MetaRoleService;
use Core\Service\User\Admin as AdminUserService;

class Role extends \Controller\AuthController
{
    /**
     * 角色列表显示
     */
    public function ListRole($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_role_listrole');

        global $smarty;

        $metaRoleService = new MetaRoleService();
        $smarty->assign('roleArray', $metaRoleService->fetchRoleArray());

        out_display:
        $smarty->display('account_role_listrole.tpl');
    }

    /**
     * 角色详情显示
     */
    public function Edit($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_role_edit_get');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id   = $validator->digits()->min(1)->validate('meta_id');
        $meta_id   = ($meta_id > 0) ? $meta_id : 0;
        if (!$this->validate($validator)) {
            goto out;
        }

        // 查询角色信息
        $metaRoleService = new MetaRoleService();
        $role            = $metaRoleService->loadRoleById($meta_id);
        if (0 != $meta_id && $role->isEmpty()) { // 不存在的角色
            $this->addFlashMessage('角色不存在');
            goto out;
        }

        if ($role->isEmpty()) {
            // 新建角色
            $this->requirePrivilege('manage_account_role_create');
        }

        if (!$f3->get('POST')) {
            // 没有 post ，只是普通的显示
            goto out_display;
        }

        // 权限检查
        $this->requirePrivilege('manage_account_role_edit_post');

        // 用户提交了更新请求，这里做角色信息更新
        $validator = new Validator($f3->get('POST'));
        $metaRoleService->saveRole(
            $meta_id,
            $validator->validate('meta_name'),
            $validator->validate('meta_desc'),
            $role['meta_data']
        );

        if (0 == $meta_id) {
            $this->addFlashMessage('成功新建角色');
        } else {
            $this->addFlashMessage('角色信息更新成功');
        }

        out_display:
        //给 smarty 模板赋值
        $smarty->assign($role->toArray());

        out:
        $smarty->display('account_role_edit.tpl');
    }

    /**
     * 添加角色
     *
     * @param $f3
     */
    public function Create($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_role_create');

        global $smarty;
        $smarty->display('account_role_edit.tpl');
    }


    /**
     * 角色权限管理
     *
     * @param $f3
     */
    public function Privilege($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_role_privilege_get');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id   = $validator->required()->digits()->min(1)->validate('meta_id');
        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 查询角色信息
        $metaRoleService = new MetaRoleService();
        $role            = $metaRoleService->loadRoleById($meta_id);
        if ($role->isEmpty()) { // 不存在的角色
            $this->addFlashMessage('角色不存在');
            goto out_fail;
        }

        if (!Request::isRequestPost()) {
            // 没有 post ，只是普通的显示
            goto out_display;
        }

        // 权限检查
        $this->requirePrivilege('manage_account_role_privilege_post');

        $action_list_str = '';
        $actionCodeArray = $f3->get('POST[action_code]');

        if (empty($actionCodeArray)) {
            // 清空了所有权限
            $action_list_str = '';
            goto update_privilege;
        }

        // 清除掉 privilegeAll，角色不能设置最高权限
        while ($actionCodeArrayIndex = array_search(AdminUserService::privilegeAll, $actionCodeArray)) {
            unset($actionCodeArray[$actionCodeArrayIndex]);
        }

        // 生成权限字符串
        $action_list_str = implode(',', $actionCodeArray);

        update_privilege:
        $role->meta_data = $action_list_str;
        $role->save();
        $this->addFlashMessage('角色权限保存成功');

        out_display:
        $smarty->assign($role->toArray());

        // 取得权限显示列表
        $metaPrivilegeService = new MetaPrivilegeService();
        $smarty->assign('privilegeArray', $metaPrivilegeService->fetchPrivilegeArray());

        $smarty->display('account_role_privilege.tpl');
        return; // 正常从这里返回

        out_fail: // 失败，返回角色列表
        RouteHelper::reRoute($this, '/Account/Role/ListRole');
    }

}