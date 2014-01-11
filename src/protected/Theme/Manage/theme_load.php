<?php

/**
 * @author QiangYu
 *
 * 主题加载文件，用于主题自身的初始化
 *
 * */

use Core\Plugin\SystemHelper;

$themeUniqueId = 'theme_manage';
$themeVersion  = $f3->get('sysConfig[version]') ? : '1.0';
$themeBasePath = dirname(__FILE__);

// 增加当前主题的 autoload 路径
SystemHelper::addAutoloadPath($themeBasePath . '/Code', true);

// 加载路由定义
$f3->config($themeBasePath . '/route.cfg');
$f3->config($themeBasePath . '/route-rewrite.cfg');

// 注册一个通用的缓存清除程序
\Core\Cache\ClearHelper::registerInstanceClass('\Core\Cache\ShareClear');

// 设置模板文件路径
$smarty->addTemplateDir($themeBasePath . '/Tpl/');

// 注册模块
\Core\Asset\ManagerHelper::registerModule($themeUniqueId, $themeVersion, $themeBasePath . '/Asset');

// 发布主题的资源文件
\Core\Asset\ManagerHelper::publishAsset($themeUniqueId, 'bootstrap-custom');
\Core\Asset\ManagerHelper::publishAsset($themeUniqueId, 'css');
\Core\Asset\ManagerHelper::publishAsset($themeUniqueId, 'img');
\Core\Asset\ManagerHelper::publishAsset($themeUniqueId, 'js');

// 加载 smarty 的扩展，里面有一些我们需要用到的函数
require_once(dirname(__FILE__) . '/Code/smarty_helper.php');

// 注册 smarty 函数
smarty_helper_register($smarty);

// 注册资源应用函数
function smarty_helper_get_asset_url(array $paramArray, $smarty)
{
    global $themeUniqueId;

    if (!isset($paramArray['asset'])) {
        return '';
    }

    return \Core\Asset\ManagerHelper::getAssetUrl($themeUniqueId, $paramArray['asset']);
}


/**
 * 获取合并之后的 Css 文件
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return string
 */
function smarty_helper_function_dump_merged_asset_css_url(array $paramArray, $smarty)
{
    global $themeUniqueId;

    if (!isset($paramArray['asset'])) {
        return '';
    }

    global $f3;
    $merge = $f3->get('sysConfig[enable_asset_merge]');

    if (isset($paramArray['merge'])) {
        $merge = $paramArray['merge'];
    }

    $fileRelativeNameArray = explode(',', preg_replace('![\r\n\s\t]+!', '', $paramArray['asset']));

    if (empty($fileRelativeNameArray)) {
        return '';
    }

    $outputStr = '';
    if (!$merge) {
        foreach ($fileRelativeNameArray as $relativeAssetPath) {
            $outputStr .= '<link rel="stylesheet" type="text/css" href="' . \Core\Asset\ManagerHelper::getAssetUrl(
                    $themeUniqueId,
                    $relativeAssetPath
                ) . '"/>' . "\n";
        }
    } else {
        // 合并文件
        $outputStr = '<link rel="stylesheet" type="text/css" href="' . \Core\Asset\ManagerHelper::getMergedAssetCssUrl(
                $themeUniqueId,
                $fileRelativeNameArray
            ) . '"/>';
    }

    return $outputStr;
}

/**
 * 获取合并之后的 JS 文件
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return string
 */
function smarty_helper_function_dump_merged_asset_js_url(array $paramArray, $smarty)
{
    global $themeUniqueId;

    if (!isset($paramArray['asset'])) {
        return '';
    }

    global $f3;
    $merge = $f3->get('sysConfig[enable_asset_merge]');

    if (isset($paramArray['merge'])) {
        $merge = $paramArray['merge'];
    }

    $fileRelativeNameArray = explode(',', preg_replace('![\r\n\s\t]+!', '', $paramArray['asset']));

    if (empty($fileRelativeNameArray)) {
        return '';
    }

    $outputStr = '';
    if (!$merge) {
        foreach ($fileRelativeNameArray as $relativeAssetPath) {
            $outputStr .= '<script type="text/javascript" src="' . \Core\Asset\ManagerHelper::getAssetUrl(
                    $themeUniqueId,
                    $relativeAssetPath
                ) . '"></script>' . "\n";
        }
    } else {
        // 合并文件
        $outputStr = '<script type="text/javascript" src="' . \Core\Asset\ManagerHelper::getMergedAssetJsUrl(
                $themeUniqueId,
                $fileRelativeNameArray
            ) . '"></script>';
    }

    return $outputStr;
}

$smarty->registerPlugin('function', 'bzf_get_asset_url', 'smarty_helper_get_asset_url');
$smarty->registerPlugin(
    'function',
    'bzf_dump_merged_asset_css_url',
    'smarty_helper_function_dump_merged_asset_css_url'
);
$smarty->registerPlugin(
    'function',
    'bzf_dump_merged_asset_js_url',
    'smarty_helper_function_dump_merged_asset_js_url'
);

// 设置网站的 Base 路径，给 JavaScript 使用
$smarty->assign("WEB_ROOT_HOST", $f3->get('sysConfig[webroot_schema_host]'));
$smarty->assign("WEB_ROOT_BASE", $f3->get('BASE'));
$smarty->assign(
    "WEB_ROOT_BASE_RES",
    \Core\Asset\ManagerHelper::getAssetUrl($themeUniqueId, '')
);
