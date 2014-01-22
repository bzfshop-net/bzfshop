<?php

/**
 * 从 1.0.4 升级到 1.0.5
 */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Theme\Shop {

    use Core\Plugin\AbstractUpdate;

    class Update_1_0_5 extends AbstractUpdate
    {

        protected $sourceVersionAllowed = array('1.0.4');

        /**
         * 把版本升级到 1.0.5
         */
        protected $targetVersion = '1.0.5';

        public function doUpdate($currentVersion)
        {
            // 增加了新的设置选项
            ShopThemePlugin::saveOptionValue('goods_view_detail_notice', null);

            // 设置新的版本
            ShopThemePlugin::saveOptionValue('version', $this->targetVersion);

            return true;
        }
    }
}


// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 update instance
    return new Theme\Shop\Update_1_0_5();
}

