<?php

/**
 * @author QiangYu
 *
 * 输出 Cache Page，前端调用一次就永久缓存了，方便我们后面的操作
 *
 * */

namespace Controller\Cache;

use Theme\Mobile\MobileThemePlugin;

class Page extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        // 生成 smarty 的缓存 id
        $smartyCacheId = 'Cache|Page';

        enableSmartyCache(true, MobileThemePlugin::getOptionValue('smarty_cache_time_cache_page')); // 缓存页面

        if ($smarty->isCached('cache_page.tpl', $smartyCacheId)) {
            goto out_display;
        }

        out_display:
        $smarty->display('cache_page.tpl', $smartyCacheId);
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
