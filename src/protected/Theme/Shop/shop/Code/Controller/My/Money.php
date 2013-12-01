<?php

/**
 * @author QiangYu
 *
 * 我的资金变化显示
 *
 * */

namespace Controller\My;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\User\AccountLog as AccountLogService;
use Core\Service\User\User as UserBasicService;

class Money extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        if (!$this->validate($validator)) {
            goto out_display;
        }

        $userInfo = AuthHelper::getAuthUser();

        $userBasicService = new UserBasicService();
        $userInfo         = $userBasicService->loadUserById($userInfo['user_id']);

        // 用户总共有资金余额
        $smarty->assign('userMoney', $userInfo['user_money']);
        $accountLog = new AccountLogService();

        // 用户总共有多少account_log
        $totalCount = $accountLog->countUserMoneyArray($userInfo['user_id'], 10); //缓存 10 秒钟
        if ($totalCount <= 0) { // 没资金变动记录，可以直接退出了
            goto out_display;
        }

        $smarty->assign('totalCount', $totalCount);

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/My/Money');
        }

        // 传递分页的变量
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);

        // account_log 列表
        $accountLogArray =
            $accountLog->fetchUserMoneyArray($userInfo['user_id'], $pageNo * $pageSize, $pageSize, 10); //缓存 10 秒钟
        foreach ($accountLogArray as &$accountLogItem) {
            $accountLogItem['change_type_desc'] = AccountLogService::$changeTypeDesc[$accountLogItem['change_type']];
        }
        unset($accountLogItem);
        $smarty->assign('accountLogArray', $accountLogArray);

        out_display:
        $smarty->display('my_money.tpl', 'get');
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
