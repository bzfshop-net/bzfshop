<?php

/**
 * @author QiangYu
 *
 * 网站分类列表
 *
 * */

namespace Controller\Goods;

use Theme\Mobile\MobileThemePlugin;

class CategoryList extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        // 生成 smarty 的缓存 id
        $smartyCacheId = 'Goods|CategoryList';

        enableSmartyCache(true, MobileThemePlugin::getOptionValue('smarty_cache_time_cache_page')); // 缓存页面

        if ($smarty->isCached('goods_categorylist.tpl', $smartyCacheId)) {
            goto out_display;
        }

        out_display:
        $smarty->display('goods_categorylist.tpl', $smartyCacheId);

    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
