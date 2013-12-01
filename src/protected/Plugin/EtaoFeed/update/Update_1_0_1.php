<?php

/**
 * 升级程序，增加统计代码配置
 */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Plugin\Thirdpart\EtaoFeed {

    use Core\Plugin\AbstractUpdate;

    class Update_1_0_1 extends AbstractUpdate
    {

        /**
         * 接受从 1.0.0 版本做升级
         */
        protected $sourceVersionAllowed = array('1.0.0');

        /**
         * 把版本升级到 1.0.1
         */
        protected $targetVersion = '1.0.1';

        public function doUpdate($currentVersion)
        {

            // 增加了一个新的参数
            EtaoFeedPlugin::saveOptionValue('etaofeed_goods_url_extra_param', '&utm_source=etao');

            // 把版本升级到 1.0.1
            EtaoFeedPlugin::saveOptionValue('version', $this->targetVersion);

            return true;
        }
    }
}

// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 update instance
    return Plugin\Thirdpart\EtaoFeed\Update_1_0_1::instance();
}

