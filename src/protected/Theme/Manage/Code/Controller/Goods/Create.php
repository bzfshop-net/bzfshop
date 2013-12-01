<?php

/**
 * @author QiangYu
 *
 * 商品创建操作
 *
 * */

namespace Controller\Goods;

use Core\Helper\Utility\Validator;

class Create extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_create');

        global $smarty;
        $smarty->display('goods_create.tpl');
    }

}
