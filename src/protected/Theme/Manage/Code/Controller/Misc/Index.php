<?php

/**
 * @author QiangYu
 *
 * 杂项设置
 *
 * */

namespace Controller\Misc;

use Core\Helper\Utility\Validator;

class Index extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;
        $smarty->display('misc_index.tpl');
    }

}
