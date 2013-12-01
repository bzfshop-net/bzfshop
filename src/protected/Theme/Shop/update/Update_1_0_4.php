<?php

/**
 * 从 1.0.3 升级到 1.0.4
 */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Theme\Shop {

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
            // 增加了新的设置选项
            ShopThemePlugin::saveOptionValue('shop_index_notice', '这里是网站公告');
            ShopThemePlugin::saveOptionValue('smarty_cache_time_article_view', 86400);

            $jsonArray = array(array('title' => '', 'url' => ''));
            ShopThemePlugin::saveOptionValue('head_nav_json_data', json_encode($jsonArray));

            $jsonArray = array(
                0 => array(
                    'title' => '新手指南',
                    'item'  => array(
                        0 => array('title' => '账户注册', 'url' => '#'),
                        1 => array('title' => '购物流程', 'url' => '#'),
                        3 => array('title' => '付款方式', 'url' => '#'),
                        4 => array('title' => '常见问题', 'url' => '#'),
                    )
                ),
                1 => array(
                    'title' => '配送方式',
                    'item'  => array(
                        0 => array('title' => '配送说明', 'url' => '#'),
                        1 => array('title' => '订单拆分', 'url' => '#'),
                    )
                ),
                2 => array(
                    'title' => '支付方式',
                    'item'  => array(
                        0 => array('title' => '在线支付', 'url' => '#'),
                        1 => array('title' => '其它支付方式', 'url' => '#'),
                    )
                ),
                3 => array(
                    'title' => '售后服务',
                    'item'  => array(
                        0 => array('title' => '退换货政策', 'url' => '#'),
                        1 => array('title' => '退换货办理流程', 'url' => '#'),
                    )
                ),
                4 => array(
                    'title' => '购物保障',
                    'item'  => array(
                        0 => array('title' => '免责声明', 'url' => '#'),
                        1 => array('title' => '隐私保护', 'url' => '#'),
                        2 => array('title' => '注册协议', 'url' => '#'),
                        3 => array('title' => '正品保障', 'url' => '#'),
                    )
                ),
            );
            ShopThemePlugin::saveOptionValue('foot_nav_json_data', json_encode($jsonArray));

            $jsonArray = array(array('title' => '', 'url' => ''));
            ShopThemePlugin::saveOptionValue('friend_link_json_data', json_encode($jsonArray));

            // 设置新的版本
            ShopThemePlugin::saveOptionValue('version', $this->targetVersion);

            return true;
        }
    }
}


// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 update instance
    return new Theme\Shop\Update_1_0_4();
}

