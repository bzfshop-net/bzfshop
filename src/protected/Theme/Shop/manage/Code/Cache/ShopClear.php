<?php
/**
 *
 * 清除 Shop 工程的 smarty 缓存
 *
 */

namespace Cache;

use Core\Cache\AbstractClear;
use Core\Cache\ClearHelper;

class ShopClear extends AbstractClear
{

    /**
     * 取得一个和 Theme 初始化一样的 Smarty 对象，只有这样才能正确处理缓存清理工作
     */
    private function getThemeSmarty()
    {
        $themeSmarty = new \Smarty();

        //设置 smarty 工作目录
        $themeSmarty->setCompileDir(RUNTIME_PATH . '/Smarty/Shop/Compile');
        $themeSmarty->setCacheDir(RUNTIME_PATH . '/Smarty/Shop/Cache');

        // 获取当前插件的根地址
        $currentThemeBasePath = realpath(dirname(__FILE__) . '/../../../');

        // 增加 smarty 模板搜索路径
        $themeSmarty->addTemplateDir($currentThemeBasePath . '/shop/Tpl/');

        return $themeSmarty;
    }

    public function clearAllCache()
    {
        ClearHelper::smartyClearAllCache($this->getThemeSmarty());
    }

    public function clearHomePage()
    {
        ClearHelper::smartyClearCache($this->getThemeSmarty(), null, 'Shop|Index');
    }

    public function clearGoodsCategory()
    {
        ClearHelper::smartyClearCache($this->getThemeSmarty(), null, 'Ajax|Category');
    }

    public function clearGoodsCacheById($goods_id)
    {
        ClearHelper::smartyClearCache($this->getThemeSmarty(), null, 'Goods|' . $goods_id);
    }

    public function clearArticleCacheById($article_id)
    {
        ClearHelper::smartyClearCache($this->getThemeSmarty(), null, 'Article|' . $article_id);
    }

    /**
     * 清除搜索页缓存
     */
    public function clearGoodsSearch()
    {
        ClearHelper::smartyClearCache($this->getThemeSmarty(), null, 'Goods|Search');
        ClearHelper::smartyClearCache($this->getThemeSmarty(), null, 'Goods|Category');
    }
}