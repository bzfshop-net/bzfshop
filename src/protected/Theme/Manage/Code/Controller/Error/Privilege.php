<?php

/**
 * @author QiangYu
 *
 * 404 错误
 *
 * */

namespace Controller\Error;

use Core\Helper\Utility\Route as RouteHelper;
use Core\Service\Meta\Privilege as MetaPrivilegeService;
use Core\Service\User\Admin as UserAdminService;

class Privilege extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;

        $privilegeKey  = $f3->get('GET[privilege]');
        $privilegeItem = array();

        if (!empty($privilegeKey)) {

            if (UserAdminService::privilegeAll == $privilegeKey) {
                $privilegeItem['meta_name'] = '最高权限';
                $privilegeItem['meta_desc'] = '系统的最高权限';
            } else {
                $metaPrivilegeService = new MetaPrivilegeService();
                $privilege            = $metaPrivilegeService->loadPrivilegeItem($privilegeKey);
                $privilegeItem        = $privilege->toArray();
            }
        }

        $smarty->assign('privilegeItem', $privilegeItem);
        $smarty->assign('refer_url', RouteHelper::getRefer());
        $smarty->display('error_privilege.tpl');
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
