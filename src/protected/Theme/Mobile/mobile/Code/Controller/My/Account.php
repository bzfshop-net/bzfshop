<?php

/**
 * @author QiangYu
 *
 * 显示 我的账号 菜单列表
 *
 * */

namespace Controller\My;

use Core\Helper\Utility\Validator;

class Account extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        // 生成 smarty 的缓存 id
        $smartyCacheId = 'My|Account|get';

        enableSmartyCache(true, 3600); // 缓存 1 小时

        if ($smarty->isCached('my_account.tpl', $smartyCacheId)) {
            goto out_display;
        }

        out_display:
        $smarty->display('my_account.tpl', $smartyCacheId);
    }

}