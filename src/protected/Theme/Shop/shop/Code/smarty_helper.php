<?php

/**
 * @author QiangYu
 *
 * SmartyHelper 用于提供我们自己的模板使用的一些函数，
 *
 * */
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;

/**
 * 注册所有我们可能会用到的 function()
 * 所有函数前缀都是 bzf_ ，比如 bzf_makeUrl()，方便识别
 * */
function smarty_helper_register(&$smarty)
{
    $smarty->registerPlugin(
        'function',
        'bzf_query_plugin_feature_var',
        'smarty_helper_function_query_plugin_feature_var'
    );
    $smarty->registerPlugin('function', 'bzf_make_system_url', 'smarty_helper_function_make_system_url');
    $smarty->registerPlugin('function', 'bzf_make_url', 'smarty_helper_function_make_url');
    $smarty->registerPlugin('function', 'bzf_express_query_url', 'smarty_helper_function_express_query_url');
    $smarty->registerPlugin('function', 'bzf_paginator', 'smarty_helper_function_paginator');
    $smarty->registerPlugin('function', 'bzf_goods_thumb_image', 'smarty_helper_function_goods_thumb_image');
    $smarty->registerPlugin('function', 'bzf_goods_comment', 'smarty_helper_function_goods_comment');

    $smarty->registerPlugin('function', 'bzf_get_asset_url', 'smarty_helper_function_get_asset_url');
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

    $smarty->registerPlugin('function', 'bzf_get_option_value', 'smarty_helper_function_get_option_value');
    $smarty->registerPlugin('function', 'bzf_assign_option_value', 'smarty_helper_function_assign_option_value');

    $smarty->registerPlugin('modifier', 'bzf_mask_string', 'smarty_helper_modifier_mask_string');
    $smarty->registerPlugin('modifier', 'bzf_localtime', 'smarty_helper_modifier_localtime');
    $smarty->registerPlugin('modifier', 'bzf_html_image_lazyload', 'smarty_helper_modifier_html_image_lazyload');
    $smarty->registerPlugin('modifier', 'bzf_money_display', 'smarty_helper_modifier_money_display');
    $smarty->registerPlugin('modifier', 'bzf_money_int_part', 'smarty_helper_modifier_money_int_part');
    $smarty->registerPlugin('modifier', 'bzf_money_float_part', 'smarty_helper_modifier_money_float_part');
}

/**
 * 查询插件的某些属性，比如 插件的版本信息，财付通支付插件是否支持 网银直连
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return mixed|null
 */
function smarty_helper_function_query_plugin_feature_var(array $paramArray, $smarty)
{
    $varName = isset($paramArray['varName']) ? $paramArray['varName'] : '';
    unset($paramArray['varName']);
    $uniqueId = isset($paramArray['uniqueId']) ? $paramArray['uniqueId'] : '';
    unset($paramArray['uniqueId']);
    $command = isset($paramArray['command']) ? $paramArray['command'] : '';
    unset($paramArray['command']);

    if (empty($varName) || empty($uniqueId) || empty($command)) {
        throw new InvalidArgumentException('must provide varName, uniqueId, command');
    }

    $varValue = null;

    if (empty($uniqueId) || !$pluginInstance = \Core\Plugin\PluginHelper::getPluginInstanceByUniqueId($uniqueId)) {
        goto out;
    }

    // execute command to get result
    $varValue = $pluginInstance->pluginCommand($command, $paramArray);

    out:
    $smarty->assign($varName, $varValue);
}

/**
 * 用于生成系统的操作链接，符合系统 URL 调用规范
 *
 * 在模板中的使用方法 {{makeUrl controller='/User/Login' username='xxx' password='xxx' }}
 *
 * 必须要有 controller 用于指定控制器，其它参数可以没有
 *
 * */
function smarty_helper_function_make_system_url(array $paramArray, $smarty)
{
    $system     = isset($paramArray['system']) ? $paramArray['system'] : '';
    $controller = isset($paramArray['controller']) ? $paramArray['controller'] : '/Error/E404';
    $static     = isset($paramArray['static']) ? $paramArray['static'] : null;

    if (empty($system)) {
        return '';
    }

    // 去除 system,controller, static ，其它都是控制器的参数
    unset($paramArray['system']);
    unset($paramArray['controller']);
    unset($paramArray['static']);

    return RouteHelper::makeShopSystemUrl($system, $controller, $paramArray, $static);
}

/**
 * 用于生成系统的操作链接，符合系统 URL 调用规范
 *
 * 在模板中的使用方法 {{makeUrl controller='/User/Login' username='xxx' password='xxx' }}
 *
 * 必须要有 controller 用于指定控制器，其它参数可以没有
 *
 * */
function smarty_helper_function_make_url(array $paramArray, $smarty)
{
    $controller = isset($paramArray['controller']) ? $paramArray['controller'] : '/Error/E404';
    $static     = isset($paramArray['static']) ? $paramArray['static'] : null;

    // 去除 controller, static ，其它都是控制器的参数
    unset($paramArray['controller']);
    unset($paramArray['static']);

    return RouteHelper::makeUrl($controller, $paramArray, false, false, $static);
}

