<?php

/**
 * @author QiangYu
 *
 *
 * */


// 定义自己的 namespace ，防止和别的插件冲突
namespace Theme\Shop {

    use Core\Cache\ClearHelper;
    use Core\Helper\Utility\Route as RouteHelper;
    use Core\OrderRefer\HostRefer;
    use Core\OrderRefer\ReferHelper;
    use Core\Plugin\AbstractBasePlugin;
    use Core\Plugin\PluginHelper;
    use Core\Plugin\SystemHelper;
    use Core\Plugin\ThemeHelper;
    use Theme\Manage\ManageThemePlugin;

    class ShopThemePlugin extends AbstractBasePlugin
    {
        // 商品过滤
        protected $goodsFilterSystemArray = array(PluginHelper::SYSTEM_SHOP);
        // 订单过滤
        protected $orderFilterSystemArray = array(
            PluginHelper::SYSTEM_GROUPON,
            PluginHelper::SYSTEM_SHOP,
            PluginHelper::SYSTEM_MOBILE
        );

        // 使用 UUID 作为插件的唯一 ID
        protected static $pluginUniqueId = 'BD0940B4-4B18-4205-9049-BEF1BC8E9451';

        /**
         * 允许哪些系统加载本插件
         */
        protected static $systemAllowLoad = array(PluginHelper::SYSTEM_ALL);

        public function pluginActivate($system)
        {
            // 设置 商城 系统为我们自己
            ThemeHelper::setSystemThemeDirName(ThemeHelper::SYSTEM_SHOP_THEME, $this->getPluginDirName());
            return true;
        }

        public function pluginDeactivate($system)
        {
            ThemeHelper::setSystemThemeDirName(ThemeHelper::SYSTEM_SHOP_THEME, null);

            // 删除在 Manage 系统中的设置
            ManageThemePlugin::removeSystemUrlBase(PluginHelper::SYSTEM_SHOP);

            return true;
        }

        public function pluginGetConfigureUrl($system)
        {
            // manage 系统可以配置这个插件
            if (PluginHelper::SYSTEM_MANAGE === $system) {
                return RouteHelper::makeUrl('/Theme/Shop/Index');
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

            // 为 Shop 系统加载运行环境
            if (PluginHelper::SYSTEM_SHOP === $system) {
                return $this->doShopAction();
            }

            return $this->doOtherAction();
        }

        /**
         * 其它系统加载本主题
         *
         * @return bool
         */
        private function doOtherAction()
        {
            // 获取当前插件的根地址
            $currentThemeBasePath = dirname(__FILE__);
            require_once($currentThemeBasePath . '/manage/Code/Cache/ShopClear.php');
            // 注册 Smarty 缓存清除功能，用户在 Manage 后台编辑商品的时候就能智能清除缓存了
            ClearHelper::registerInstanceClass('\Cache\ShopClear');
            return true;
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

            // 通用的加载
            $this->doOtherAction();

            // manage 目录加入到 auto load 的路径中，这样系统就能自动做 class 加载
            SystemHelper::addAutoloadPath($currentThemeBasePath . '/manage/Code');

            // 设置路由，这样用户就能访问到我们的程序了
            SystemHelper::addRouteMap(
                '/Theme/Shop/@action',
                'Controller\Theme\Shop\@action'
            );

            // 注册 Asset 模块
            \Core\Asset\ManagerHelper::registerModule(
                ShopThemePlugin::pluginGetUniqueId(),
                $this->pluginGetVersion(),
                $currentThemeBasePath . '/manage/Asset'
            );

            // 增加 smarty 模板搜索路径
            global $smarty;
            $smarty->addTemplateDir($currentThemeBasePath . '/manage/Tpl/');

            return true;
        }

        /**
         *
         * 为 shop 系统设置运行环境
         *
         * @return bool
         */
        private function doShopAction()
        {
            global $f3;
            global $smarty;

            // 获取当前插件的根地址
            $currentThemeBasePath = dirname(__FILE__);

            // 通用的加载
            $this->doOtherAction();

            // shop Code 目录加入到 auto load 的路径中，这样系统就能自动做 class 加载
            SystemHelper::addAutoloadPath($currentThemeBasePath . '/shop/Code', true);

            // 设置路由，这样用户就能访问到我们的程序了
            $f3->config($currentThemeBasePath . '/shop/route.cfg');
            $f3->config($currentThemeBasePath . '/shop/route-rewrite.cfg');

            ReferHelper::addReferItem('HostRefer', new HostRefer()); // 记录来源的 refer_host 和 refer_url

            // 注册 Asset 模块
            \Core\Asset\ManagerHelper::registerModule(
                ShopThemePlugin::pluginGetUniqueId(),
                $this->pluginGetVersion(),
                $currentThemeBasePath . '/shop/Asset'
            );

            // 发布必要的资源文件
            \Core\Asset\ManagerHelper::publishAsset(
                ShopThemePlugin::pluginGetUniqueId(),
                'bootstrap-custom'
            );
            \Core\Asset\ManagerHelper::publishAsset(
                ShopThemePlugin::pluginGetUniqueId(),
                'css'
            );
            \Core\Asset\ManagerHelper::publishAsset(
                ShopThemePlugin::pluginGetUniqueId(),
                'js'
            );
            \Core\Asset\ManagerHelper::publishAsset(
                ShopThemePlugin::pluginGetUniqueId(),
                'img'
            );

            // 增加 smarty 模板搜索路径
            $smarty->addTemplateDir($currentThemeBasePath . '/shop/Tpl/');

            // 加载 smarty 的扩展，里面有一些我们需要用到的函数
            require_once($currentThemeBasePath . '/shop/Code/smarty_helper.php');

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
