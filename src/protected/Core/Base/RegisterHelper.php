<?php
/**
 *  支持注册的 Helper 基类
 *
 *  提供基本的 class register 注册功能，我们使用这种 “注册机制”提供给 Plugin 操作
 *
 *  任何的 plugin 可以很轻量的注册一个自己的处理类扩展系统的功能
 *
 * @author QiangYu
 */

namespace Core\Base;

abstract class RegisterHelper
{

    /**
     * 允许设置 instance 的类型用于检查，比如 'ISearch' 用于检查 Search 接口
     *
     * @var string
     */
    protected static $instanceType = null;

    // 注册 class 对应的 name --> initParam
    protected static $registerClassArray = array();

    // 记录 key --> class 的对应，这样我们可以从 $registerClassArray 加载类实例
    protected static $registerKeyArray = array();

    /**
     * 根据 $className 加载一个 instance
     *
     * @param string $className
     * @param array  $initParam
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected static function loadClassInstance($className, array $initParam)
    {
        // 检查 class 是否存在
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('class [' . $className . '] does not exist');
        }

        $instance = new $className;

        // 检查 class 对应的类型是否正确
        if (static::$instanceType && !is_a($instance, static::$instanceType)) {
            throw new \InvalidArgumentException('class [' . $className . '] is not [' . static::$instanceType . ']');
        }

        // 初始化 instance
        if (method_exists($instance, 'init')) {
            call_user_func_array(array($instance, 'init'), $initParam);
        }

        return $instance;
    }

    /**
     * 注册一个处理的 class
     *
     * @param string $className  类名
     * @param mixed  $initParam  init方法的调用参数
     *
     * @throws \InvalidArgumentException
     */
    public static function registerInstanceClass($className, $initParam = null)
    {
        if (array_key_exists($className, static::$registerClassArray)) {
            throw new \InvalidArgumentException('class [' . $className . '] already registered');
        }

        $initParam = $initParam ? : array();
        $initParam = is_scalar($initParam) ? array($initParam) : $initParam;

        global $f3;
        if ($f3->get('DEBUG')) {
            // DEBUG 模式下预先做参数检查，有错误早知道
            $instance = static::loadClassInstance($className, $initParam);
            unset($instance);
        }

        static::$registerClassArray[$className] = $initParam;
    }

    /**
     * 取消一个 class 的注册， 我不确定你为什么会用它，但是我还是保留这个借口给你使用
     *
     * @param string $className
     */
    public static function unregisterInstanceClass($className)
    {
        if (array_key_exists($className, static::$registerClassArray)) {
            unset(static::$registerClassArray[$className]);
        }
    }

    /**
     * 注册一个 key 对应的 class 实例
     *
     * @param  string $key
     * @param  string $className
     * @param array   $initParam
     *
     * @throws \InvalidArgumentException
     */
    public static function registerInstanceKeyClass($key, $className, $initParam = null)
    {
        if (array_key_exists($key, static::$registerKeyArray)) {
            throw new \InvalidArgumentException('key [' . $key . '] already registered');
        }

        // 注册 class
        static::registerInstanceClass($className, $initParam);

        // 保存 key --> class 映射关系
        static::$registerKeyArray[$key] = $className;
    }

    /**
     * 取消一个 key 的注册， 我不确定你为什么会用它，但是我还是保留这个借口给你使用
     *
     * @param string $key
     */
    public static function unregisterInstanceKeyClass($key)
    {
        if (array_key_exists($key, static::$registerKeyArray)) {
            static::unregisterInstanceClass(static::$registerKeyArray[$key]);
            unset(static::$registerKeyArray[$key]);
        }
    }

    /**
     * 根据 $key 加载一个 instance 上来
     *
     * @param string $key
     * @param array  $initParam 初始化的参数数组
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected static function loadKeyInstance($key, $initParam = array())
    {
        if (!array_key_exists($key, static::$registerKeyArray)) {
            throw new \InvalidArgumentException('key [' . $key . '] does not exist');
        }

        return static::loadClassInstance(static::$registerKeyArray[$key], $initParam);
    }
}