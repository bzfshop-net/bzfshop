<?php

/**
 * 从 1.0.1 升级到 1.0.2
 */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Theme\Shop {

    use Core\Plugin\AbstractUpdate;

    class Update_1_0_2 extends AbstractUpdate
    {

        protected $sourceVersionAllowed = array('1.0.1');

        /**
         * 把版本升级到 1.0.2
         */
        protected $targetVersion = '1.0.2';

        public function doUpdate($currentVersion)
        {
            // 增加了新的设置选项
            ShopThemePlugin::saveOptionValue('business_qq', null);

            // 设置新的版本
            ShopThemePlugin::saveOptionValue('version', $this->targetVersion);

            return true;
        }
    }
}


// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 update instance
    return new Theme\Shop\Update_1_0_2();
}

