<?php

/**
 * @author QiangYu
 *
 * 操作管理员用户账号
 *
 * */

namespace Controller\Account;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Request;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Service\Meta\Privilege as MetaPrivilegeService;
use Core\Service\Meta\Role as MetaRoleService;
use Core\Service\User\Admin as AdminUserService;
use Core\Service\User\AdminLog as AdminLogService;

class Admin extends \Controller\AuthController
{

    /**
     * 管理员列表显示
     */
    public function ListUser($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_admin_listuser');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo = $validator->digits()->min(0)->validate('pageNo');
        $pageSize = $validator->digits()->min(0)->validate('pageSize');

        //查询条件
        $formQuery = array();
        $formQuery['user_name'] = $validator->validate('user_name');
        $formQuery['user_real_name'] = $validator->validate('user_real_name');
        $formQuery['user_desc'] = $validator->validate('user_desc');
        $formQuery['role_id'] = $validator->digits()->validate('role_id');

        // 设置缺省值
        $pageNo = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 建立查询条件
        $condArray = QueryBuilder::buildQueryCondArray($formQuery);

        // 查询管理员列表
        $adminUserService = new AdminUserService();
        $totalCount = $adminUserService->countAdminArray($condArray);
        if ($totalCount <= 0) { // 没用户，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Account/Admin/ListUser');
        }

        // 管理员列表
        $adminUserArray = $adminUserService->fetchAdminArray($condArray, $pageNo * $pageSize, $pageSize);

        // 取得角色列表
        $metaRoleService = new MetaRoleService();
        $roleArray = $metaRoleService->fetchRoleArray();

        // 建立  roleId --> role 的倒查表
        $roleIdToRoleArray = array();
        foreach ($roleArray as $roleItem) {
            $roleIdToRoleArray[$roleItem['meta_id']] = $roleItem;
        }

        // 给管理员添加角色信息
        foreach ($adminUserArray as &$adminUser) {
            if (array_key_exists($adminUser['role_id'], $roleIdToRoleArray)) {
                $adminUser['role_name'] = $roleIdToRoleArray[$adminUser['role_id']]['meta_name'];
            } else {
                $adminUser['role_name'] = '';
            }
        }
        unset($adminUser);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('adminUserArray', $adminUserArray);

        out_display:
        $smarty->display('account_admin_listuser.tpl');
    }

    /**
     * 管理员详情显示
     */
    public function Edit($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_admin_edit_get');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $user_id = $validator->digits()->min(1)->validate('user_id');
        $user_id = ($user_id > 0) ? $user_id : 0;
        if (!$this->validate($validator)) {
            goto out;
        }

        // 查询管理员信息
        $adminUserService = new AdminUserService();
        $adminUser = $adminUserService->loadAdminById($user_id);
        if (0 != $user_id && $adminUser->isEmpty()) { // 不存在的管理员
            $this->addFlashMessage('管理员不存在');
            goto out;
        }

        if ($adminUser->isEmpty()) {
            // 新建管理员
            $this->requirePrivilege('manage_account_admin_create');
        } else {
            if (AdminUserService::verifyPrivilege(AdminUserService::privilegeAll, $adminUser['action_list'])) {
                // 拥有最高权限的管理员只有他自己能编辑自己
                $authAdminUser = AuthHelper::getAuthUser();
                if ($authAdminUser['user_id'] != $adminUser['user_id']) {
                    $this->addFlashMessage('超级管理员只有他自己能操作自己的信息');
                    RouteHelper::reRoute($this, '/Account/Admin/ListUser');
                }
            }
        }

        if (!$f3->get('POST')) {
            // 没有 post ，只是普通的显示
            goto out_display;
        }

        // 权限检查
        $this->requirePrivilege('manage_account_admin_edit_post');

        // 用户提交了更新请求，这里做管理员信息更新
        // 参数验证
        $inputArray = array();
        $validator = new Validator($f3->get('POST'));
        $inputArray['user_name'] = $validator->required()->minlength(3)->validate('user_name');
        $inputArray['disable'] = $validator->filter('ValidatorIntValue')->validate('disable');
        $inputArray['user_real_name'] = $validator->required()->minlength(2)->validate('user_real_name');
        $inputArray['is_kefu'] = $validator->filter('ValidatorIntValue')->validate('is_kefu');
        $inputArray['user_desc'] = $validator->validate('user_desc');
        $password = $validator->validate('password');
        if (!Utils::isBlank($password)) {
            // 权限检查
            $this->requirePrivilege('manage_account_admin_edit_change_account_password');
            $inputArray['password'] = $password;

            if ($f3->get('sysConfig[is_demo]')) {
                $this->addFlashMessage('演示系统不允许修改密码');
                goto out;
            }
        }

        if (!$this->validate($validator)) {
            goto out;
        }

        // 确认管理员账号没有重复
        if (!empty($inputArray['user_name'])) {
            $tmpAdminUser = $adminUserService->loadAdminByUserName($inputArray['user_name']);
            if ((0 == $user_id && !$tmpAdminUser->isEmpty()) // 新建用户，已经有一个同名用户存在了
                || (!$tmpAdminUser->isEmpty() && $tmpAdminUser['user_id'] != $user_id) // 重复的用户
            ) {
                $this->addFlashMessage('管理员账号 ' . $inputArray['user_name'] . ' 已经存在');
                goto out;
            }
        }

        if ($adminUser['user_name'] != $inputArray['user_name']) {
            // 管理员账号发生修改，检查权限
            $this->requirePrivilege('manage_account_admin_edit_change_account_password');
        }

        // 更新管理员信息
        $adminUserService->updateAdmin($adminUser, $inputArray);
        $this->addFlashMessage('管理员信息更新成功');

        out_display:
        //给 smarty 模板赋值
        $smarty->assign($adminUser->toArray());

        out:
        $smarty->display('account_admin_edit.tpl');
    }

