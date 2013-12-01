<?php

/**
 * 升级程序，goods_category 使用 goods_search 代替
 */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Theme\Mobile {

    use Core\Plugin\AbstractUpdate;

    class Update_1_0_1 extends AbstractUpdate
    {

        /**
         * 1.0.0 是最初始的版本
         */
        protected $sourceVersionAllowed = array('1.0.0');

        /**
         * 把版本升级到 1.0.1
         */
        protected $targetVersion = '1.0.1';

        public function doUpdate($currentVersion)
        {
            // 获得插件的配置 (config.php 里面的信息)
            $configArray = MobileThemePlugin::$instance->loadConfigFile();

            // 删除 smarty_cache_time_goods_category 设置
            MobileThemePlugin::removeOptionValue('smarty_cache_time_goods_category');

            // 添加一个新的设置
            MobileThemePlugin::saveOptionValue(
                'smarty_cache_time_goods_search',
                $configArray['smarty_cache_time_goods_search']
            );

            // 升级版本信息
            MobileThemePlugin::saveOptionValue('version', $this->targetVersion);

            return true;
        }
    }
}


// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 update instance
    return new Theme\Mobile\Update_1_0_1();
}

