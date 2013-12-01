<?php
/**
 * @author QiangYu
 *
 * 缓存清除接口的抽象实现
 *
 */

namespace Core\Cache;

abstract class AbstractClear implements IClear
{
    public function clearAllCache()
    {
        // do nothing
    }

    public function clearHomePage()
    {
        // do nothing
    }

    public function clearGoodsCategory()
    {
        // do nothing
    }

    public function clearGoodsCacheById($goods_id)
    {
        // do nothing
    }

    public function clearArticleCacheById($article_id)
    {
        // do nothing
    }
}
