<?php

/**
 * @author QiangYu
 *
 * SmartyHelper 用于提供我们自己的模板使用的一些函数，
 *
 * */
use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Service\Goods\Gallery as GoodsGalleryService;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Meta\Dictionary as MetaDictionaryService;
use Core\Service\User\Admin as AdminUserService;

/**
 * 注册所有我们可能会用到的 function()
 * 所有函数前缀都是 bzf_ ，比如 bzf_makeUrl()，方便识别
 * */
function smarty_helper_register(&$smarty)
{
    $smarty->registerPlugin('block', 'bzf_verify_privilege', 'smarty_helper_block_verify_privilege');

    $smarty->registerPlugin('function', 'bzf_dump_register_css', 'smarty_helper_function_dump_register_css');
    $smarty->registerPlugin('function', 'bzf_dump_register_js', 'smarty_helper_function_dump_register_js');

    $smarty->registerPlugin('function', 'bzf_express_query_url', 'smarty_helper_function_express_query_url');
    $smarty->registerPlugin('function', 'bzf_mobile_query_url', 'smarty_helper_function_mobile_query_url');
    $smarty->registerPlugin('function', 'bzf_ip_query_url', 'smarty_helper_function_ip_query_url');

    $smarty->registerPlugin('function', 'bzf_get_sysconfig', 'smarty_helper_function_get_sysconfig');
    $smarty->registerPlugin('function', 'bzf_make_url', 'smarty_helper_function_make_url');
    $smarty->registerPlugin('function', 'bzf_paginator', 'smarty_helper_function_paginator');
    $smarty->registerPlugin('function', 'bzf_goods_thumb_image', 'smarty_helper_function_goods_thumb_image');
    $smarty->registerPlugin('function', 'bzf_thumb_image', 'smarty_helper_function_thumb_image');
    $smarty->registerPlugin('function', 'bzf_goods_view_toolbar', 'smarty_helper_function_goods_view_toolbar');

    $smarty->registerPlugin('modifier', 'bzf_localtime', 'smarty_helper_modifier_localtime');
    $smarty->registerPlugin('modifier', 'bzf_dictionary_name', 'smarty_helper_modifier_dictionary_name');
    $smarty->registerPlugin('modifier', 'bzf_money_display', 'smarty_helper_modifier_money_display');
    $smarty->registerPlugin('modifier', 'bzf_system_name', 'smarty_helper_modifier_system_display_name');

}

/**
 * 判断用户是否有某种权限
 *
 * @param                          $params
 * @param                          $content
 * @param Smarty_Internal_Template $template
 * @param                          $repeat
 */
function smarty_helper_block_verify_privilege($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    if ($repeat) {
        return '';
    }

    if (array_key_exists('privilege', $params) && !empty($params['privilege'])) {

        $authAdminUser = AuthHelper::getAuthUser();
        if (empty($authAdminUser)) {
            return '';
        }

        // 检查权限
        if (!AdminUserService::verifyPrivilege(
            $params['privilege'],
            $authAdminUser['action_list'] . ',' . $authAdminUser['role_action_list']
        )
        ) {
            return '';
        }

        return $content; // 成功从这里返回
    }
}

/**
 * dump 出所有插件注册的 css 文件
 *
 * @param array $paramArray
 * @param       $smarty
 */
function smarty_helper_function_dump_register_css(array $paramArray, $smarty)
{

    $cssArray = \Core\Asset\ManagerHelper::getRegisterCssArray();

    if (empty($cssArray)) {
        return '';
    }

    $cssLinkHtml = '';

    foreach ($cssArray as $cssItem) {
        $cssLinkHtml .= '<link rel="stylesheet" type="text/css" href="' . $cssItem . '"/>';
    }

    return $cssLinkHtml;
}

/**
 * dump 出所有插件注册的 js 文件
 *
 * @param array $paramArray
 * @param       $smarty
 */
function smarty_helper_function_dump_register_js(array $paramArray, $smarty)
{

    $jsArray = \Core\Asset\ManagerHelper::getRegisterJsArray();

    if (empty($jsArray)) {
        return '';
    }

    $jsHtml = '';

    foreach ($jsArray as $jsItem) {
        $jsHtml .= '<script type="text/javascript" src="' . $jsItem . '"></script>';
    }

    return $jsHtml;
}

/**
 * 取得系统配置数据
 *
 * @param array $paramArray
 * @param $smarty
 * @return string
 */
