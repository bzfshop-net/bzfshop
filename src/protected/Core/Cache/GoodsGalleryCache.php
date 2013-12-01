<?php
/**
 * 商品信息的缓存
 *
 * 我们这里使用 ShareCache ，这样可以在所有的系统之间共用信息
 *
 * @author QiangYu
 *
 */

namespace Core\Cache;


use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Service\Goods\Gallery as GoodsGalleryService;

final class GoodsGalleryCache
{

    protected static function makeGoodsGalleryCacheId($goods_id)
    {
        return md5(__FILE__ . '//' . __METHOD__) . '_' . $goods_id;
    }

    /**
     * 从缓存中取得商品的图片集
     *
     * @param int $goods_id
     */
    public static function getGoodsGallery($goods_id)
    {
        // 参数不对，没有东西可以输出
        if ($goods_id <= 0) {
            throw new \InvalidArgumentException('goods_id [' . $goods_id . '] invalid');
        }

        $cacheId = static::makeGoodsGalleryCacheId($goods_id);

        if ($goodsGallery = ShareCache::get($cacheId)) {
            goto out;
        }

        // 优化，别老不断生成新的对象
        static $goodGalleryService = null;
        if (!$goodGalleryService) {
            $goodGalleryService = new GoodsGalleryService();
        }

        $goodsGallery = $goodGalleryService->fetchGoodsGalleryArrayByGoodsId($goods_id);

        // 非空，缓存
        if (!empty($goodsGallery)) {
            // 缓存一天
            ShareCache::set($cacheId, $goodsGallery, 86400);
        }

        out:
        return $goodsGallery;
    }

    /**
     * 取得商品的 thumbImage，并且放入缓存，提高效率
     *
     * @param int $goods_id
     *
     * @return string
     */
    public static function getGoodsThumbImageUrl($goods_id)
    {

        // 参数不对，没有东西可以输出
        if ($goods_id <= 0) {
            return '';
        }

        $goodsGallery = self::getGoodsGallery($goods_id);

        if (empty($goodsGallery)) {
            return '';
        }

        return RouteHelper::makeImageUrl($goodsGallery[0]['thumb_url']);
    }


    /**
     * 取得商品的头图 url
     *
     * @param int $goods_id
     *
     * @return string
     */
    public static function getGoodsHeadImageUrl($goods_id)
    {

        // 参数不对，没有东西可以输出
        if ($goods_id <= 0) {
            return '';
        }

        $goodsGallery = self::getGoodsGallery($goods_id);

        if (empty($goodsGallery)) {
            return '';
        }

        return RouteHelper::makeImageUrl($goodsGallery[0]['img_url']);
    }

    /**
     * 清除 goods thumb image 的缓存
     *
     * @param int $goods_id
     */
    public static function clearGoodsGallery($goods_id)
    {
        // 参数不对，没有东西可以输出
        if ($goods_id <= 0) {
            return;
        }

        $cacheId = static::makeGoodsGalleryCacheId($goods_id);
        ShareCache::clear($cacheId);
    }
}