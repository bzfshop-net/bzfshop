<?php

/**
 * @author QiangYu
 *
 * 开源社区的集成
 *
 * */

namespace Controller\Community;

use Core\Helper\Utility\Route as RouteHelper;

class Announce extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;
        $smarty->assign('version', $f3->get('sysConfig[version]'));
        $smarty->assign('release_date', $f3->get('sysConfig[release_date]'));
        $smarty->display('community_announce.tpl');
    }

}
