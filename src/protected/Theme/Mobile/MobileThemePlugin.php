<?php

/**
 * @author QiangYu
 *
 * */


// 定义自己的 namespace ，防止和别的插件冲突
namespace Theme\Mobile {

    use Core\Cache\ClearHelper;
    use Core\Helper\Utility\Route as RouteHelper;
    use Core\OrderRefer\HostRefer;
    use Core\OrderRefer\ReferHelper;
    use Core\Plugin\AbstractBasePlugin;
    use Core\Plugin\PluginHelper;
    use Core\Plugin\SystemHelper;
    use Core\Plugin\ThemeHelper;
    use Theme\Manage\ManageThemePlugin;

    class MobileThemePlugin extends AbstractBasePlugin
    {
        // 商品过滤
        protected $goodsFilterSystemArray = array(PluginHelper::SYSTEM_MOBILE);
        // 订单过滤
        protected $orderFilterSystemArray = array(
            PluginHelper::SYSTEM_MOBILE
        );

        // 使用 UUID 作为插件的唯一 ID
        protected static $pluginUniqueId = 'B3FB932E-BEC0-49CE-AA74-95CE5AB2CF3D';

        /**
         * 允许哪些系统加载本插件
         */
        protected static $systemAllowLoad = array(PluginHelper::SYSTEM_ALL);

        public function pluginActivate($system)
        {
            // 设置 Mobile 系统为我们自己
            ThemeHelper::setSystemThemeDirName(ThemeHelper::SYSTEM_MOBILE_THEME, $this->getPluginDirName());
            return true;
        }

        public function pluginDeactivate($system)
        {
            ThemeHelper::setSystemThemeDirName(ThemeHelper::SYSTEM_MOBILE_THEME, null);

            // 删除在 Manage 系统中的设置
            ManageThemePlugin::removeSystemUrlBase(PluginHelper::SYSTEM_MOBILE);

            return true;
        }

        public function pluginGetConfigureUrl($system)
        {
            // manage 系统可以配置这个插件
            if (PluginHelper::SYSTEM_MANAGE === $system) {
                return RouteHelper::makeUrl('/Theme/Mobile/Configure');
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

            // 为 mobile 系统加载运行环境
            if (PluginHelper::SYSTEM_MOBILE === $system) {
                return $this->doMobileAction();
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
            require_once($currentThemeBasePath . '/manage/Code/Cache/MobileClear.php');
            // 注册 Smarty 缓存清除功能，用户在 Manage 后台编辑商品的时候就能智能清除缓存了
            ClearHelper::registerInstanceClass('\Cache\MobileClear');
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
                '/Theme/Mobile/@action',
                'Controller\Theme\Mobile\@action'
            );

            // 增加 smarty 模板搜索路径
            global $smarty;
            $smarty->addTemplateDir($currentThemeBasePath . '/manage/Tpl/');

            return true;
        }

        /**
         *
         * 为 mobile 系统设置运行环境
         *
         * @return bool
         */
        private function doMobileAction()
        {
            global $f3;

            // 获取当前插件的根地址
            $currentThemeBasePath = dirname(__FILE__);

            // 通用的加载
            $this->doOtherAction();

            // mobile 目录加入到 auto load 的路径中，这样系统就能自动做 class 加载
            SystemHelper::addAutoloadPath($currentThemeBasePath . '/mobile/Code', true);

            // 设置路由，这样用户就能访问到我们的程序了
            $f3->config($currentThemeBasePath . '/mobile/route.cfg');
            $f3->config($currentThemeBasePath . '/mobile/route-rewrite.cfg');

            // 记录用户从什么来源到达网站的
            ReferHelper::addReferItem('HostRefer', new HostRefer()); // 记录来源的 refer_host 和 refer_url

            // 注册 Asset 模块
            \Core\Asset\ManagerHelper::registerModule(
                MobileThemePlugin::pluginGetUniqueId(),
                $this->pluginGetVersion(),
                $currentThemeBasePath . '/mobile/Asset'
            );

            // 发布必要的资源文件
            \Core\Asset\ManagerHelper::publishAsset(
                MobileThemePlugin::pluginGetUniqueId(),
                'jquery-mobile'
            );

            \Core\Asset\ManagerHelper::publishAsset(
                MobileThemePlugin::pluginGetUniqueId(),
                'css'
            );

            \Core\Asset\ManagerHelper::publishAsset(
                MobileThemePlugin::pluginGetUniqueId(),
                'js'
            );

            \Core\Asset\ManagerHelper::publishAsset(
                MobileThemePlugin::pluginGetUniqueId(),
                'img'
            );

            // 增加 smarty 模板搜索路径
            global $smarty;
            $smarty->addTemplateDir($currentThemeBasePath . '/mobile/Tpl/');

            // 加载 smarty 的扩展，里面有一些我们需要用到的函数
            require_once($currentThemeBasePath . '/mobile/Code/smarty_helper.php');

            // 注册 smarty 函数
            smarty_helper_register($smarty);

            global $f3;
            // 设置网站的 Base 路径，给 JavaScript 使用
            $smarty->assign("WEB_ROOT_HOST", $f3->get('sysConfig[webroot_schema_host]'));
            $smarty->assign("WEB_ROOT_BASE", $f3->get('BASE'));
            $smarty->assign(
                "WEB_ROOT_BASE_RES",
                smarty_helper_function_get_asset_url(array('asset' => ''), null)
            );
            $smarty->assign(
                "IS_USER_AUTH",
                \Core\Helper\Utility\Auth::isAuthUser()
            );

            // jQuery Mobile 根据当前页面的 URL 来决定是否缓存，我们对某些不希望缓存的页面在这里需要特殊处理，
            // 确保它的 url 是一直变化的
            $currentPageUrl = \Core\Helper\Utility\Route::getRequestURL();

            //  下面的操作页面不要缓存，因为每次可能都不一样，比如有验证码，或者有新订单
            if (false !== strpos($currentPageUrl, '/User/')
                || false !== strpos($currentPageUrl, '/My/')
                || false !== strpos($currentPageUrl, '/Cart/')
            ) {
                $currentPageUrl =
                    \Core\Helper\Utility\Route::addParam($currentPageUrl, array('_no_cache_page' => time()));
            }
            $smarty->assign("CURRENT_PAGE_URL", $currentPageUrl);

            return true;
        }

    }

}
