<?php

/**
 * @author QiangYu
 *
 * 杂项设置
 *
 * */

namespace Controller\Misc;

use Core\Cache\ClearHelper;
use Core\Cache\ShareClear;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;

class Cache extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;
        $smarty->display('misc_cache.tpl');
    }

    public function clearAllCache($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_misc_cache');

        ClearHelper::clearAllCache();

        $this->addFlashMessage('所有缓存清理成功');
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    public function clearDataCache($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_misc_cache');

        $shareClear = new ShareClear();
        $shareClear->clearAllCache();

        $this->addFlashMessage('数据缓存清理成功');
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    public function clearHomePage($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_misc_cache');

        ClearHelper::clearHomePage();

        $this->addFlashMessage('首页缓存清理成功');
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    public function clearGoodsCategory($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_misc_cache');

        ClearHelper::clearGoodsCategory();
        $this->addFlashMessage('商品类目页面清理成功');

        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    public function clearGoodsCacheById($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_misc_cache');

        // 参数验证
        $validator = new Validator($f3->get('POST'));
        $goods_id  = $validator->required('商品ID不能为空')->digits()->validate('goods_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        ClearHelper::clearGoodsCacheById($goods_id);
        $this->addFlashMessage('商品[' . $goods_id . ']页面清理成功');

        out: // 从这里退出
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    public function clearArticleCacheById($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_misc_cache');

        // 参数验证
        $validator  = new Validator($f3->get('POST'));
        $article_id = $validator->required('商品ID不能为空')->digits()->validate('article_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        ClearHelper::clearArticleCacheById($article_id);
        $this->addFlashMessage('商品[' . $article_id . ']页面清理成功');

        out: // 从这里退出
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

}
