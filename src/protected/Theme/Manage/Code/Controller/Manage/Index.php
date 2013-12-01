<?php

/**
 * @author QiangYu
 *
 * 网站的缺省控制器
 *
 * */

namespace Controller\Manage;

use Core\Helper\Utility\Route as RouteHelper;

class Index extends \Controller\AuthController
{

    public function get($f3)
    {
        RouteHelper::reRoute($this, '/Community/Announce');
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
