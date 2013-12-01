<?php

/**
 * @author QiangYu
 *
 * 输出商品分类 html
 *
 * */

namespace Controller\Ajax;

use Core\Service\Goods\Category as GoodsCategoryService;

class Category extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        // 生成 smarty 的缓存 id
        $smartyCacheId = 'Ajax|Category';

        // 开启并设置 smarty 缓存时间
        enableSmartyCache(true, bzf_get_option_value('smarty_cache_time_ajax_category'));

        if ($smarty->isCached('ajax_category.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // 取得商品分类树形结构
        $goodsCategoryService   = new GoodsCategoryService();
        $goodsCategoryTreeArray = $goodsCategoryService->fetchCategoryTreeArray(0);
        $smarty->assign('goodsCategoryTreeArray', $goodsCategoryTreeArray);

        out_display:
        $f3->expire(600); // 让客户端缓存 10 分钟
        $smarty->display('ajax_category.tpl', $smartyCacheId);
    }

}