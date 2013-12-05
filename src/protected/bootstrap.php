<?php

/**
 * @author QiangYu
 *
 *    整体系统的初始化文件
 *
 *  1. 初始化 F3 框架
 *  2. 设置框架的全局变量
 *  3. 设置缺省值
 *  4. 初始化 smarty 模板
 *
 *    目前整体系统只有 3 个全局变量
 *    $f3 : F3 框架的实例
 *    $smarty : $smarty 实例
 *    $logger : 用于输出日志信息
 *
 * */
define('PROTECTED_PATH', dirname(__FILE__));

// 设置缺省的时区
date_default_timezone_set('Asia/Shanghai');

// 系统框架初始化
$f3 = require_once(PROTECTED_PATH . '/Framework/F3/base.php');

// 设置 http 输出的 header 'X-Powered-By' 信息
$f3->set('PACKAGE', 'BZFSHOP');

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
require_once(PROTECTED_PATH . '/Core/Plugin/IPlugin.php');
require_once(PROTECTED_PATH . '/Core/Plugin/AbstractBasePlugin.php');
require_once(PROTECTED_PATH . '/Core/Plugin/PluginHelper.php');
require_once(PROTECTED_PATH . '/Core/Plugin/Option/IOptionDriver.php');
require_once(PROTECTED_PATH . '/Core/Plugin/Option/OptionDbDriver.php');
require_once(PROTECTED_PATH . '/Core/Plugin/Option/OptionHelper.php');

// 设置 UI 路径
// 注意：我们使用 smarty 模板引擎，所以这里 UI 对我们没什么用，这里唯一的用途是用于验证码(captcha)生成时候指定字体
$f3->set('UI', PROTECTED_PATH . '/Framework/F3/UI/');

// 初始化 smarty 模板引擎
require_once(PROTECTED_PATH . '/Framework/Smarty/Smarty.class.php');
$smarty = new Smarty;

//修改 smarty 缺省的 {}，太容易导致错误了
$smarty->left_delimiter  = '{{';
$smarty->right_delimiter = '}}';

// smarty 缺省对所有的输出都要做 html_specialchars 防止出错
$smarty->escape_html = true;

// Smarty 严重安全漏洞，由于 smarty 不会过滤 <script language="php">phpinfo();</php> 这样的代码，我们只能自己过滤
function smarty_helper_security_output_filter($source, Smarty_Internal_Template $smartyTemplate)
{
    return preg_replace('/<script[^>]*language[^>]*>(.*?)<\/script>/is', "", $source);
}

$smarty->registerFilter('output', 'smarty_helper_security_output_filter');

//缺省不缓存，防止出错，后面不同页面根据实际需要再自己定义缓存
$smarty->setCaching(Smarty::CACHING_OFF);
$smarty->setCacheLifetime(0);

/**
 * 定义全局的 smarty 缓存开关
 *
 * @param $enable boolean 开/关
 * @param $ttl    integer 缓存时间
 * @param $policy smarty 缓存时间的策略，比如 \Smarty::CACHING_LIFETIME_SAVED
 * */
function enableSmartyCache($enable, $ttl = 0, $policy = \Smarty::CACHING_LIFETIME_SAVED)
{
    global $f3;
    global $smarty;

    if ($enable && $f3->get('sysConfig[smarty_caching]')) {
        $smarty->setCaching($policy);
        $smarty->setCacheLifetime($ttl);
        return;
    }
    $smarty->setCaching(Smarty::CACHING_OFF);
}

// 初始化 logger
$logger = new \Core\Log\Wrapper();

/**
 * 用一个简单的全局函数方便日志的输出
 *
 * @param string $msg    日志消息
 * @param string $source 日志的来源，比如 'SQL'
 * @param string $level  日志等级
 *
 * */
function printLog($msg, $source = '', $level = \Core\Log\Base::INFO)
{
    global $logger;
    $logger->addLogInfo($level, $source, $msg);
}

// option 我们使用数据库实现
\Core\Plugin\Option\OptionHelper::setOptionDriver(new \Core\Plugin\Option\OptionDbDriver());

// 设置插件所在的路径
\Core\Plugin\PluginHelper::addPluginDir(PROTECTED_PATH . '/Plugin'); // 系统自带的插件

// 设置 Theme 所在的路径
\Core\Plugin\ThemeHelper::addPluginDir(PROTECTED_PATH . '/Theme');

// 加载运行环境变量设置
$f3->config(PROTECTED_PATH . '/Config/env.cfg');

// 加载全局变量设置
$f3->config(PROTECTED_PATH . '/Config/common.cfg');
// 根据环境变量的不同，加载对应的环境变量设置，开发环境和生产环境的配置显然是不一样的
$f3->config(PROTECTED_PATH . '/Config/common-' . $f3->get('sysConfig[env]') . '.cfg');
