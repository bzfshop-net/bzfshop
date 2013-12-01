<?php

/**
 * @author QiangYu
 *
 * 插件加载文件，用于初始化插件同时返回一个插件的 instance 对象
 *
 * */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Plugin\Thirdpart\EtaoFeed {

    use Core\Helper\Utility\Route as RouteHelper;
    use Core\Plugin\AbstractBasePlugin;
    use Core\Plugin\PluginHelper;
    use Core\Plugin\SystemHelper;

    class EtaoFeedPlugin extends AbstractBasePlugin
    {
        // 使用 UUID 作为插件的唯一 ID
        protected static $pluginUniqueId = '7CB8A8B4-7621-43EB-B36D-16E8C170E253';

        // 允许哪些系统加载本插件
        protected static $systemAllowLoad = array(
            PluginHelper::SYSTEM_MANAGE,
            PluginHelper::SYSTEM_SHOP,
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
                return RouteHelper::makeUrl('/Thirdpart/EtaoFeed/Configure');
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

            // 为 shop 系统加载运行环境
            if (PluginHelper::SYSTEM_SHOP === $system) {
                return $this->doEtaoFeedAction();
            }

            return false;
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
                '/Thirdpart/EtaoFeed/Configure',
                'Controller\Thirdpart\EtaoFeed\Configure'
            );

            // 增加 smarty 模板搜索路径
            global $smarty;
            $smarty->addTemplateDir($currentPluginBasePath . '/manage/Tpl/');

            return true;
        }

        /**
         * 为系统设置运行环境
         *
         * @return bool
         */
        private function doEtaoFeedAction()
        {
            // 获取当前插件的根地址
            $currentPluginBasePath = dirname(__FILE__);

            // yiqifacps code 目录加入到 auto load 的路径中，这样系统就能自动做 class 加载
            SystemHelper::addAutoloadPath($currentPluginBasePath . '/shop/Code');

            // 设置路由，这样用户就能访问到我们的程序了
            SystemHelper::addRouteMap(
                '/Thirdpart/EtaoFeed/Category',
                'Controller\Thirdpart\EtaoFeed\Category'
            );
            SystemHelper::addRouteMap(
                '/Thirdpart/EtaoFeed/FullIndex',
                'Controller\Thirdpart\EtaoFeed\FullIndex'
            );
            SystemHelper::addRouteMap(
                '/Thirdpart/EtaoFeed/IncIndex',
                'Controller\Thirdpart\EtaoFeed\IncIndex'
            );
            SystemHelper::addRouteMap(
                '/Thirdpart/EtaoFeed/Item/@fileName',
                'Controller\Thirdpart\EtaoFeed\Item'
            );
            return true;
        }

    }

}

// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 plugin instance
    return Plugin\Thirdpart\EtaoFeed\EtaoFeedPlugin::instance();
}