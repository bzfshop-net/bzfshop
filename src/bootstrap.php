<?php

/**
 * @author QiangYu
 *
 * 棒主妇商城
 *
 * */

use Core\Cloud\CloudHelper;
use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\OrderRefer\ReferHelper;
use Core\Plugin\PluginHelper;
use Core\Plugin\ThemeHelper;
use Core\Service\Cart\Cart as CartBasicService;
use Core\Service\Order\Order as OrderBasicService;
use Theme\Manage\ManageThemePlugin;

if (!defined('XIAOBAI_INDEX_PATH')) {
    die('illegal call, please call index.php');
}

define('SHOP_PATH', dirname(__FILE__));
//define('SHOP_DIR', basename(SHOP_PATH));
define('SHOP_DIR', '');

// 包含整个系统的初始化
//require_once(SHOP_PATH . '/../protected/bootstrap.php');
require_once(SHOP_PATH . '/protected/bootstrap.php');

//预先加载一些常用模块，提高后面的加载效率
require_once(PROTECTED_PATH . '/Core/Helper/Utility/Auth.php');
require_once(PROTECTED_PATH . '/Core/Helper/Utility/Route.php');
require_once(PROTECTED_PATH . '/Core/Helper/Utility/Time.php');
require_once(PROTECTED_PATH . '/Core/OrderRefer/ReferHelper.php');
require_once(PROTECTED_PATH . '/Core/Service/Cart/Cart.php');
require_once(PROTECTED_PATH . '/Core/Service/Order/Order.php');
require_once(PROTECTED_PATH . '/Core/Plugin/PluginHelper.php');
require_once(PROTECTED_PATH . '/Core/Plugin/ThemeHelper.php');
require_once(PROTECTED_PATH . '/Core/Log/File.php');
require_once(PROTECTED_PATH . '/Core/Asset/IManager.php');
require_once(PROTECTED_PATH . '/Core/Asset/ManagerHelper.php');
require_once(PROTECTED_PATH . '/Core/Cloud/CloudHelper.php');

// ---------------------------------------- 1. 设置系统运行环境 --------------------------------------

// 加载全局变量设置
$f3->config(PROTECTED_PATH . '/Config/shop.cfg');
// 根据环境变量的不同，加载对应的环境变量设置，开发环境和生产环境的配置显然是不一样的
$f3->config(PROTECTED_PATH . '/Config/shop-' . $f3->get('sysConfig[env]') . '.cfg');

// 设置工作时区
if ($f3->get('sysConfig[time_zone]')) {
    date_default_timezone_set($f3->get('sysConfig[time_zone]'));
}

// 设置 session 在多个子域名之间共享
if ($f3->get('sysConfig[cookie_domain]')) {
    $f3->set('JAR.domain', $f3->get('sysConfig[cookie_domain]'));
}

// 设置网站唯一的 key，防止通用模块之间的冲突
RouteHelper::$uniqueKey = 'BZFRouteHelper';

OrderBasicService::$orderSnPrefix  = 'SB';
ReferHelper::$orderReferStorageKey = 'BZFOrderRefer';

// 记录系统订单来源
CartBasicService::$cartSystemId = PluginHelper::SYSTEM_SHOP;

// 把几个网站的 key 设置成一样，配合 sysConfig[cookie_domain] 设置，就可以实现几个网站 统一登陆
AuthHelper::$uniqueKey = 'BZFAUTH';

// ------------ 2. 初始化 云服务引擎，云服务引擎会设置好我们的运行环境，包括 可写目录 等 ------------

CloudHelper::initCloudEnv(PluginHelper::SYSTEM_SHOP);

// ---------------------------------------- 3. 开启系统日志 --------------------------------------

$todayDateStr   = \Core\Helper\Utility\Time::localTimeStr('Y-m-d');
$todayDateArray = explode('-', $todayDateStr);

// 设置一个 fileLogger 方便查看所有的日志输出，按照 年/月/年-月-日.log 输出
$fileLogger =
    new \Core\Log\File(
        $todayDateArray[0] . '/' . $todayDateArray[1] . '/' . implode('-', $todayDateArray)
        . '.shop.log');
// 我们不打印 DEBUG 级别的日志，不然数据量太大了
$fileLogger->levelAllow = array(
    \Core\Log\Base::CRITICAL,
    \Core\Log\Base::ERROR,
    \Core\Log\Base::WARN,
    \Core\Log\Base::NOTICE,
    \Core\Log\Base::INFO,
);
$logger->addLogger($fileLogger);
unset($fileLogger);

// 设置支付日志，我们需要把所有的支付都记录到支付日志中方便以后查询，按照日期分目录存储
$fileLogger                = new \Core\Log\File('PAYMENT/' . $todayDateArray[0] . '/' .
    $todayDateArray[1] . '/' . implode('-', $todayDateArray) . '.payment.log');
