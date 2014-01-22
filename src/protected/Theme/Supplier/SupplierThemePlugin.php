<?php

/**
 * @author QiangYu
 *
 * */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Theme\Supplier {

    use Core\Plugin\AbstractBasePlugin;
    use Core\Plugin\PluginHelper;
    use Core\Plugin\SystemHelper;
    use Core\Plugin\ThemeHelper;

    class SupplierThemePlugin extends AbstractBasePlugin
    {
        // 使用 UUID 作为插件的唯一 ID
        protected static $pluginUniqueId = '48917E92-F17E-4CB2-989E-8B726E2515EB';

        /**
         * 允许哪些系统加载本插件
         */
        protected static $systemAllowLoad = array(PluginHelper::SYSTEM_MANAGE, PluginHelper::SYSTEM_SUPPLIER);

        public function pluginActivate($system)
        {
            // 设置 Supplier 系统为我们自己
            ThemeHelper::setSystemThemeDirName(ThemeHelper::SYSTEM_SUPPLIER_THEME, $this->getPluginDirName());
            return true;
        }

        public function pluginDeactivate($system)
        {
            ThemeHelper::setSystemThemeDirName(ThemeHelper::SYSTEM_SUPPLIER_THEME, null);
            return true;
        }

        public function pluginGetConfigureUrl($system)
        {
            // manage 系统可以配置这个插件
            if (PluginHelper::SYSTEM_MANAGE === $system) {
                //return RouteHelper::makeUrl('/Theme/Supplier/Configure');
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

            // 为 supplier 系统加载运行环境
            if (PluginHelper::SYSTEM_SUPPLIER === $system) {
                return $this->doSupplierAction();
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
            $currentThemeBasePath = dirname(__FILE__);

            return true;
        }

        /**
         *
         * 为 supplier 系统设置运行环境
         *
         * @return bool
         */
        private function doSupplierAction()
        {
            global $f3;
            global $smarty;

            // 获取当前插件的根地址
            $currentThemeBasePath = dirname(__FILE__);

            // supplier 目录加入到 auto load 的路径中，这样系统就能自动做 class 加载
            SystemHelper::addAutoloadPath($currentThemeBasePath . '/supplier/Code', true);

            // 设置路由，这样用户就能访问到我们的程序了
            $f3->config($currentThemeBasePath . '/supplier/route.cfg');

            // 注册 Asset 模块
            \Core\Asset\ManagerHelper::registerModule(
                SupplierThemePlugin::pluginGetUniqueId(),
                $this->pluginGetVersion(),
                $currentThemeBasePath . '/supplier/Asset'
            );

            // 发布必要的资源文件
            \Core\Asset\ManagerHelper::publishAsset(
                SupplierThemePlugin::pluginGetUniqueId(),
                'bootstrap-custom'
            );
            \Core\Asset\ManagerHelper::publishAsset(
                SupplierThemePlugin::pluginGetUniqueId(),
                'css'
            );
            \Core\Asset\ManagerHelper::publishAsset(
                SupplierThemePlugin::pluginGetUniqueId(),
                'img'
            );
            \Core\Asset\ManagerHelper::publishAsset(
                SupplierThemePlugin::pluginGetUniqueId(),
                'js'
            );

            // 增加 smarty 模板搜索路径
            $smarty->addTemplateDir($currentThemeBasePath . '/supplier/Tpl/');

            // 加载 smarty 的扩展，里面有一些我们需要用到的函数
            require_once($currentThemeBasePath . '/supplier/Code/smarty_helper.php');

            // 注册 smarty 函数
            smarty_helper_register($smarty);

            // 设置网站的 Base 路径，给 JavaScript 使用
            $smarty->assign("WEB_ROOT_HOST", $f3->get('sysConfig[webroot_schema_host]'));
            $smarty->assign("WEB_ROOT_BASE", $f3->get('BASE'));
            $smarty->assign(
                "WEB_ROOT_BASE_RES",
                smarty_helper_function_get_asset_url(array('asset' => ''), null)
            );

            return true;
        }

    }

}
