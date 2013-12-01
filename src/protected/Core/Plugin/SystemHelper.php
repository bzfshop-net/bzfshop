<?php
/**
 * @author QiangYu
 *
 * plugin 用于设置系统的一些运行环境操作
 *
 */

namespace Core\Plugin;


class SystemHelper
{

    /**
     * 添加一个用于 class autoload 的路径
     *
     * 注意：我们允许 plugin 覆盖系统本身的功能，所以如果你的 path 里面包含和系统名字一样的 Controller，
     * 你就可能会覆盖系统已有的操作
     *
     * @param string $path
     * @param bool   $addToHead 是否加入搜索路径的开头，如果你的类不常用，请加入到搜索末尾有利于提高搜索效率
     *
     */
    public static function addAutoloadPath($path, $addToHead = false)
    {
        if (!file_exists($path)) {
            printLog('[' . $path . '] does not exist', 'SystemHelper', \Core\Log\Base::WARN);
            return;
        }
        $path = realpath($path);

        global $f3;
        if ($addToHead) {
            // 加到搜索路径开头
            $f3->set('AUTOLOAD', $path . '/;' . $f3->get('AUTOLOAD'));
        } else {
            // 加到搜索路径末尾
            $f3->set('AUTOLOAD', $f3->get('AUTOLOAD') . $path . '/;');
        }
    }

    /**
     * 这里完全采用 F3 框架的路由功能，具体参见 F3 框架的说明
     *
     * @param string          $route
     * @param string|callable $target
     */
    public static function addRoute($route, $target)
    {
        global $f3;
        $f3->route($route, $target);
    }

    /**
     * 这里完全采用 F3 框架的路由功能，具体参见 F3 框架的说明
     *
     * @param string          $route
     * @param string|callable $target
     */
    public static function addRouteMap($route, $target)
    {
        global $f3;
        $f3->map($route, $target);
    }
}