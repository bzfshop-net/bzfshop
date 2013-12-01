<?php

/**
 * @author QiangYu
 *
 * 文章管理
 *
 * */

namespace Controller\Article;

use Core\Helper\Utility\Validator;

class Index extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;
        $smarty->display('article_index.tpl');
    }

}
