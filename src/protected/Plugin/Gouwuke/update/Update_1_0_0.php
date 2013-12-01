<?php

/**
 * 升级程序，做基本的升级
 */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Plugin\Thirdpart\Gouwuke {

    use Core\Plugin\AbstractUpdate;

    class Update_1_0_0 extends AbstractUpdate
    {

        /**
         * 1.0.0 是最初始的版本，所以只接受 null 版本升级
         */
        protected $sourceVersionAllowed = array(null);

        /**
         * 把版本升级到 1.0.0
         */
        protected $targetVersion = '1.0.0';

        public function doUpdate($currentVersion)
        {
            // 简单的把版本设置为 1.0.0 就算完成升级了
            GouwukePlugin::saveOptionValue('version', $this->targetVersion);

            return true;
        }
    }
}

// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 update instance
    return Plugin\Thirdpart\Gouwuke\Update_1_0_0::instance();
}

