<?php

/**
 * @author QiangYu
 *
 * 操作供货商用户账号
 *
 * */

namespace Controller\Account;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Service\User\Supplier as SupplierUserService;

class Supplier extends \Controller\AuthController
{

    /**
     * 供货商列表显示
     */
    public function ListUser($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_supplier_listuser');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        //查询条件
        $formQuery                   = array();
        $formQuery['suppliers_name'] = $validator->validate('suppliers_name');
        $formQuery['suppliers_desc'] = $validator->validate('suppliers_desc');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 建立查询条件
        $condArray = QueryBuilder::buildQueryCondArray($formQuery);

        // 查询供货商列表
        $supplierUserService = new SupplierUserService();
        $totalCount          = $supplierUserService->countSupplierArray($condArray);
        if ($totalCount <= 0) { // 没用户，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Account/Supplier/ListUser');
        }

        // 供货商列表
        $supplierUserArray = $supplierUserService->fetchSupplierArray($condArray, $pageNo * $pageSize, $pageSize);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('supplierUserArray', $supplierUserArray);

        out_display:
        $smarty->display('account_supplier_listuser.tpl');
    }

    /**
     * 供货商详情显示
     */
    public function Edit($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_supplier_edit_get');

        global $smarty;

        // 参数验证
        $validator    = new Validator($f3->get('GET'));
        $suppliers_id = $validator->digits()->min(1)->validate('suppliers_id');
        $suppliers_id = ($suppliers_id > 0) ? $suppliers_id : 0;
        if (!$this->validate($validator)) {
            goto out;
        }

        // 查询供货商信息
        $supplierUserService = new SupplierUserService();
        $supplier            = $supplierUserService->loadSupplierById($suppliers_id);
        if (0 != $suppliers_id && $supplier->isEmpty()) { // 不存在的供货商
            $this->addFlashMessage('供货商不存在');
            goto out;
        }

        if ($supplier->isEmpty()) {
            // 新建供货商账号，权限检查
            $this->requirePrivilege('manage_account_supplier_create');
        }

        if (!$f3->get('POST')) {
            // 没有 post ，只是普通的显示
            goto out_display;
        }

        // 用户提交了更新请求，这里做供货商信息更新

        // 权限检查
        $this->requirePrivilege('manage_account_supplier_edit_post');

        // 参数验证
        $inputArray                      = array();
        $validator                       = new Validator($f3->get('POST'));
        $inputArray['suppliers_account'] = $validator->required()->minlength(4)->validate('suppliers_account');
        $inputArray['suppliers_name']    = $validator->required()->minlength(4)->validate('suppliers_name');
        $inputArray['phone']             = $validator->validate('phone');
        $inputArray['address']           = $validator->validate('address');
        $inputArray['suppliers_desc']    = $validator->validate('suppliers_desc');
        $password                        = $validator->validate('password');
        if (!Utils::isBlank($password)) {
            // 权限检查
            $this->requirePrivilege('manage_account_supplier_edit_change_account_password');
            $inputArray['password'] = $password;
        }

        if (!$this->validate($validator)) {
            goto out;
        }

        // 确认供货商账号没有重复
        if (!empty($inputArray['suppliers_account'])) {
            $tmpSupplierUser = $supplierUserService->loadSupplierBySupplierAccount($inputArray['suppliers_account']);
            if ((0 == $suppliers_id && !$tmpSupplierUser->isEmpty()) // 新建用户，已经有一个同名用户存在了
                || (!$tmpSupplierUser->isEmpty() && $tmpSupplierUser['suppliers_id'] != $suppliers_id) // 重复的用户
            ) {
                $this->addFlashMessage('供货商账号 ' . $inputArray['suppliers_account'] . ' 已经存在');
                goto out;
            }
        }

        // 如果供货商账号发生了变化
        if ($supplier['suppliers_account'] != $inputArray['suppliers_account']) {
            // 权限检查
            $this->requirePrivilege('manage_account_supplier_edit_change_account_password');
        }

        // 更新供货商信息
        $supplierUserService->updateSupplier($supplier, $inputArray);
        $this->addFlashMessage('供货商信息更新成功');

        out_display:
        //给 smarty 模板赋值
        $smarty->assign($supplier->toArray());

        out:
        $smarty->display('account_supplier_edit.tpl');
    }

    /**
     * 添加供货商
     *
     * @param $f3
     */
    public function Create($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_supplier_create');

        global $smarty;
        $smarty->display('account_supplier_edit.tpl');
    }
}