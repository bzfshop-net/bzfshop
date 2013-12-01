<?php

/**
 * @author QiangYu
 *
 *    整体系统的初始化文件，用于命令行系统
 *
 *  1. 初始化 F3 框架
 *  2. 设置框架的全局变量
 *  3. 设置缺省值
 *
 *    目前整体系统只有 2 个全局变量
 *    $f3 : F3 框架的实例
 *    $logger : 用于输出日志信息
 *
 * */
define('PROTECTED_PATH', dirname(__FILE__));

// 设置缺省的时区
date_default_timezone_set('Asia/Shanghai');

// 设置一些运行时候参数
ini_set('display_errors', 'On'); // 显示错误信息
ini_set('memory_limit', '128M'); // 内存 128MB 应该够用了
set_time_limit(0); // 没有时间限制

// 系统框架初始化
$f3 = require_once(PROTECTED_PATH . '/Framework/F3/base.php');

// 设置系统的 autoload 路径
$f3->set(
    'AUTOLOAD',
    PROTECTED_PATH . '/;'
    . PROTECTED_PATH . '/Framework/F3/;'
    . PROTECTED_PATH . '/Framework/F3Ext/;'
    . PROTECTED_PATH . '/Vendor/;'
);

//预先加载一些常用模块，提高后面的加载效率
require_once(PROTECTED_PATH . '/Framework/F3/magic.php');
require_once(PROTECTED_PATH . '/Framework/F3/db/cursor.php');
require_once(PROTECTED_PATH . '/Framework/F3/db/sql.php');
require_once(PROTECTED_PATH . '/Framework/F3/db/sql/mapper.php');
require_once(PROTECTED_PATH . '/Core/Modal/DbEngine.php');
require_once(PROTECTED_PATH . '/Core/Modal/SqlMapper.php');
require_once(PROTECTED_PATH . '/Core/Log/Base.php');
require_once(PROTECTED_PATH . '/Core/Log/Wrapper.php');
require_once(PROTECTED_PATH . '/Core/Plugin/Option/IOptionDriver.php');
require_once(PROTECTED_PATH . '/Core/Plugin/Option/OptionDbDriver.php');
require_once(PROTECTED_PATH . '/Core/Plugin/Option/OptionHelper.php');

// 初始化 logger
$logger = new \Core\Log\Wrapper();

/**
 * 用一个简单的全局函数方便日志的输出
 *
 * @param string $msg     日志消息
 * @param string $source  日志的来源，比如 'SQL'
 * @param string $level   日志等级
 *
 * */
function printLog($msg, $source = '', $level = \Core\Log\Base::INFO)
{
    global $logger;
    $logger->addLogInfo($level, $source, $msg);
}

// option 我们使用数据库实现
\Core\Plugin\Option\OptionHelper::setOptionDriver(new \Core\Plugin\Option\OptionDbDriver());

// 加载运行环境变量设置
$f3->config(PROTECTED_PATH . '/Config/env.cfg');

// 加载全局变量设置
$f3->config(PROTECTED_PATH . '/Config/common.cfg');
// 根据环境变量的不同，加载对应的环境变量设置，开发环境和生产环境的配置显然是不一样的
$f3->config(PROTECTED_PATH . '/Config/common-' . $f3->get('sysConfig[env]') . '.cfg');
