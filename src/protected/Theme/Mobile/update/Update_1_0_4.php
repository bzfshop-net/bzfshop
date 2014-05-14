<?php

/**
 * 从 1.0.3 升级到 1.0.4
 */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Theme\Mobile {

    use Core\Plugin\AbstractUpdate;

    class Update_1_0_4 extends AbstractUpdate
    {

        protected $sourceVersionAllowed = array('1.0.3');

        /**
         * 把版本升级到 1.0.4
         */
        protected $targetVersion = '1.0.4';

        public function doUpdate($currentVersion)
        {
            // 删除旧的设置，由于 JQuery Mobile 本身的特性，不适合用传统的统计代码来做统计
            MobileThemePlugin::removeOptionValue('statistics_code');

            // 新的统计设置选项
            // Google Analytics 的 UA 设置
            MobileThemePlugin::saveOptionValue('google_analytics_ua', '');

            // 设置新的版本
            MobileThemePlugin::saveOptionValue('version', $this->targetVersion);

            return true;
        }
    }
}


// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 update instance
    return new Theme\Mobile\Update_1_0_4();
}