/**
 * 生成快递查询地址
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return string
 */
function smarty_helper_function_express_query_url(array $paramArray, $smarty)
{
    return 'http://www.kuaidi100.com/chaxun?'
    . 'com=' . urlencode(@$paramArray['expressName'])
    . '&nu=' . @$paramArray['expressNo'];
}

/**
 * 生成分页栏，用法：{{bzf_paginator count=$totalCount pageNo=$pageNo pageSize=$pageSize }}
 */
function smarty_helper_function_paginator(array $paramArray, $smarty)
{
    $count      = isset($paramArray['count']) ? $paramArray['count'] : 0;
    $pageNo     = isset($paramArray['pageNo']) ? $paramArray['pageNo'] : 0;
    $pageSize   = isset($paramArray['pageSize']) ? $paramArray['pageSize'] : 10;
    $currentUrl = isset($paramArray['currentUrl']) ? $paramArray['currentUrl'] : RouteHelper::getRequestURL();

    // 不需要分页
    if ($count <= 0 || $count < $pageSize) {
        return '';
    }

    // 修正 page 的值
    $pageNo = ($pageNo * $pageSize < $count) ? $pageNo : 0;

    $totalPage = ceil($count / $pageSize);

    // 只有一页，不需要分页
    if ($totalPage <= 1) {
        return '';
    }

    // 首页
    $paginator = '<li><a href="' . RouteHelper::addParam($currentUrl, array('pageNo' => 0), true) . '">首页</a></li>';

    // 前后各输出 5 页
    $displayPageCount = 5;

    $pageStart = ($pageNo - $displayPageCount > 0) ? ($pageNo - $displayPageCount) : 0;
    $pageEnd   = ($pageNo + $displayPageCount < $totalPage) ? ($pageNo + $displayPageCount) : $totalPage - 1;

    for ($pageIndex = $pageStart; $pageIndex <= $pageEnd; $pageIndex++) {
        $link = '';
        if (0 == $pageIndex || $totalPage - 1 == $pageIndex) {
            $link = '<a href="' . RouteHelper::addParam($currentUrl, array('pageNo' => $pageIndex), true) . '">' . (
                    $pageIndex + 1) . '</a>';
        } else {
            if ($pageStart == $pageIndex) {
                $link =
                    '<a href="' . RouteHelper::addParam(
                        $currentUrl,
                        array('pageNo' => $pageIndex),
                        true
                    ) . '">&lt;&lt;</a>';
            } else {
                if ($pageEnd == $pageIndex) {
                    $link =
                        '<a href="' . RouteHelper::addParam($currentUrl, array('pageNo' => $pageIndex), true)
                        . '">&gt;&gt;</a>';
                } else {
                    $link =
                        '<a href="' . RouteHelper::addParam($currentUrl, array('pageNo' => $pageIndex), true) . '">' . (
                            $pageIndex + 1) . '</a>';
                }
            }
        }

        if ($pageNo == $pageIndex) {
            // 当前页，active
            $paginator .= '<li class="active">' . $link . '</li>';
        } else {
            $paginator .= '<li>' . $link . '</li>';
        }
    }

    // 末页
    $paginator .=
        '<li><a href="' . RouteHelper::addParam(
            $currentUrl,
            array('pageNo' => ($totalPage - 1)),
            true
        ) . '">末页</a></li>';

    // 显示总共有多少页
    $pageCountDisplayStr = isset($paramArray['noPageCount'])
        ? '' : '<span>（总数：' . $count . '&nbsp;&nbsp;页数：' . ($pageNo + 1) . '/' . $totalPage . '）</span>';

    return $pageCountDisplayStr . '<ul>' . $paginator
    . '</ul>';
}

/**
 *
 * 取得商品缩略图的链接，这个函数会调用非常频繁，所以我们需要做好缓存提高效率
 *
 */
function smarty_helper_function_goods_thumb_image(array $paramArray, $smarty)
{
    $goods_id = isset($paramArray['goods_id']) ? intval($paramArray['goods_id']) : 0;

    // 参数不对，没有东西可以输出
    if ($goods_id <= 0) {
        return '';
    }
    return \Core\Cache\GoodsGalleryCache::getGoodsThumbImageUrl($goods_id);
}

/**
 * 取得商品的用户评价信息（取得第一页）
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return string
 */
function smarty_helper_function_goods_comment(array $paramArray, $smarty)
{
    $goods_id = isset($paramArray['goods_id']) ? intval($paramArray['goods_id']) : 0;

    // 参数不对，没有东西可以输出
    if ($goods_id <= 0) {
        return '';
    }
    $ajaxGoodsCommentController = new \Controller\Ajax\GoodsComment();
    return $ajaxGoodsCommentController->fetchPage($goods_id, 0);
}

