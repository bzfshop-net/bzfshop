<?php

/**
 * @author QiangYu
 *
 * 404 错误
 *
 * */

namespace Controller\Install;

use Core\Helper\Utility\Route as RouteHelper;

class Step1 extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        $smarty->display('install_step1.tpl');
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
