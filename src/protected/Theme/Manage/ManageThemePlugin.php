<?php

/**
 * @author QiangYu
 *
 * */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Theme\Manage {

    use Core\Plugin\AbstractBasePlugin;
    use Core\Plugin\PluginHelper;

    class ManageThemePlugin extends AbstractBasePlugin
    {
        // 使用 UUID 作为插件的唯一 ID
        protected static $pluginUniqueId = 'C258CD90-CD7D-4EB4-862B-4F7185E86DEA';

        //允许哪些系统加载本插件
        protected static $systemAllowLoad = array(PluginHelper::SYSTEM_MANAGE);

        public function pluginActivate($system)
        {
            return false;
        }

        public function pluginDeactivate($system)
        {
            return false;
        }

        public function pluginAction($system)
        {
            // 为 manage 系统加载运行环境
            if (PluginHelper::SYSTEM_MANAGE === $system) {
                return true;
            }

            return false;
        }

        /**
         * 取得某个系统的 url base，比如取得商城的 url base 为  http://www.bangzhufu.com
         *
         * @param string $system
         *
         * @return array
         *
         * 结果为 array('name' => 'xxxx', 'desc' => 'xxxx', 'base' => 'http://www.bangzhufu.com')
         *
         */
        public static function getSystemUrlBase($system)
        {
            $theme_system_url_base_array = json_decode(self::getOptionValue('system_url_base_array'), true);
            if (empty($theme_system_url_base_array) || !array_key_exists($system, $theme_system_url_base_array)) {
                return null;
            }

            return $theme_system_url_base_array[$system];
        }

        /**
         * 设置某个系统的 url base， 比如 商城设置自己的  url base 为  http://www.bangzhufu.com ，
         * 这样后台系统就能根据这些设置生成对应系统的 URL ，
         * 比如查看商城的商品 地址为  http://www.bangzhufu.com/Goods/View?goods_id=123456
         *
         * @param string $system
         * @param string $name
         * @param string $desc
         * @param string $base
         */
        public static function saveSystemUrlBase($system, $name, $desc, $base)
        {
            $theme_system_url_base_array = json_decode(self::getOptionValue('system_url_base_array'), true);
            if (empty($theme_system_url_base_array)) {
                $theme_system_url_base_array = array();
            }
            $theme_system_url_base_array[$system] = array('name' => $name, 'desc' => $desc, 'base' => $base);
            self::saveOptionValue('system_url_base_array', json_encode($theme_system_url_base_array));
        }

        /**
         * 删除一个系统的  url base 设置
         *
         * @param string $system
         */
        public static function removeSystemUrlBase($system)
        {
            $theme_system_url_base_array = json_decode(self::getOptionValue('system_url_base_array'), true);
            if (empty($theme_system_url_base_array) || !array_key_exists($system, $theme_system_url_base_array)) {
                return;
            }
            unset($theme_system_url_base_array[$system]);
            self::saveOptionValue('system_url_base_array', json_encode($theme_system_url_base_array));
        }
    }

}
