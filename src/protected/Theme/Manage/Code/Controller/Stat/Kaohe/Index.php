<?php

/**
 * @author QiangYu
 *
 * 业绩考核统计
 *
 * */

namespace Controller\Stat\Kaohe;

use Core\Helper\Utility\Validator;

class Index extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;
        $smarty->display('stat_kaohe_index.tpl');
    }

}
