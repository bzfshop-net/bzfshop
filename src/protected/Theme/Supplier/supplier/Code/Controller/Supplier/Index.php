<?php

/**
 * @author QiangYu
 *
 * 网站的缺省控制器
 *
 * */

namespace Controller\Supplier;

use Core\Helper\Utility\Route as RouteHelper;

class Index extends \Controller\AuthController
{

    public function get($f3)
    {
        RouteHelper::reRoute($this, '/Order/Goods/Search');
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
