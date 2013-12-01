<?php

/**
 * @author QiangYu
 *
 * 404 错误
 *
 * */

namespace Controller\Install;

use Core\Helper\Utility\Route as RouteHelper;

class Index extends \Controller\BaseController
{

    public function get($f3)
    {
        RouteHelper::reRoute($this, '/Install/Step1');
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
