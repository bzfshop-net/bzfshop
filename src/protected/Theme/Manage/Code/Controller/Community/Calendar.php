<?php

/**
 * @author QiangYu
 *
 * 开源社区的集成
 *
 * */

namespace Controller\Community;

use Core\Helper\Utility\Route as RouteHelper;

class Calendar extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;
        $smarty->display('community_calendar.tpl');
    }

}
