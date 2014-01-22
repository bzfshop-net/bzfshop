<?php

/**
 * @author QiangYu
 *
 * 棒主妇商城
 *
 * */

use Core\Cloud\CloudHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Plugin\PluginHelper;

if (!defined('BZF_PHP_VERSION_REQUIRE')) {
    die('illegal call, please call index.php');
}

define('INSTALL_PATH', dirname(__FILE__));
define('INSTALL_DIR', basename(INSTALL_PATH));

// 包含整个系统的初始化
require_once(INSTALL_PATH . '/../protected/bootstrap.php');

// ---------------------------------------- 1. 设置系统运行环境 --------------------------------------

// 设置工作时区
if ($f3->get('sysConfig[time_zone]')) {
    date_default_timezone_set($f3->get('sysConfig[time_zone]'));
}

// 设置网站唯一的 key，防止通用模块之间的冲突
RouteHelper::$uniqueKey = 'BZFRouteHelper';

// ------------ 2. 初始化 云服务引擎，云服务引擎会设置好我们的运行环境，包括 可写目录 等 ------------

CloudHelper::initCloudEnv(PluginHelper::SYSTEM_INSTALL);

// RUNTIME_PATH 必须要有读写权限
@file_put_contents(RUNTIME_PATH . '/install.write', 'install.write');
if ('install.write' != @file_get_contents(RUNTIME_PATH . '/install.write')) {
    die('错误：[' . RUNTIME_PATH . ']必须有读写权限');
}
unlink(RUNTIME_PATH . '/install.write');

// ---------------------------------------- 3. 开启系统日志 --------------------------------------

// 设置一个 fileLogger 方便查看所有的日志输出
$fileLogger = new \Core\Log\File('install.log');
$logger->addLogger($fileLogger);
unset($fileLogger);

/* * **************** 如果是调试模式，在这里设置调试 ************************ */

if ($f3->get('DEBUG')) {

    // 调试模式，关闭缓存
    $f3->set('CACHE', false);

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

// ---------------------------------------- 4. 设置工程环境 --------------------------------------

// 设置代码路径
\Core\Plugin\SystemHelper::addAutoloadPath(INSTALL_PATH . '/Code', true);

// 设置路由，这样用户就能访问到我们的程序了
$f3->config(INSTALL_PATH . '/route.cfg');

// 增加 smarty 模板搜索路径
$smarty->addTemplateDir(INSTALL_PATH . '/Tpl/');

// 加载 smarty 的扩展，里面有一些我们需要用到的函数
require_once(INSTALL_PATH . '/Code/smarty_helper.php');

// 注册 smarty 函数
smarty_helper_register($smarty);

// ---------------------------------------- 5. 为 JavaScript 设置变量 --------------------------------------

// 设置网站的 Base 路径，给 JavaScript 使用
$smarty->assign("WEB_ROOT_HOST", $f3->get('sysConfig[webroot_schema_host]'));
$smarty->assign("WEB_ROOT_BASE", $f3->get('BASE'));
$smarty->assign(
    "WEB_ROOT_BASE_RES",
    $f3->get('BASE') . '/Asset/'
);

// ---------------------------------------- 6. 启动程序 --------------------------------------

// 启动控制器
$f3->run();
