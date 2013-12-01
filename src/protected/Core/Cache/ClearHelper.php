<?php
/**
 *
 * Clear 的 pipeline 实现，用于调用一串的 clear
 *
 */

namespace Core\Cache;

use Core\Base\PipelineHelper;

abstract class ClearHelper extends PipelineHelper
{
    // 所有 instance 类型必须都是 IClear 接口的实现
    protected static $instanceType = '\Core\Cache\IClear';

    // 注册 class 对应的 name --> initParam
    protected static $registerClassArray = array();

    /**
     * 清除 smarty 所有的缓存
     *
     * @param int $ttl  清除多长时间之前的缓存
     */
    public static function smartyClearAllCache($smarty, $ttl = 0)
    {
        if (!$smarty || !$smarty instanceof \Smarty) {
            throw new \InvalidArgumentException('smarty object invalid');
        }

        if (0 == $ttl) {
            $smarty->clearAllCache();
            return;
        }
        $smarty->clearAllCache($ttl);
    }

    /**
     * 清除特定的模板、模板组缓存，
     *
     * @param string $template   模板名，比如 team_view.tpl
     * @param string $cacheId    模板的 ID
     */
    public static function smartyClearCache($smarty, $template, $cacheId = null)
    {
        if (!$smarty || !$smarty instanceof \Smarty) {
            throw new \InvalidArgumentException('smarty object invalid');
        }
        $smarty->clearCache($template, $cacheId);
    }


}