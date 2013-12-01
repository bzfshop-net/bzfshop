<?php
/**
 * @author QiangYu
 *
 * Option 的操作工具类
 *
 */

namespace Core\Plugin\Option;


class OptionHelper
{

    protected static $optionDriver;

    /**
     * 设置对应的 option 驱动实现
     *
     * @param $optionDriver
     */
    public static function setOptionDriver($optionDriver)
    {
        if (empty($optionDriver) || !$optionDriver instanceof IOptionDriver) {
            throw new \InvalidArgumentException('optionDriver is invalid');
        }
        static::$optionDriver = $optionDriver;
    }

    static function __callstatic($func, array $args)
    {
        if (empty(static::$optionDriver)) {
            throw new \InvalidArgumentException('optionDriver is invalid');
        }
        return call_user_func_array(array(static::$optionDriver, $func), $args);
    }
}