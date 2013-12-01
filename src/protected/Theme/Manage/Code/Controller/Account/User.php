<?php

/**
 * @author QiangYu
 *
 * 操作管理员用户账号
 *
 * */

namespace Controller\Account;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Money;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Search\SearchHelper;
use Core\Service\User\AccountLog;
use Core\Service\User\AccountLog as AccountLogService;
use Core\Service\User\User as UserBasicService;

class User extends \Controller\AuthController
{

    /**
     * 用户搜索
     */
    public function Search($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_user_search');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        //查询条件
        $searchFormQuery              = array();
        $searchFormQuery['user_id']   = $validator->digits()->filter('ValidatorIntValue')->validate('user_id');
        $searchFormQuery['user_name'] = $validator->validate('user_name');
        $searchFormQuery['email']     = $validator->validate('email');

        $orderByUserMoney = $validator->validate('orderByUserMoney');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 建立查询条件
        $searchParamArray = QueryBuilder::buildSearchParamArray($searchFormQuery);

        // 查询管理员列表
        $totalCount =
            SearchHelper::count(SearchHelper::Module_User, $searchParamArray);
        if ($totalCount <= 0) { // 没用户，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Account/User/Search');
        }

        $orderByArray = array();
        switch ($orderByUserMoney) {
            case 1:
                $orderByArray[] = array('user_money', 'desc');
                break;
            case 2:
                $orderByArray[] = array('user_money', 'asc');
                break;
            default:
                break;
        }
        // 加入缺省排序
        $orderByArray[] = array('user_id', 'desc');

        // 用户列表
        $userInfoArray = SearchHelper::search(
            SearchHelper::Module_User,
            'user_id, user_name, email, sns_login, user_money, last_login',
            $searchParamArray,
            $orderByArray,
            $pageNo * $pageSize,
            $pageSize
        );

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('userInfoArray', $userInfoArray);

        out_display:
        $smarty->display('account_user_search.tpl');
    }

    /**
     * 显示用户详情
     */
    public function ajaxDetail($f3)
    {
        global $smarty;

        // 权限检查
        $this->requirePrivilege('manage_account_user_search');

        $errorMessage = '';
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $user_id   = $validator->required()->digits()->min(1)->validate('user_id');
        if (!$this->validate($validator)) {
            $errorMessage = '用户ID非法';
            goto out_fail;
        }

        // 取 order_goods
        $userBasicService = new UserBasicService();
        $userInfo         = $userBasicService->loadUserById($user_id);
        if ($userInfo->isEmpty() || $userInfo['user_id'] <= 0) {
            $errorMessage = '用户ID非法';
            goto out_fail;
        }

        // 给模板赋值
        $smarty->assign('userInfo', $userInfo);

        out_fail:
        $smarty->assign('errorMessage', $errorMessage);

        out_display:
        $smarty->display('account_user_ajaxdetail.tpl');
    }


    /**
     * 给用户余额充值
     * @param $f3
     */
    public function Charge($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_user_charge');

        $validator       = new Validator($f3->get('POST'));
        $user_id         = $validator->required()->digits()->validate('user_id');
        $chargeMoney     = Money::toStorage($validator->validate('chargeMoney'));
        $chargeMoneyDesc = $validator->validate('chargeMoneyDesc');

        if (!$this->validate($validator)) {
            goto out;
        }

        if (0 == $chargeMoney) {
            $this->addFlashMessage('充值为0，不操作');
            goto out;
        }

        // 加载用户信息
        $userBasicService = new UserBasicService();
        $userInfo         = $userBasicService->loadUserById($user_id);
        if ($userInfo->isEmpty()) {
            $this->addFlashMessage('用户ID非法[' . $user_id . ']');
            goto out;
        }

        // 当前操作的管理员
        $authAdminUser = AuthHelper::getAuthUser();

        // 给用户充值
        $accountLog = new AccountLog();
        $accountLog->logChange(
            $user_id,
            $chargeMoney,
            0,
            0,
            0,
            '管理员[' . $authAdminUser['user_name'] . ']充值[' . Money::toSmartyDisplay($chargeMoney) . ']元'
            . "\n" . $chargeMoneyDesc,
            ($chargeMoney > 0) ? AccountLog::ACT_SAVING : AccountLog::ACT_DRAWING,
            $authAdminUser['user_id']
        );

        $this->addFlashMessage('充值 [' . Money::toSmartyDisplay($chargeMoney) . '] 元成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    /**
     * 用户资金变动列表
     *
     * @param $f3
     */
    public function Money($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_account_user_money');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        //查询条件
        $formQuery                  = array();
        $formQuery['user_id']       = $validator->filter('ValidatorIntValue')->validate('user_id');
        $formQuery['admin_user_id'] = $validator->filter('ValidatorIntValue')->validate('admin_user_id');
        $formQuery['change_type']   = $validator->filter('ValidatorIntValue')->validate('change_type');
        $formQuery['change_desc']   = $validator->validate('change_desc');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 20;

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 建立查询条件
        $condArray = QueryBuilder::buildQueryCondArray($formQuery);

        // 查询资金列表
        $accountLogService = new AccountLogService();
        $totalCount        = $accountLogService->_countArray('account_log', $condArray);
        if ($totalCount <= 0) { // 没用户，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Account/User/Money');
        }

        // 资金列表
        $accountLogArray =
            $accountLogService->_fetchArray(
                'account_log',
                '*',
                $condArray,
                array('order' => 'log_id desc'),
                $pageNo * $pageSize,
                $pageSize
            );

        // 取用户信息
        $userIdArray = array();
        foreach ($accountLogArray as $accountLogItem) {
            $userIdArray[] = $accountLogItem['user_id'];
        }
        $userIdArray      = array_unique($userIdArray);
        $userBasicService = new UserBasicService();
        $userInfoArray    = $userBasicService->fetchUserArrayByUserIdArray($userIdArray);
        //建立倒查表
        $userIdToUserInfoMap = array();
        foreach ($userInfoArray as $userInfo) {
            $userIdToUserInfoMap[$userInfo['user_id']] = $userInfo;
        }

        // 转换显示
        foreach ($accountLogArray as &$accountLogItem) {
            $accountLogItem['user_name']        = @$userIdToUserInfoMap[$accountLogItem['user_id']]['user_name'];
            $accountLogItem['change_type_desc'] = AccountLogService::$changeTypeDesc[$accountLogItem['change_type']];
        }

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('accountLogArray', $accountLogArray);

        out_display:
        $smarty->display('account_user_money.tpl');
    }

}