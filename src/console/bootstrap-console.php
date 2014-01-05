<?php

/**
 * @author QiangYu
 *
 * 初始化 Console 工程
 *
 * */

use Core\Cloud\CloudHelper;
use Core\Plugin\PluginHelper;

define('CONSOLE_PATH', dirname(__FILE__));
define('CONSOLE_DIR', basename(CONSOLE_PATH));

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

// 初始化 云服务引擎，云服务引擎会设置好我们的运行环境，包括 可写目录 等
CloudHelper::initCloudEnv(PluginHelper::SYSTEM_CONSOLE);

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
