<?php

/**
 * @author QiangYu
 *
 * 插件加载文件，用于初始化插件同时返回一个插件的 instance 对象
 *
 * */


// 定义自己的 namespace ，防止和别的插件冲突
namespace Plugin\Thirdpart\Dev360Auth {

    use Controller\Thirdpart\Dev360Auth\Callback;
    use Controller\Thirdpart\Dev360Auth\Login;
    use Core\Helper\Utility\Route as RouteHelper;
    use Core\Plugin\AbstractBasePlugin;
    use Core\Plugin\PluginHelper;
    use Core\Plugin\SystemHelper;

    class Dev360AuthPlugin extends AbstractBasePlugin
    {
        // 使用 UUID 作为插件的唯一 ID
        protected static $pluginUniqueId = 'BCA22CBB-8107-4F50-8C07-63EFCAB494CE';

        // 允许哪些系统加载本插件
        protected static $systemAllowLoad = array(
            PluginHelper::SYSTEM_MANAGE,
            PluginHelper::SYSTEM_SHOP,
            // 其它系统
            PluginHelper::SYSTEM_AIMEIDAREN
        );

        public function pluginActivate($system)
        {
            return true;
        }

        public function pluginDeactivate($system)
        {
            return true;
        }

        public function pluginGetConfigureUrl($system)
        {
            // manage 系统可以配置这个插件
            if (PluginHelper::SYSTEM_MANAGE === $system) {
                return RouteHelper::makeUrl('/Thirdpart/Dev360Auth/Configure');
            }

            // 其它系统不需要配置
            return null;
        }

        public function pluginAction($system)
        {
            // 为 manage 系统加载运行环境
            if (PluginHelper::SYSTEM_MANAGE === $system) {
                return $this->doManageAction();
            }

            // 为 aimeidaren 系统加载运行环境
            if (PluginHelper::SYSTEM_AIMEIDAREN === $system) {
                return $this->doAimeidarenAction();
            }

            // 为 其它前端系统 系统加载运行环境
            return $this->doShopAction();
        }

        /**
         * 为 manage 系统设置运行环境
         *
         * @return bool
         */
        private function doManageAction()
        {
            // 获取当前插件的根地址
            $currentPluginBasePath = dirname(__FILE__);

            // mobile 目录加入到 auto load 的路径中，这样系统就能自动做 class 加载
            SystemHelper::addAutoloadPath($currentPluginBasePath . '/manage/Code');

            // 设置路由，这样用户就能访问到我们的程序了
            SystemHelper::addRouteMap(
                '/Thirdpart/Dev360Auth/Configure',
                'Controller\Thirdpart\Dev360Auth\Configure'
            );

            // 增加 smarty 模板搜索路径
            global $smarty;
            $smarty->addTemplateDir($currentPluginBasePath . '/manage/Tpl/');

            return true;
        }

        private function doDev360AuthAction()
        {
            // 设置路由，这样用户就能访问到我们的程序了
            SystemHelper::addRouteMap(
                '/Thirdpart/Dev360Auth/Login',
                'Controller\Thirdpart\Dev360Auth\Login'
            );
            SystemHelper::addRouteMap(
                '/Thirdpart/Dev360Auth/Callback',
                'Controller\Thirdpart\Dev360Auth\Callback'
            );

            return true;
        }

        /**
         * 为 Shop 系统设置运行环境
         *
         * @return bool
         */
        private function doShopAction()
        {
            // 获取当前插件的根地址
            $currentPluginBasePath = dirname(__FILE__);

            // Shop 目录加入到 auto load 的路径中，这样系统就能自动做 class 加载
            SystemHelper::addAutoloadPath($currentPluginBasePath . '/shop/Code');

            // 设置 Prefix
            Callback::$optionKeyPrefix = 'shop_';
            Login::$optionKeyPrefix    = 'shop_';

            return $this->doDev360AuthAction();
        }

        /**
         * 为 aimeidaren 设置运行环境
         *
         * @return bool
         */
        private function doAimeidarenAction()
        {
            // 获取当前插件的根地址
            $currentPluginBasePath = dirname(__FILE__);

            // Shop 目录加入到 auto load 的路径中，这样系统就能自动做 class 加载
            SystemHelper::addAutoloadPath($currentPluginBasePath . '/shop/Code');

            // 设置 Prefix
            Callback::$optionKeyPrefix = 'aimeidaren_';
            Login::$optionKeyPrefix    = 'aimeidaren_';

            return $this->doDev360AuthAction();
        }

    }

}

// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 plugin instance
    return Plugin\Thirdpart\Dev360Auth\Dev360AuthPlugin::instance();
}
