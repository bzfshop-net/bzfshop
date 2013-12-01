<?php
/**
 * @author QiangYu
 *
 * 资源管理工具类
 *
 */

namespace Core\Asset;


class ManagerHelper
{

    protected static $assetManager;

    public static function setAssetManager($assetManager)
    {
        if (empty($assetManager) || !$assetManager instanceof IManager) {
            throw new \InvalidArgumentException('$assetManager is invalid');
        }
        static::$assetManager = $assetManager;
    }

    static function __callstatic($func, array $args)
    {
        if (empty(static::$assetManager)) {
            throw new \InvalidArgumentException('assetManager needs configure before you can use');
        }
        return call_user_func_array(array(static::$assetManager, $func), $args);
    }

}