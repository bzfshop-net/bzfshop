<?php

/**
 * @author QiangYu
 *
 * 订单统计
 *
 * */

namespace Controller\Stat\Order;

use Core\Helper\Utility\Validator;

class Index extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;
        $smarty->display('stat_order_index.tpl');
    }

}
