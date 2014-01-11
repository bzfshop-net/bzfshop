<?php

/**
 * @author QiangYu
 *
 * 初始化 管理后台
 *
 * */

use Core\Cloud\CloudHelper;
use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Plugin\PluginHelper;
use Core\Plugin\ThemeHelper;
use Core\Service\Order\Order as OrderBasicService;

define('MANAGE_PATH', dirname(__FILE__));
define('MANAGE_DIR', basename(MANAGE_PATH));

// 包含整个系统的初始化
require_once(MANAGE_PATH . '/../protected/bootstrap.php');

//预先加载一些常用模块，提高后面的加载效率
require_once(PROTECTED_PATH . '/Core/Helper/Utility/Auth.php');
require_once(PROTECTED_PATH . '/Core/Helper/Utility/Route.php');
require_once(PROTECTED_PATH . '/Core/Helper/Utility/Time.php');
require_once(PROTECTED_PATH . '/Core/Service/Order/Order.php');
require_once(PROTECTED_PATH . '/Core/Plugin/PluginHelper.php');
require_once(PROTECTED_PATH . '/Core/Plugin/ThemeHelper.php');
require_once(PROTECTED_PATH . '/Core/Log/File.php');
require_once(PROTECTED_PATH . '/Core/Asset/IManager.php');
require_once(PROTECTED_PATH . '/Core/Asset/ManagerHelper.php');
require_once(PROTECTED_PATH . '/Core/Cloud/CloudHelper.php');

// ---------------------------------------- 1. 设置系统运行设置 --------------------------------------

// 加载全局变量设置
$f3->config(PROTECTED_PATH . '/Config/manage.cfg');
// 根据环境变量的不同，加载对应的环境变量设置，开发环境和生产环境的配置显然是不一样的
$f3->config(PROTECTED_PATH . '/Config/manage-' . $f3->get('sysConfig[env]') . '.cfg');

// 设置工作时区
if ($f3->get('sysConfig[time_zone]')) {
    date_default_timezone_set($f3->get('sysConfig[time_zone]'));
}

// 设置网站唯一的 key，防止通用模块之间的冲突
RouteHelper::$uniqueKey           = 'MANAGE';
AuthHelper::$uniqueKey            = 'MANAGE';
AuthHelper::$enableCookieAuth     = true; // manage 用到了 swfupload 用于上传图片，所以必须开启 CookieAuth
OrderBasicService::$orderSnPrefix = 'MANAGE';

// ------------ 2. 初始化 云服务引擎，云服务引擎会设置好我们的运行环境，包括 可写目录 等 ------------

CloudHelper::initCloudEnv(PluginHelper::SYSTEM_MANAGE);

// ---------------------------------------- 3. 开启系统日志 --------------------------------------

// 设置系统的日志
$todayDateStr   = \Core\Helper\Utility\Time::localTimeStr('Y-m-d');
$todayDateArray = explode('-', $todayDateStr);

// 设置一个 fileLogger 方便查看所有的日志输出，按照 年/月/年-月-日.log 输出
$fileLogger =
    new \Core\Log\File(
        $todayDateArray[0] . '/' . $todayDateArray[1] . '/' . implode('-', $todayDateArray) . '.manage.log');
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

/* * **************** 如果是调试模式，在这里设置调试 ************************ */

if ($f3->get('DEBUG')) {

    // 调试模式，关闭缓存
    $f3->set('CACHE', false);

    // 调试模式下，弄一个 fileLogger 方便查看所有的日志输出
    $fileLogger = new \Core\Log\File(\Core\Helper\Utility\Time::localTimeStr('Y-m-d') . '.manage.debug.log');
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
}

// ---------------------------------------- 4. 加载显示主题 --------------------------------------

// 加载主题自己的初始化文件
$themeLoadFile = PROTECTED_PATH . '/Theme/' . $f3->get('sysConfig[theme]') . '/theme_load.php';
if (is_file($themeLoadFile)) {
    require_once($themeLoadFile);
} else {
    die('系统主题设置错误');
}

// ---------------------------------------- 5. 加载系统插件 --------------------------------------

// 这里我们加载额外的插件
PluginHelper::loadActivePlugin(PluginHelper::SYSTEM_MANAGE);
// 执行插件的 action 方法，让插件能完成各种注册
PluginHelper::doActivePluginAction(PluginHelper::SYSTEM_MANAGE);

// ---------------------------------------- 6. 把系统安装的主题当作插件一样加载上来，用于主题管理配置----------------

ThemeHelper::loadInstallTheme(PluginHelper::SYSTEM_MANAGE);
ThemeHelper::doInstallThemeAction(PluginHelper::SYSTEM_MANAGE);

// ---------------------------------------- 7. 启动整个系统 --------------------------------------

// 启动控制器
$f3->run();

// unload 系统安装的主题
ThemeHelper::unloadInstallTheme(PluginHelper::SYSTEM_MANAGE);
// 执行完成，unload 插件
PluginHelper::unloadActivePlugin(PluginHelper::SYSTEM_MANAGE);
