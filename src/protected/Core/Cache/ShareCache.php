<?php
/**
 * 普通的 $f3->set('xxxx','xxxx', 600) 使用的缓存会根据每个系统(Shop, Mobile, Groupon ...)的不同有不同的缓存前缀
 * 所以，你在 Shop 里面 $f3->set('cacheKey', 'cacheValue', 100)，然后在 Mobile 里面 $f3->get('cacheKey') 是取不到值的
 * 因为 Shop 和 Mobile 系统的 Cache Prefix 是不一样的，这样可以有效的防止 Cache 冲突
 *
 * 有些时候，我们希望不同的系统(Shop, Mobile, Groupon ...)之间能够共用缓存，这样可以提高缓存命中效率，同时保证缓存的一致性
 * 这种情况下，我们就必须考虑使用 ShareCache 了
 *
 * 注意：由于 ShareCache 是各个系统(Shop, Mobile, Groupon ...)共用的，所以请谨慎选择你的 cacheKey，不然很容易发生冲突
 * （多个系统偶然的选择了同一个 cacheKey，但其实用途是不一样的，于是各个系统相互覆盖对方的缓存，造成错误）
 *
 * 注意： Cache Instance 在 $f3 中会自动初始化，我们只需要使用就可以了
 *
 * @author QiangYu
 *
 */

namespace Core\Cache;


final class ShareCache
{

    public static $cachePrefix = null;

    /**
     * @return string
     */
    protected static function getCachePrefix()
    {
        if (!static::$cachePrefix) {
            static::$cachePrefix = md5(__FILE__);
            // for debug purpose
            //static::$cachePrefix = str_replace(DIRECTORY_SEPARATOR, '_', __CLASS__);;
        }
        return static::$cachePrefix;
    }

    /**
     * 取得 Cache 值
     *
     * @param string $cacheKey
     *
     * @return mixed
     */
    public static function get($cacheKey)
    {
        // 设置 Cache 的 Prefix 为我们自己定义的 Prefix
        $cacheInstance  = \Cache::instance();
        $oldCachePrefix = $cacheInstance->getPrefix();
        $cacheInstance->setPrefix(static::getCachePrefix());

        $cacheValue = $cacheInstance->get($cacheKey);

        // 恢复 Cache 的 Prefix
        $cacheInstance->setPrefix($oldCachePrefix);

        return $cacheValue;
    }

    /**
     * 设置缓存值
     *
     * @param string $cacheKey
     * @param mixed  $cacheValue
     * @param int    $ttl
     */
    public static function set($cacheKey, $cacheValue, $ttl)
    {
        if ($ttl <= 0) {
            self::clear($cacheKey);
            return;
        }

        // 设置 Cache 的 Prefix 为我们自己定义的 Prefix
        $cacheInstance  = \Cache::instance();
        $oldCachePrefix = $cacheInstance->getPrefix();
        $cacheInstance->setPrefix(static::getCachePrefix());

        $cacheInstance->set($cacheKey, $cacheValue, $ttl);

        // 恢复 Cache 的 Prefix
        $cacheInstance->setPrefix($oldCachePrefix);
    }

    /**
     * 清除缓存值
     *
     * @param string $cacheKey
     */
    public static function clear($cacheKey)
    {
        // 设置 Cache 的 Prefix 为我们自己定义的 Prefix
        $cacheInstance  = \Cache::instance();
        $oldCachePrefix = $cacheInstance->getPrefix();
        $cacheInstance->setPrefix(static::getCachePrefix());

        $cacheInstance->clear($cacheKey);

        // 恢复 Cache 的 Prefix
        $cacheInstance->setPrefix($oldCachePrefix);

    }

    /**
     * 清除整个 ShareCache
     */
    public static function reset()
    {
        // 设置 Cache 的 Prefix 为我们自己定义的 Prefix
        $cacheInstance  = \Cache::instance();
        $oldCachePrefix = $cacheInstance->getPrefix();
        $cacheInstance->setPrefix(static::getCachePrefix());

        $cacheInstance->reset();

        // 恢复 Cache 的 Prefix
        $cacheInstance->setPrefix($oldCachePrefix);
    }
}