$fileLogger->sourceAllow[] = 'PAYMENT'; // 只接收支付日志
$logger->addLogger($fileLogger); // 把 $fileLogger 放到全局日志列表中
unset($fileLogger);


/* * **************** 如果是调试模式，在这里设置调试 ************************ */

if ($f3->get('DEBUG')) {

    // 调试模式，关闭缓存
    $f3->set('CACHE', false);

    // 调试模式下，弄一个 fileLogger 方便查看所有的日志输出
    $fileLogger = new \Core\Log\File(\Core\Helper\Utility\Time::localTimeStr('Y-m-d') . '.shop.debug.log');
    $logger->addLogger($fileLogger);

    // 把 smarty 的一些错误警告关闭，不然会影响我们的调试
    Smarty::muteExpectedErrors();

    // 使用自定义的调试框架
    if ($f3->get('USERDEBUG')) {
        require_once(PROTECTED_PATH . '/Framework/Debug/BzfDebug.php');
        // 开启 debug 功能
        BzfDebug::enableDebug();
        // 开启 Smarty Web Log
        BzfDebug::enableSmartyWebLog();
    }

} else {
    /*** 错误处理，如果网站出现错误，我简单的把用户定位到 首页 ***/
    $f3->set(
        'ONERROR',
        function ($f3) {

            /**
             * Information about the last HTTP error that occurred.
             * ERROR.code is the HTTP status code.
             * ERROR.title contains a brief description of the error.
             * ERROR.text provides greater detail. For HTTP 500 errors, use ERROR.trace to retrieve the stack trace.
             */

            $code = $f3->get('ERROR.code');
            if (404 == intval($code)) {
                RouteHelper::reRoute(null, '/Error/E404?url=' . urlencode(RouteHelper::getFullURL()));
            } else {
                RouteHelper::reRoute(null, '/');
            }
        }
    );
}

// ---------------------------------------- 4. 加载显示主题 -----------------------------------

// 为 Manage 设置网站的 WebRootBase，这样在 Manage 中就可以对相应网站做操作
$systemUrlBase = ManageThemePlugin::getSystemUrlBase(PluginHelper::SYSTEM_SHOP);
if (empty($systemUrlBase) || $systemUrlBase['base'] != $f3->get('sysConfig[webroot_url_prefix]')) {
    ManageThemePlugin::saveSystemUrlBase(
        PluginHelper::SYSTEM_SHOP,
        '商城',
        '棒主妇商城',
        $f3->get('sysConfig[webroot_url_prefix]')
    );
}

$themeIntance = ThemeHelper::loadSystemTheme(ThemeHelper::SYSTEM_SHOP_THEME);
if (!$themeIntance) {
    die('没有正确设置 ' . ThemeHelper::SYSTEM_SHOP_THEME . ' 主题');
}

// 调用主题自己的初始化方法
$themeLoadRet = $themeIntance->pluginLoad(PluginHelper::SYSTEM_SHOP);
if (true !== $themeLoadRet) {
    die(ThemeHelper::SYSTEM_SHOP_THEME . ' 主题无法初始化:' . $themeLoadRet);
}
// 调用主题的 action
$themeActionRet = $themeIntance->pluginAction(PluginHelper::SYSTEM_SHOP);
if (true !== $themeActionRet) {
    die(ThemeHelper::SYSTEM_SHOP_THEME . ' 主题无法加载:' . $themeActionRet);
}


// ---------------------------------------- 5. 加载系统插件 --------------------------------------

// 在这里启动插件体系架构

// 这里我们加载额外的插件
PluginHelper::loadActivePlugin(PluginHelper::SYSTEM_SHOP);
// 执行插件的 action 方法，让插件能完成各种注册
PluginHelper::doActivePluginAction(PluginHelper::SYSTEM_SHOP);


// ----------------------------- 6. 把系统安装的主题当作插件一样加载上来，用于不同主题之间互相合作----------------

ThemeHelper::loadActiveTheme(
    PluginHelper::SYSTEM_SHOP,
    ThemeHelper::getSystemThemeDirName(ThemeHelper::SYSTEM_SHOP_THEME)
);
ThemeHelper::doActiveThemeAction(PluginHelper::SYSTEM_SHOP);

// ---------------------------------------- 7. 启动整个系统 --------------------------------------

// 是否开启 URL 伪静态化
if ($f3->get('sysConfig[enable_static_url][' . PluginHelper::SYSTEM_SHOP . ']')) {
    RouteHelper::$isMakeStaticUrl = true; // 我们开启 URL 伪静态化
    RouteHelper::processStaticUrl(); // 解析静态化的 URL
}

// 启动控制器
$f3->run();

// unload 系统 active 的主题插件
ThemeHelper::unloadActiveTheme(PluginHelper::SYSTEM_SHOP);
// 执行完成，卸载插件
PluginHelper::unloadActivePlugin(PluginHelper::SYSTEM_SHOP);
// 执行完成，卸载主题
$themeIntance->pluginUnload(PluginHelper::SYSTEM_SHOP);
