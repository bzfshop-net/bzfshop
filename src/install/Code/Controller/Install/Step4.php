<?php

/**
 * @author QiangYu
 *
 * 404 错误
 *
 * */

namespace Controller\Install;

use Core\Helper\Utility\Route as RouteHelper;

class Step4 extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        $smarty->assign('envFile', realpath(INSTALL_PATH . '/../protected/Config/env.cfg'));
        $smarty->assign('configFile', realpath(INSTALL_PATH . '/../protected/Config/common-prod.cfg'));

        $smarty->display('install_step4.tpl');
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
