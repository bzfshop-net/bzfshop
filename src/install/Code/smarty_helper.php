<?php

/**
 * @author QiangYu
 *
 * SmartyHelper 用于提供我们自己的模板使用的一些函数，
 *
 * */
use Core\Helper\Utility\Route as RouteHelper;

/**
 * 注册所有我们可能会用到的 function()
 * 所有函数前缀都是 bzf_ ，比如 bzf_makeUrl()，方便识别
 * */
function smarty_helper_register(&$smarty)
{
    $smarty->registerPlugin('function', 'bzf_make_url', 'smarty_helper_function_make_url');
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
