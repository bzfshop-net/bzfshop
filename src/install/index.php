<?php

/**
 * @author QiangYu
 *
 * 棒主妇商城
 *
 * */

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\OrderRefer\ReferHelper;
use Core\Plugin\PluginHelper;
use Core\Plugin\ThemeHelper;
use Core\Service\Cart\Cart as CartBasicService;
use Core\Service\Order\Order as OrderBasicService;
use Theme\Manage\ManageThemePlugin;

define('INSTALL_PATH', dirname(__FILE__));
define('INSTALL_DIR', basename(INSTALL_PATH));

// 包含整个系统的初始化
require_once(INSTALL_PATH . '/../protected/bootstrap.php');

// ---------------------------------------- 1. 设置系统运行环境 --------------------------------------

// 设置工作时区
if ($f3->get('sysConfig[time_zone]')) {
    date_default_timezone_set($f3->get('sysConfig[time_zone]'));
}

// 当前网站的 webroot_url_prefix
if (!$f3->get('sysConfig[webroot_url_prefix]')) {
    $f3->set(
        'sysConfig[webroot_url_prefix]',
        $f3->get('SCHEME') . '://' . $f3->get('HOST')
        . (('80' != $f3->get('PORT')) ? ':' . $f3->get('PORT') : '')
        . $f3->get('BASE')
    );
}

// RunTime 路径
if (!$f3->get('sysConfig[runtime_path]')) {
    $f3->set('sysConfig[runtime_path]', realpath(PROTECTED_PATH . '/Runtime'));
}

define('RUNTIME_PATH', $f3->get('sysConfig[runtime_path]'));

// RUNTIME_PATH 必须要有读写权限

if(!is_writable(RUNTIME_PATH) || !is_readable(RUNTIME_PATH)){
    die('错误：['.RUNTIME_PATH.']必须有读写权限');
}

// 设置 Tmp 路径
$f3->set('TEMP', RUNTIME_PATH . '/Temp/');

// 设置 Log 路径
$f3->set('LOGS', RUNTIME_PATH . '/Log/Install/');

// 设置网站唯一的 key，防止通用模块之间的冲突
RouteHelper::$uniqueKey = 'BZFRouteHelper';

// 初始化 smarty 模板引擎
$smarty->debugging     = $f3->get('sysConfig[smarty_debug]');
$smarty->force_compile = $f3->get('sysConfig[smarty_force_compile]');
$smarty->use_sub_dirs  = $f3->get('sysConfig[smarty_use_sub_dirs]');

//设置 smarty 工作目录
$smarty->setCompileDir(RUNTIME_PATH . '/Smarty/Install/Compile');
$smarty->setCacheDir(RUNTIME_PATH . '/Smarty/Install/Cache');

// ---------------------------------------- 2. 开启系统日志 --------------------------------------

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

// ---------------------------------------- 3. 设置工程环境 --------------------------------------

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

// ---------------------------------------- 4. 为 JavaScript 设置变量 --------------------------------------

// 设置网站的 Base 路径，给 JavaScript 使用
$smarty->assign(
    "WEB_ROOT_HOST",
    $f3->get('SCHEME') . '://' . $f3->get('HOST') . (('80' != $f3->get('PORT')) ? ':' . $f3->get('PORT')
        : '')
);
$smarty->assign("WEB_ROOT_BASE", $f3->get('BASE'));
$smarty->assign(
    "WEB_ROOT_BASE_RES",
    $f3->get('BASE').'/Asset/'
);

// ---------------------------------------- 5. 启动程序 --------------------------------------

// 启动控制器
$f3->run();
