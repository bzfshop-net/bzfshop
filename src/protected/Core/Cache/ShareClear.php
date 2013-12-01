<?php
/**
 * @author QiangYu
 *
 * 通用的缓存清除接口，我们这里主要用于清除 F3 的缓存
 *
 */

namespace Core\Cache;

class ShareClear extends AbstractClear
{
    public function clearAllCache()
    {
        //TODO 由于 Groupon, Shop, Mobile, ... 各自有各自的 Cache，我们需要考虑怎么清除这些独立系统的缓存

        // 清除 f3 的所有缓存
        global $f3;
        $f3->clear('CACHE');

        // 清空共享缓存
        ShareCache::reset();
    }

    public function clearGoodsCacheById($goods_id)
    {
        GoodsGalleryCache::clearGoodsGallery($goods_id);
    }

}
