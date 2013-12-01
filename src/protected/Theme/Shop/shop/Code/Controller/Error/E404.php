<?php

/**
 * @author QiangYu
 *
 * 404 错误
 *
 * */

namespace Controller\Error;

class E404 extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;
        $smarty->display('error_e404.tpl');
    }

    public function post($f3)
    {
        $this->get($f3);
    }
}
