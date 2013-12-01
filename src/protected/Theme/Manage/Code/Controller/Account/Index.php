<?php

/**
 * @author QiangYu
 *
 * 账号管理首页
 *
 * */

namespace Controller\Account;

use Core\Helper\Utility\Validator;

class Index extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;
        $smarty->display('account_index.tpl');
    }

}
