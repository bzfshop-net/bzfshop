<?php

/**
 * @author QiangYu
 *
 * 商品创建操作
 *
 * */

namespace Controller\Order;

use Core\Helper\Utility\Validator;

class Index extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;
        $smarty->display('order_index.tpl');
    }

}
