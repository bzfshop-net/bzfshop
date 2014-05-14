<?php

/**
 * @author QiangYu
 *
 * SmartyHelper 用于提供我们自己的模板使用的一些函数，
 *
 * */
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Service\Goods\Category as BasicCategoryService;


/**
 * 注册所有我们可能会用到的 function()
 * 所有函数前缀都是 bzf_ ，比如 bzf_makeUrl()，方便识别
 * */
function smarty_helper_register(&$smarty)
{
    $smarty->registerPlugin('function', 'bzf_flash_message_str', 'smarty_helper_function_flash_message_str');
    $smarty->registerPlugin('function', 'bzf_debug_log_web', 'smarty_helper_function_debug_log_web');
    $smarty->registerPlugin('function', 'bzf_make_url', 'smarty_helper_function_make_url');
    $smarty->registerPlugin('function', 'bzf_paginator', 'smarty_helper_function_paginator');
    $smarty->registerPlugin('function', 'bzf_category_list', 'smarty_helper_function_category_list');
    $smarty->registerPlugin('function', 'bzf_goods_thumb_image', 'smarty_helper_function_goods_thumb_image');

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
    $smarty->registerPlugin('modifier', 'bzf_localtime', 'smarty_helper_modifier_localtime');
    $smarty->registerPlugin('modifier', 'bzf_html_image_lazyload', 'smarty_helper_modifier_html_image_lazyload');
    $smarty->registerPlugin('modifier', 'bzf_money_display', 'smarty_helper_modifier_money_display');

    $smarty->registerPlugin('block', 'bzf_is_option_valid', 'smarty_helper_block_is_option_valid');

    $smarty->registerFilter('output', 'smarty_helper_output_filter_replace_session_id');
}

/**
 * 方便程序取得 optionValue 的一个方法
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

    return \Theme\Mobile\MobileThemePlugin::getOptionValue($paramArray['optionKey']);
}

/**
 * 取得 flash message
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return string
 */
function smarty_helper_function_flash_message_str(array $paramArray, $smarty)
{
    $currentController = $smarty->getTemplateVars('currentController');

    if (!$currentController) {
        return '';
    }

    $flashMessgeArray = $currentController->getFlashMessageArray();
    if (empty($flashMessgeArray)) {
        return '';
    }

    return json_encode($flashMessgeArray);
}

/**
 * 输出日志到网页上，方便调试
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return string
 */
