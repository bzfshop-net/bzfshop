<?php

/**
 *
 * @author QiangYu
 *
 * 给 API 使用的商品操作
 *
 * */

namespace Core\Service\Api;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Gallery as GoodsGalleryService;

class Goods extends \Core\Service\BaseService
{

    /**
     * 计算有多少商品通过 API 返回
     *
     * @param  array $queryCondArray  查询条件
     * @param int    $ttl             缓存时间
     *
     * @return int
     */
    public function countGoodsGalleryPromote($queryCondArray = null, $ttl = 0)
    {
        // 查询商品信息
        $condArray   = array();
        $condArray[] = array('g.goods_id = gp.goods_id');

        $formQuery                  = array();
        $formQuery['is_delete']     = 0;
        $formQuery['is_on_sale']    = 1;
        $formQuery['is_alone_sale'] = 1;

        if (!empty($queryCondArray)) {
            $condArray = array_merge($condArray, $queryCondArray);
        }
        $condArray = array_merge($condArray, QueryBuilder::buildQueryCondArray($formQuery));

        return $this->_countArray(
            array('goods' => 'g', 'goods_promote' => 'gp'), // tables
            $condArray, // filter
            null,
            $ttl
        ); // options
    }

    /**
     * 取得 goods 和 goods_promote 的 join 结果，同时取得 goods 对应的所有商品
     * 由于这个函数的代价非常大（几千个商品，上万个图片），所以我们一定要做缓存
     *
     * @return array  array('goods' => $goodsArray, 'goodsIdToGalleryArray' => $goodsIdToGalleryArray)
     *
     * @param array  $queryCondArray 查询条件
     * @param string $sort
     * @param  int   $offset
     * @param  int   $limit
     * @param int    $ttl            缓存时间
     *
     * @return array
     */
    public function fetchGoodsGalleryPromote($queryCondArray, $sort, $offset, $limit, $ttl = 0)
    {
        // 首先做参数验证
        $validator = new Validator(array('sort' => $sort, 'offset' => $offset, 'limit' => $limit, 'ttl' => $ttl));
        $offset    = $validator->digits()->min(0)->validate('offset');
        $limit     = $validator->digits()->min(0)->validate('limit');
        $ttl       = $validator->digits()->min(0)->validate('ttl');
        $sort      = $validator->validate('sort');

        // 查询商品信息
        $condArray   = array();
        $condArray[] = array('g.goods_id = gp.goods_id');

        $formQuery                  = array();
        $formQuery['is_delete']     = 0;
        $formQuery['is_on_sale']    = 1;
        $formQuery['is_alone_sale'] = 1;

        if (!empty($queryCondArray)) {
            $condArray = array_merge($condArray, $queryCondArray);
        }
        $condArray = array_merge($condArray, QueryBuilder::buildQueryCondArray($formQuery));

        $goodsArray = $this->_fetchArray(
            array('goods' => 'g', 'goods_promote' => 'gp'), // tables
            // fields
            'g.system_tag_list, g.goods_name, g.goods_name_short, g.brand_id, g.goods_number, g.market_price, g.shop_price, g.cat_id'
            . ', g.sort_order, g.goods_brief, g.seo_keyword, g.goods_notice , g.virtual_buy_number, g.user_pay_number'
            . ', gp.* ',
            $condArray, // filter
            array('order' => $sort),
            $offset,
            $limit,
            $ttl
        ); // options

        // 如果没有数据就退出
        if (empty($goodsArray)) {
            return array();
        }

        // 查询商品图片
        $goodsIdArray = array();
        foreach ($goodsArray as $goodsItem) {
            $goodsIdArray[] = $goodsItem['goods_id'];
        }

        $goodsGalleryService = new GoodsGalleryService();
        $goodsGalleryArray   = $goodsGalleryService->fetchGoodsGalleryArrayByGoodsIdArray($goodsIdArray, $ttl);

        // 建立 goods_id --> goods_gallery 的反查表
        $goodsIdToGalleryArray = array();
        foreach ($goodsGalleryArray as $goodsGalleryItem) {
            if (!isset($goodsIdToGalleryArray[$goodsGalleryItem['goods_id']])) {
                $goodsIdToGalleryArray[$goodsGalleryItem['goods_id']] = array();
            }
            $goodsIdToGalleryArray[$goodsGalleryItem['goods_id']][] = $goodsGalleryItem;
        }

        return array('goods' => $goodsArray, 'goodsIdToGalleryArray' => $goodsIdToGalleryArray);
    }

}