    /**
     * 添加管理员
     *
     * @param $f3
     */
    public function Create($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_admin_create');

        global $smarty;
        $smarty->display('account_admin_edit.tpl');
    }

    /**
     * 管理员权限管理
     *
     * @param $f3
     */
    public function Privilege($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_admin_privilege_get');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $user_id = $validator->required()->digits()->min(1)->validate('user_id');
        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 查询管理员信息
        $adminUserService = new AdminUserService();
        $adminUser = $adminUserService->loadAdminById($user_id);
        if ($adminUser->isEmpty()) { // 不存在的管理员
            $this->addFlashMessage('管理员不存在');
            goto out_fail;
        } else {
            if (AdminUserService::verifyPrivilege(AdminUserService::privilegeAll, $adminUser['action_list'])) {
                // 拥有最高权限的管理员只有他自己能编辑自己
                $authAdminUser = AuthHelper::getAuthUser();
                if ($authAdminUser['user_id'] != $adminUser['user_id']) {
                    $this->addFlashMessage('超级管理员只有他自己能操作自己的信息');
                    RouteHelper::reRoute($this, '/Account/Admin/ListUser');
                }
            }
        }

        if (!Request::isRequestPost()) {
            // 没有 post ，只是普通的显示
            goto out_display;
        }

        // 权限检查
        $this->requirePrivilege('manage_account_admin_privilege_post');

        $action_list_str = '';
        $actionCodeArray = $f3->get('POST[action_code]');

        if (empty($actionCodeArray)) {
            // 清空了所有权限
            $action_list_str = '';
            goto update_privilege;
        }

        if (in_array(AdminUserService::privilegeAll, $actionCodeArray)) {

            // 权限检查，只有自身拥有 privilegeAll 权限的人才能给别人授权 privilegeAll
            $this->requirePrivilege(AdminUserService::privilegeAll);

            // 用户有所有的权限
            $action_list_str = AdminUserService::privilegeAll;
            goto update_privilege;
        }

        // 生成权限字符串
        $action_list_str = implode(',', $actionCodeArray);

        update_privilege:
        $adminUser->role_id = $f3->get('POST[role_id]');
        $adminUser->action_list = $action_list_str;
        $adminUser->save();
        $this->addFlashMessage('管理员权限保存成功');

        out_display:
        $smarty->assign($adminUser->toArray());

        // 取得权限显示列表
        $metaPrivilegeService = new MetaPrivilegeService();
        $smarty->assign('privilegeArray', $metaPrivilegeService->fetchPrivilegeArray());

        $smarty->display('account_admin_privilege.tpl');
        return; // 正常从这里返回

        out_fail: // 失败，返回管理员列表
        RouteHelper::reRoute(
            $this,
            RouteHelper::makeUrl('/Account/Admin/ListUser', array('user_id' => $user_id), true)
        );
    }

    /**
     * 管理员操作日志
     *
     * @param $f3
     */
    public function ListLog($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_admin_listlog');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo = $validator->digits()->min(0)->validate('pageNo');
        $pageSize = $validator->digits()->min(0)->validate('pageSize');

        //查询条件
        $formQuery = array();
        $formQuery['user_id'] = $validator->filter('ValidatorIntValue')->validate('user_id');
        $formQuery['operate'] = $validator->validate('operate');
        $formQuery['operate_desc'] = $validator->validate('operate_desc');

        //操作时间
        $operateTimeStartStr = $validator->validate('operate_time_start');
        $operateTimeStart = Time::gmStrToTime($operateTimeStartStr) ? : null;
        $operateTimeEndStr = $validator->validate('operate_time_end');
        $operateTimeEnd = Time::gmStrToTime($operateTimeEndStr) ? : null;
        $formQuery['operate_time'] = array($operateTimeStart, $operateTimeEnd);

        // 设置缺省值
        $pageNo = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 20;

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 建立查询条件
        $condArray = QueryBuilder::buildQueryCondArray($formQuery);

        // 查询管理员列表
        $adminLogService = new AdminLogService();
        $totalCount = $adminLogService->countAdminLogArray($condArray);
        if ($totalCount <= 0) { // 没数据，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Account/Admin/ListLog');
        }

        // 管理员列表
        $adminLogArray = $adminLogService->fetchAdminLogArray($condArray, $pageNo * $pageSize, $pageSize);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('adminLogArray', $adminLogArray);

        out_display:
        $smarty->display('account_admin_listlog.tpl');
    }
}