function smarty_helper_function_get_sysconfig(array $paramArray, $smarty)
{
    $key = isset($paramArray['key']) ? $paramArray['key'] : null;
    if (empty($key)) {
        return '';
    }

    global $f3;
    return $f3->get('sysConfig[' . $key . ']');
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
    $static = isset($paramArray['static']) ? $paramArray['static'] : null;

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
 * 生成手机号查询地址
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return string
 */
function smarty_helper_function_mobile_query_url(array $paramArray, $smarty)
{
    return 'http://www.showji.com/search.htm?m=' . @$paramArray['mobile'];
}

/**
 * 生成IP查询地址
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return string
 */
function smarty_helper_function_ip_query_url(array $paramArray, $smarty)
{
    return 'http://www.ip138.com/ips138.asp?ip=' . @$paramArray['ip'];
}

/**
 * 生成分页栏，用法：{{bzf_paginator count=$totalCount pageNo=$pageNo pageSize=$pageSize }}
 */
function smarty_helper_function_paginator(array $paramArray, $smarty)
{
    $count = isset($paramArray['count']) ? $paramArray['count'] : 0;
    $pageNo = isset($paramArray['pageNo']) ? $paramArray['pageNo'] : 0;
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

    // 首页
    $paginator = '<li><a href="' . RouteHelper::addParam($currentUrl, array('pageNo' => 0), true) . '">首页</a></li>';

    // 前后各输出 5 页
    $displayPageCount = 5;

    $pageStart = ($pageNo - $displayPageCount > 0) ? ($pageNo - $displayPageCount) : 0;
    $pageEnd = ($pageNo + $displayPageCount < $totalPage) ? ($pageNo + $displayPageCount) : $totalPage - 1;

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

    return '<span>（总数：' . $count . '&nbsp;&nbsp;页数：' . ($pageNo + 1) . '/' . $totalPage . '）</span><ul>' . $paginator
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
 * 根据 img_id 取得缩略图
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return string
 */
function smarty_helper_function_thumb_image(array $paramArray, $smarty)
{
    $img_id = isset($paramArray['img_id']) ? intval($paramArray['img_id']) : 0;

    // 参数不对，没有东西可以输出
    if ($img_id <= 0) {
        return '';
    }

    static $goodsGalleryService = null;
    if (!$goodsGalleryService) {
        $goodsGalleryService = new GoodsGalleryService();
    }

    $galleryItem = $goodsGalleryService->loadGoodsGalleryById($img_id, 1800);
    if ($galleryItem->isEmpty()) {
        return '';
    }

    return RouteHelper::makeImageUrl($galleryItem['thumb_url']);
}

/**
 * 生成商品查看的 toolbar，用户可以查看 团购、商城、移动
 *
 * @param array $paramArray
 * @param       $smarty
 *
 * @return string
 */
function smarty_helper_function_goods_view_toolbar(array $paramArray, $smarty)
{
    $goods_id = isset($paramArray['goods_id']) ? intval($paramArray['goods_id']) : 0;
    $system_tag_list = isset($paramArray['system_tag_list']) ? $paramArray['system_tag_list'] : '';

    // 参数不对，没有东西可以输出
    if ($goods_id <= 0) {
        return 'goods_id [' . $goods_id . '] 非法';
    }

    // 如果不提供 system_tag_list 参数，我们从数据库查询
    if (!array_key_exists('system_tag_list', $paramArray)) {

        static $goodsBasicService = null;
        if (!$goodsBasicService) {
            $goodsBasicService = new GoodsBasicService();
        }

        // 缓存 5 秒钟
        $goods = $goodsBasicService->loadGoodsById($goods_id, 5);
        if (!$goods->isEmpty()) {
            $system_tag_list = $goods['system_tag_list'];
        } else {
            return 'goods_id [' . $goods_id . '] 非法';
        }
    }

    // 解析成 System Array
    $systemArray = \Core\Helper\Utility\Utils::parseTagString($system_tag_list);

    $htmlContent = '<div class="btn-group">';

    $system_url_base_array =
        json_decode(\Theme\Manage\ManageThemePlugin::getOptionValue('system_url_base_array'), true);

    if (!empty($system_url_base_array)) {
        foreach ($systemArray as $system) {
            if (!array_key_exists($system, $system_url_base_array)) {
                // 不存在的系统，跳过
                continue;
            }
            $themeSystem = $system_url_base_array[$system];
            $htmlContent .= '<a title="查看' . $themeSystem['name'] . '商品详情" target="_blank" href="'
                . RouteHelper::makeShopSystemUrl($system, '/Goods/View', array('goods_id' => $goods_id))
                . '" class="btn btn-mini btn-info">' . $themeSystem['name'] . '</a>';
        }
    }

    $htmlContent .= '<a title="清除商品的页面缓存" href="'
        . RouteHelper::makeUrl('/Goods/ClearCache', array('goods_id' => $goods_id))
        . '" class="btn btn-mini btn-danger">' . '清缓存' . '</a>';

    $htmlContent .= '</div>';

    return htmlspecialchars($htmlContent);
}


/**
 * 系统使用的是 GM 时间，这个方法用于转换为 Local Time，并且显示
 */
function smarty_helper_modifier_localtime($gmTime, $format = null)
{
    return Time::gmTimeToLocalTimeStr($gmTime, $format);
}

/**
 * 根据字典中数据转换显示
 *
 * @param $key
 *
 * @return mixed
 */
function smarty_helper_modifier_dictionary_name($key)
{
    if (empty($key)) {
        return $key;
    }

    $metaDictionaryService = new MetaDictionaryService();
    $dictItem = $metaDictionaryService->getWord($key, 600); //缓存 10 分钟
    return $dictItem['name'];
}

/**
 * 正确显示商品价格，千分位价格显示 比如 52.10 显示为 52.1 ， 52.00 显示为 52
 */
function smarty_helper_modifier_money_display($price)
{
    return \Core\Helper\Utility\Money::toSmartyDisplay($price);
}

/**
 * 取得系统的可显示名字
 *
 */
function smarty_helper_modifier_system_display_name($system)
{
    if (empty($system)) {
        return '';
    }

    $system_url_base_array =
        json_decode(\Theme\Manage\ManageThemePlugin::getOptionValue('system_url_base_array'), true);

    if (!empty($system_url_base_array)) {

        if (!array_key_exists($system, $system_url_base_array)) {
            // 不存在的系统，跳过
            return '';
        }
        return $system_url_base_array[$system]['name'];
    }
}