function smarty_helper_function_debug_log_web(array $paramArray, $smarty)
{
    // 需要一个全局定义的 logCollector ，从这里收集所有的日志然后输出
    global $logCollector;
    global $f3;

    // 只有在 debug 模式我们才输出日志
    if (!$f3->get('DEBUG') || !$logCollector) {
        return '';
    }

    // 按照表格方式输出调试信息
    $logTableStr = '';

    $logArray = $logCollector->getLogArray();
    $index    = 0;
    foreach ($logArray as $logItem) {
        $logTableStr .=
            '<tr>'
            . '<td>'
            . ($index++) . '</td><td>'
            . $logItem['level'] . '</td><td>'
            . $logItem['source'] . '</td><td>'
            . $logItem['msg']
            . '</td>'
            . '</tr>';
    }

    return <<<DEBUGMSG
    <br/><br/><br/>
 <table data-role="table" data-mode="reflow" class="ui-responsive table-stroke">
  <thead>
    <tr>
      <th>Id</th>
      <th>Level</th>
      <th>Source</th>
      <th>Message</th>
    </tr>
  </thead>
  <tbody>
  {$logTableStr}
  <tbody>
  </table>
DEBUGMSG;
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
 * 生成分页栏，用法：{{bzf_paginator count=$totalCount pageNo=$pageNo pageSize=$pageSize }}
 */
function smarty_helper_function_paginator(array $paramArray, $smarty)
{
    $count    = isset($paramArray['count']) ? $paramArray['count'] : 0;
    $pageNo   = isset($paramArray['pageNo']) ? $paramArray['pageNo'] : 0;
    $pageSize = isset($paramArray['pageSize']) ? $paramArray['pageSize'] : 10;

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

    // 处理参数
    $currentUrl = RouteHelper::getRequestURL();
    // 去除已有的 page 和 size 参数
    $currentUrl = RouteHelper::removeParam($currentUrl, 'pageNo');
    $currentUrl = RouteHelper::removeParam($currentUrl, 'pageSize');


    // 上一页
    $pagePrevious =
        '<a data-role="button" class="ui-disabled" data-icon="arrow-l" data-iconpos="left" href="#" data-theme="b">上一页</a>';
    if ($pageNo > 0) {
        $pagePrevious = '<a data-role="button" data-icon="arrow-l" data-iconpos="left" href="'
            . RouteHelper::addParam(
                $currentUrl,
                array('pageNo' => ($pageNo - 1)),
                true
            )
            . '"  data-theme="b" data-direction="reverse" data-transition="flow" >上一页</a>';
    }

    // 下一页
    $pageNext =
        '<a data-role="button" class="ui-disabled" data-icon="arrow-r" data-iconpos="right" href="#" data-theme="b">下一页</a>';;
    if ($pageNo < $totalPage - 1) {
        $pageNext = '<a data-role="button" data-icon="arrow-r" data-iconpos="right" href="'
            . RouteHelper::addParam(
                $currentUrl,
                array('pageNo' => ($pageNo + 1)),
                true
            )
            . '"  data-theme="b" data-transition="flow">下一页</a>';
    }

    return '<div class="ui-grid-a"><div class="ui-block-a">'
    . $pagePrevious
    . '</div><div class="ui-block-b">'
    . $pageNext
    . '</div></div>';
}

/**
 * 生成网页左侧分类列表的显示
 */
function smarty_helper_function_category_list(array $paramArray, $smarty)
{
    $basicCategoryService = new BasicCategoryService();

    $liHead = '<li><a data-transition="flow"  href="';

    // 我们只显示顶级分类
    $categoryArray = $basicCategoryService->fetchCategoryArray(0, false, 1800); // 缓存 30 分钟

    $output = $liHead . RouteHelper::makeUrl('/') . '">网站首页</a></li>';
    foreach ($categoryArray as $categoryItem) {
        $output .=
            $liHead . RouteHelper::makeUrl(
                '/Goods/Search',
                array('category_id' => $categoryItem['meta_id']),
                false,
                false,
                false
            ) . '">'
            . $categoryItem['meta_name'] . '</a></li>';
    }

    return $output;
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
        \Theme\Mobile\MobileThemePlugin::pluginGetUniqueId(),
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
                    \Theme\Mobile\MobileThemePlugin::pluginGetUniqueId(),
                    $relativeAssetPath
                ) . '"/>' . "\n";
        }
    } else {
        // 合并文件
        $outputStr = '<link rel="stylesheet" type="text/css" href="' . \Core\Asset\ManagerHelper::getMergedAssetCssUrl(
                \Theme\Mobile\MobileThemePlugin::pluginGetUniqueId(),
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
                    \Theme\Mobile\MobileThemePlugin::pluginGetUniqueId(),
                    $relativeAssetPath
                ) . '"></script>' . "\n";
        }
    } else {
        // 合并文件
        $outputStr = '<script type="text/javascript" src="' . \Core\Asset\ManagerHelper::getMergedAssetJsUrl(
                \Theme\Mobile\MobileThemePlugin::pluginGetUniqueId(),
                $fileRelativeNameArray
            ) . '"></script>';
    }

    return $outputStr;
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
        smarty_helper_function_get_asset_url(array('asset' => 'img/lazyload_placeholder_700_300.png'), null);

    $htmlContent = preg_replace(
        '/<img(.*?)src="(.*?)"(.*?)\/?>/',
        '<img class="lazyloadtap goods_desc_image bzf_zoom" src="' . $loadingImage . '" data-original="\2" />' .
        '<noscript><img class="goods_desc_image" \1 src="\2" \3 /></noscript>',
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
 * 判断选项是否有效，从而决定是否输出模板中的值
 *
 * @param                          $params
 * @param                          $content
 * @param Smarty_Internal_Template $template
 * @param                          $repeat
 *
 * @return string
 */
function smarty_helper_block_is_option_valid($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    if ($repeat || !isset($params['optionKey'])) {
        return '';
    }

    // 取得选项值
    $optionValue = smarty_helper_function_get_option_value($params, null);

    if (empty($optionValue)) {
        return '';
    }

    return $content; // 成功从这里返回
}

/**
 * bzf_make_url 本身会自动带上 sessionId 作为参数，但是由于我们使用了 smarty 缓存页面，所以我们把 session id 也给缓存了，
 * 每个用户应该使用自己的 session id，不能使用缓存的别人的 session id，这个函数就是做 session id 的替换
 *
 * 替换所有的 session id 参数
 *
 * @param                          $tpl_output
 * @param Smarty_Internal_Template $template
 *
 * @return mixed
 */
function smarty_helper_output_filter_replace_session_id($tpl_output, Smarty_Internal_Template $template)
{
    $sessionName = session_name();
    $sessionId   = session_id();

    return
        preg_replace('/' . $sessionName . '=[0-9a-zA-Z]+/', $sessionName . '=' . $sessionId, $tpl_output);
}

/**
 * 正确显示商品价格，千分位价格显示 比如 52.10 显示为 52.1 ， 52.00 显示为 52
 */
function smarty_helper_modifier_money_display($price)
{
    return \Core\Helper\Utility\Money::toSmartyDisplay($price);
}