/**
 * 获取资源文件的方法
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return string
 */
function smarty_helper_function_get_asset_url(array $paramArray, $smarty)
{
    if (!isset($paramArray['asset'])) {
        return '';
    }

    return \Core\Asset\ManagerHelper::getAssetUrl(
        \Theme\Shop\ShopThemePlugin::pluginGetUniqueId(),
        $paramArray['asset']
    );
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
                    \Theme\Shop\ShopThemePlugin::pluginGetUniqueId(),
                    $relativeAssetPath
                ) . '"/>' . "\n";
        }
    } else {
        // 合并文件
        $outputStr = '<link rel="stylesheet" type="text/css" href="' . \Core\Asset\ManagerHelper::getMergedAssetCssUrl(
                \Theme\Shop\ShopThemePlugin::pluginGetUniqueId(),
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
                    \Theme\Shop\ShopThemePlugin::pluginGetUniqueId(),
                    $relativeAssetPath
                ) . '"></script>' . "\n";
        }
    } else {
        // 合并文件
        $outputStr = '<script type="text/javascript" src="' . \Core\Asset\ManagerHelper::getMergedAssetJsUrl(
                \Theme\Shop\ShopThemePlugin::pluginGetUniqueId(),
                $fileRelativeNameArray
            ) . '"></script>';
    }

    return $outputStr;
}

/**
 * 获取主题设置的 option 的值
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return string
 */
function smarty_helper_function_get_option_value(array $paramArray, $smarty)
{
    if (!isset($paramArray['optionKey'])) {
        return '';
    }

    return \Theme\Shop\ShopThemePlugin::getOptionValue($paramArray['optionKey']);
}

/**
 * 把一个 option 的值赋值给 smarty 模板，方便在模板中调用
 *
 * @param array $paramArray
 * @param       $smarty
 */
function smarty_helper_function_assign_option_value(array $paramArray, $smarty)
{
    if (!isset($paramArray['optionKey'])) {
        return;
    }

    $optionKey   = $paramArray['optionKey'];
    $optionValue = \Theme\Shop\ShopThemePlugin::getOptionValue($optionKey);

    // 解析 json 值
    if (@$paramArray['decodeJson']) {
        $optionValue = json_decode($optionValue, true);
    }

    $smarty->assign($optionKey, $optionValue);
}

/**
 * 方便程序取得 optionValue 的一个方法
 *
 * @param string $optionKey
 *
 * @return string
 */
function bzf_get_option_value($optionKey)
{
    return \Theme\Shop\ShopThemePlugin::getOptionValue($optionKey);
}

/**
 * 对字符串做 mask 防止泄露关键信息，比如显示用户名
 *
 * @param $string
 *
 * @return string
 */
function smarty_helper_modifier_mask_string($string)
{
    return \Core\Helper\Utility\Utils::maskString($string);
}

/**
 * 系统使用的是 GM 时间，这个方法用于转换为 Local Time，并且显示
 */
function smarty_helper_modifier_localtime($gmTime, $format = null)
{
    return Time::gmTimeToLocalTimeStr($gmTime, $format);
}


/**
 * 把 html 中的图片替换成 lazyload 模式
 *
 * @param $htmlContent
 */
function smarty_helper_modifier_html_image_lazyload($htmlContent)
{
    $loadingImage =
        smarty_helper_function_get_asset_url(array('asset' => 'img/lazyload_placeholder_goods_desc.png'), null);

    $htmlContent = preg_replace(
        '/<img(.*?)src="(.*?)"(.*?)\/?>/',
        '<img \1 class="lazyload" src="' . $loadingImage . '" data-original="\2" \3 />',
        $htmlContent
    );

    // 这里做 文本替换
    global $f3;
    if ($f3->get('sysConfig[goods_desc_text_replace]')) {
        $replaceArray = $f3->get('sysConfig[goods_desc_text_replace]');
        foreach ($replaceArray as $key => $value) {
            $htmlContent = str_replace($key, $value, $htmlContent);
        }
    }

    return $htmlContent;
}

/**
 * 正确显示商品价格，千分位价格显示 比如 52.10 显示为 52.1 ， 52.00 显示为 52
 */
function smarty_helper_modifier_money_display($price)
{
    return \Core\Helper\Utility\Money::toSmartyDisplay($price);
}

/**
 * 取得价格的整数部分
 *
 * @param $price
 *
 * @return int
 */
function smarty_helper_modifier_money_int_part($price)
{
    return intval(smarty_helper_modifier_money_display($price));
}

/**
 * 取得价格的小数部分
 *
 * @param $price
 *
 * @return int
 */
function smarty_helper_modifier_money_float_part($price)
{
    $displayPrice = smarty_helper_modifier_money_display($price);
    $floatValue   = $displayPrice - intval($displayPrice);
    if (0 == $floatValue) {
        return '00';
    }

    $floatStr = number_format($floatValue, 2, '.', '');
    return substr($floatStr, strpos($floatStr, '.') + 1);
}
