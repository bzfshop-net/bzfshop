<?php

/**
 * @author QiangYu
 *
 * 插件加载文件，用于初始化插件同时返回一个插件的 instance 对象
 *
 * */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Plugin\Thirdpart\QQAuth {

    use Core\Helper\Utility\Route as RouteHelper;
    use Core\Plugin\AbstractBasePlugin;
    use Core\Plugin\PluginHelper;
    use Core\Plugin\SystemHelper;

    class QQAuthPlugin extends AbstractBasePlugin
    {
        // 使用 UUID 作为插件的唯一 ID
        protected static $pluginUniqueId = '90B3F08E-971B-4D04-A1B3-BF4FA341BD96';

        // 允许哪些系统加载本插件
        protected static $systemAllowLoad = array(
            PluginHelper::SYSTEM_MANAGE,
            PluginHelper::SYSTEM_GROUPON,
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
                return RouteHelper::makeUrl('/Thirdpart/QQAuth/Configure');
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

            return $this->doGrouponAction();
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

            // manage 目录加入到 auto load 的路径中，这样系统就能自动做 class 加载
            SystemHelper::addAutoloadPath($currentPluginBasePath . '/manage/Code');

            // 设置路由，这样用户就能访问到我们的程序了
            SystemHelper::addRouteMap(
                '/Thirdpart/QQAuth/Configure',
                'Controller\Thirdpart\QQAuth\Configure'
            );

            // 增加 smarty 模板搜索路径
            global $smarty;
            $smarty->addTemplateDir($currentPluginBasePath . '/manage/Tpl/');

            return true;
        }

        /**
         * 为 groupon 系统设置运行环境
         *
         * @return bool
         */
        private function doGrouponAction()
        {
            // 获取当前插件的根地址
            $currentPluginBasePath = dirname(__FILE__);

            // groupon 目录加入到 auto load 的路径中，这样系统就能自动做 class 加载
            SystemHelper::addAutoloadPath($currentPluginBasePath . '/groupon/Code');

            // 设置路由，这样用户就能访问到我们的程序了
            SystemHelper::addRouteMap(
                '/Thirdpart/QQAuth/Login',
                'Controller\Thirdpart\QQAuth\Login'
            );
            SystemHelper::addRouteMap(
                '/Thirdpart/QQAuth/Callback',
                'Controller\Thirdpart\QQAuth\Callback'
            );

            return true;
        }

    }

}

// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 plugin instance
    return Plugin\Thirdpart\QQAuth\QQAuthPlugin::instance();
}