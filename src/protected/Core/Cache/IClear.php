<?php
/**
 * @author QiangYu
 *
 * 缓存清除接口
 */

namespace Core\Cache;

interface  IClear
{

    /**
     * 清除所有缓存
     */
    public function clearAllCache();

    /**
     * 清除首页的缓存
     */
    public function clearHomePage();

    /**
     * 清除商品分类列表的缓存
     */
    public function clearGoodsCategory();

    /**
     * 清除单个商品对应的缓存
     *
     * @param int $goods_id
     *
     */
    public function clearGoodsCacheById($goods_id);

    /**
     * 清除文章的缓存
     *
     * @param $article_id
     *
     * @return mixed
     */
    public function clearArticleCacheById($article_id);
}
