<?php

/**
 * @author QiangYu
 *
 * 插件加载文件，用于初始化插件同时返回一个插件的 instance 对象
 *
 * */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Plugin\Thirdpart\YiqifaCps {

    use Core\Helper\Utility\Route as RouteHelper;
    use Core\OrderRefer\ReferHelper;
    use Core\Plugin\AbstractBasePlugin;
    use Core\Plugin\PluginHelper;
    use Core\Plugin\SystemHelper;

    class YiqifaCpsPlugin extends AbstractBasePlugin
    {
        // 使用 UUID 作为插件的唯一 ID
        protected static $pluginUniqueId = '16B22EA1-3C1E-4C1E-B34F-77FEA8153DBD';

        // 允许哪些系统加载本插件
        protected static $systemAllowLoad = array(
            PluginHelper::SYSTEM_MANAGE,
            PluginHelper::SYSTEM_GROUPON,
            PluginHelper::SYSTEM_MOBILE,
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
                return RouteHelper::makeUrl('/Thirdpart/YiqifaCps/Configure');
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

            // 为 groupon 系统加载运行环境
            if (PluginHelper::SYSTEM_GROUPON === $system
                || PluginHelper::SYSTEM_SHOP === $system
            ) {
                return $this->doGrouponAction();
            }

            // 为 mobile 系统加载运行环境
            if (PluginHelper::SYSTEM_MOBILE === $system) {
                return $this->doMobileAction();
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
                '/Thirdpart/YiqifaCps/Configure',
                'Controller\Thirdpart\YiqifaCps\Configure'
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
        private function doYiqifaCpsAction()
        {
            // 获取当前插件的根地址
            $currentPluginBasePath = dirname(__FILE__);

            // yiqifacps code 目录加入到 auto load 的路径中，这样系统就能自动做 class 加载
            SystemHelper::addAutoloadPath($currentPluginBasePath . '/yiqifacps/Code');

            // 设置 CPS 日志，按照日期分目录存储
            $todayDateStr              = \Core\Helper\Utility\Time::localTimeStr('Y-m-d');
            $todayDateArray            = explode('-', $todayDateStr);
            $fileLogger                = new \Core\Log\File('YIQIFACPS/' . $todayDateArray[0] . '/' .
            $todayDateArray[1] . '/' . implode('-', $todayDateArray) . '.yiqifacps.log');
            $fileLogger->sourceAllow[] = 'YIQIFACPS'; // 只接收 YIQIFACPS 的日志
            global $logger;
            $logger->addLogger($fileLogger); // 把 $fileLogger 放到全局日志列表中

            // 设置路由，这样用户就能访问到我们的程序了
            SystemHelper::addRouteMap(
                '/Thirdpart/YiqifaCps/Redirect',
                'Controller\Thirdpart\YiqifaCps\Redirect'
            );
            SystemHelper::addRouteMap(
                '/Thirdpart/YiqifaCps/QueryOrder',
                'Controller\Thirdpart\YiqifaCps\QueryOrder'
            );
            SystemHelper::addRouteMap(
                '/Thirdpart/YiqifaCps/CaibeiLogin',
                'Controller\Thirdpart\YiqifaCps\CaibeiLogin'
            );

            // 设置一个 OrderRefer 用于记录订单来自于 亿起发CPS
            require_once($currentPluginBasePath . '/yiqifacps/Code/YiqifaCpsRefer.php');
            // 设置订单 refer 用于记录订单来源于 亿起发CPS
            ReferHelper::addReferItem('YiqifaCpsRefer', new YiqifaCpsRefer());

            // 增加 smarty 模板搜索路径
            global $smarty;
            $smarty->addTemplateDir($currentPluginBasePath . '/yiqifacps/Tpl/');

            return true;
        }

        /**
         * 为 Groupon 系统设置运行环境
         *
         * @return bool
         */
        private function doGrouponAction()
        {
            // 获取当前插件的根地址
            $currentPluginBasePath = dirname(__FILE__);

            // 设置针对当前系统的查询条件

            require_once($currentPluginBasePath . '/yiqifacps/Code/YiqifaCpsRefer.php');
            YiqifaCpsRefer::$notifyParamDt = 'web';
            YiqifaCpsRefer::$cpsRateKey    = 'yiqifacps_rate_web';

            require_once($currentPluginBasePath . '/yiqifacps/Code/Controller/Thirdpart/YiqifaCps/QueryOrder.php');
            \Controller\Thirdpart\YiqifaCps\QueryOrder::$extraQueryCond =
                array(
                    ' oi.system_id = ? or oi.system_id = ? ',
                    PluginHelper::SYSTEM_GROUPON,
                    PluginHelper::SYSTEM_SHOP
                );

            return $this->doYiqifaCpsAction();
        }

        /**
         * 为 Mobile 系统设置运行环境
         *
         * @return bool
         */
        private function doMobileAction()
        {
            // 获取当前插件的根地址
            $currentPluginBasePath = dirname(__FILE__);

            // 设置针对当前系统的查询条件

            require_once($currentPluginBasePath . '/yiqifacps/Code/YiqifaCpsRefer.php');
            YiqifaCpsRefer::$notifyParamDt = 'm';
            YiqifaCpsRefer::$cpsRateKey    = 'yiqifacps_rate_mobile';

            require_once($currentPluginBasePath . '/yiqifacps/Code/Controller/Thirdpart/YiqifaCps/QueryOrder.php');
            \Controller\Thirdpart\YiqifaCps\QueryOrder::$extraQueryCond =
                array('oi.system_id = ?', PluginHelper::SYSTEM_MOBILE);

            return $this->doYiqifaCpsAction();
        }

    }

}

// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 plugin instance
    return Plugin\Thirdpart\YiqifaCps\YiqifaCpsPlugin::instance();
}