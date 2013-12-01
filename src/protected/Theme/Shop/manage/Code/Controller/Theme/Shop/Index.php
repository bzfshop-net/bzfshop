<?php

/**
 * @author QiangYu
 *
 * 棒主妇商城设置
 *
 * */

namespace Controller\Theme\Shop;


use Core\Helper\Utility\Validator;

class Index extends Base
{

    public function get($f3)
    {
        global $smarty;
        $smarty->display('theme_shop_index.tpl', 'get');
    }
}
