<?php
/**
 *
 * 清除 Mobile 工程的 smarty 缓存
 *
 */

namespace Cache;

use Core\Cache\AbstractClear;
use Core\Cache\ClearHelper;

class MobileClear extends AbstractClear
{

    /**
     * 取得一个和 Theme 初始化一样的 Smarty 对象，只有这样才能正确处理缓存清理工作
     */
    private function getThemeSmarty()
    {
        $themeSmarty = new \Smarty();

        //设置 smarty 工作目录
        $themeSmarty->setCompileDir(RUNTIME_PATH . '/Smarty/Mobile/Compile');
        $themeSmarty->setCacheDir(RUNTIME_PATH . '/Smarty/Mobile/Cache');

        // 获取当前插件的根地址
        $currentThemeBasePath = realpath(dirname(__FILE__) . '/../../../');

        // 增加 smarty 模板搜索路径
        $themeSmarty->addTemplateDir($currentThemeBasePath . '/mobile/Tpl/');

        return $themeSmarty;
    }

    public function clearAllCache()
    {
        ClearHelper::smartyClearAllCache($this->getThemeSmarty());
    }

    public function clearHomePage()
    {
        ClearHelper::smartyClearCache($this->getThemeSmarty(), null, 'Mobile|Index');
    }

    public function clearGoodsCacheById($goods_id)
    {
        ClearHelper::smartyClearCache($this->getThemeSmarty(), null, 'Goods|' . $goods_id);
    }
}