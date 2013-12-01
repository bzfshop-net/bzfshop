<?php

/**
 * @author QiangYu
 *
 * 初始化 Console 工程
 *
 * */

define('CONSOLE_PATH', dirname(__FILE__));

// 包含整个系统的初始化
require_once(CONSOLE_PATH . '/../protected/bootstrap-console.php');

// 增加当前项目的 autoload 路径
$f3->set('AUTOLOAD', CONSOLE_PATH . '/;' . $f3->get('AUTOLOAD'));

// 加载全局变量设置
$f3->config(PROTECTED_PATH . '/Config/console.cfg');
// 根据环境变量的不同，加载对应的环境变量设置，开发环境和生产环境的配置显然是不一样的
$f3->config(PROTECTED_PATH . '/Config/console-' . $f3->get('sysConfig[env]') . '.cfg');

// 设置工作时区
if ($f3->get('sysConfig[time_zone]')) {
    date_default_timezone_set($f3->get('sysConfig[time_zone]'));
}

//数据路径
if (!$f3->get('sysConfig[data_path_root]')) {
    $f3->set('sysConfig[data_path_root]', realpath(CONSOLE_PATH . '/../data'));
}

// RunTime 路径
if (!$f3->get('sysConfig[runtime_path]')) {
    $f3->set('sysConfig[runtime_path]', realpath(PROTECTED_PATH . '/Runtime'));
}

define('RUNTIME_PATH', $f3->get('sysConfig[runtime_path]'));

// 设置 Tmp 路径
$f3->set('TEMP', RUNTIME_PATH . '/Temp/');
// 设置 Log 路径
$f3->set('LOGS', RUNTIME_PATH . '/Log/Console/');
//开启 Cache 功能
if (!$f3->get('CACHE')) {
    // 让 F3 自动选择使用最优的 Cache 方案，最差的情况会使用 TEMP/cache 目录文件做缓存
    $f3->set('CACHE', 'true');
}

//预先加载一些常用模块，提高后面的加载效率
require_once(PROTECTED_PATH . '/Core/Log/File.php');
require_once(PROTECTED_PATH . '/Core/Log/Console.php');

$todayDateStr   = \Core\Helper\Utility\Time::localTimeStr('Y-m-d');
$todayDateArray = explode('-', $todayDateStr);

// 设置一个 fileLogger 方便查看所有的日志输出，按照 年/月/年-月-日.log 输出
$fileLogger =
    new \Core\Log\File(
        $todayDateArray[0] . '/' . $todayDateArray[1] . '/' . implode('-', $todayDateArray) . '.console.log');
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

// 设置一个 consoleLogger，这样我们可以同步在控制台看到输出结果
$consoleLogger             = new \Core\Log\Console();
$consoleLogger->levelAllow = array(
    \Core\Log\Base::CRITICAL,
    \Core\Log\Base::ERROR,
    \Core\Log\Base::WARN,
    \Core\Log\Base::NOTICE,
    \Core\Log\Base::INFO,
);
$logger->addLogger($consoleLogger);
//unset($consoleLogger);

/* * **************** 如果是调试模式，在这里设置调试 ************************ */
if ($f3->get('DEBUG')) {
    // debug 模式不使用缓存
    $f3->set('CACHE', false);

    // 调试模式下 console 显示 DEBUG 信息
    $consoleLogger->levelAllow[] = \Core\Log\Base::DEBUG;

    // 调试模式下，弄一个 fileLogger 方便查看所有的日志输出
    $fileLogger = new \Core\Log\File(\Core\Helper\Utility\Time::localTimeStr('Y-m-d') . '.console.debug.log');
    $logger->addLogger($fileLogger);
}
