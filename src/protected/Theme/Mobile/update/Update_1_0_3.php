<?php

/**
 * 从 1.0.2 升级到 1.0.3
 */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Theme\Mobile {

    use Core\Plugin\AbstractUpdate;

    class Update_1_0_3 extends AbstractUpdate
    {

        protected $sourceVersionAllowed = array('1.0.1', '1.0.2');

        /**
         * 把版本升级到 1.0.3
         */
        protected $targetVersion = '1.0.3';

        public function doUpdate($currentVersion)
        {
            // 增加了新的设置选项
            MobileThemePlugin::saveOptionValue('goods_after_service', '这里是售后服务说明');

            // 设置新的版本
            MobileThemePlugin::saveOptionValue('version', $this->targetVersion);

            return true;
        }
    }
}


// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 update instance
    return new Theme\Mobile\Update_1_0_3();
}

