<?php

/**
 *
 * @author QiangYu
 *
 * 商品的图像操作类
 *
 * */

namespace Core\Service\Goods;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;

class Gallery extends \Core\Service\BaseService
{

    /**
     * 根据 ID 取得 goods_gallery 记录
     *
     * @param  int $id
     * @param int  $ttl
     *
     * @return object
     */
    public function loadGoodsGalleryById($id, $ttl = 0)
    {
        return $this->_loadById('goods_gallery', 'img_id = ?', $id, $ttl);
    }

    /**
     * 取得商品的图像集合
     *
     * @return array  图像集合 array(array(图片1), array(图片2))
     *
     * @param int $goodsId 商品的 ID 号
     * @param int $ttl     缓存时间
     */
    public function fetchGoodsGalleryArrayByGoodsId($goodsId, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('goodsId' => $goodsId));
        $goodsId   = $validator->required()->digits()->validate('goodsId');
        $this->validate($validator);

        return $this->_fetchArray(
            'goods_gallery',
            '*',
            array(array('goods_id =?', $goodsId)),
            array('order' => 'img_sort_order desc, img_id asc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 给出一组商品 ID，取得所有商品的图片，
     * 这是一个大查询，可能会很多数据，之所以加这个大查询的目的是，
     * 我们宁可要一个几百条记录的大查询，也不要几百个一条记录的小查询
     *
     * @return array  图像集合 array(array(图片1), array(图片2))
     *
     * @param array $goodsIdArray 商品的 ID 数组
     * @param int   $ttl          缓存时间
     */
    public function fetchGoodsGalleryArrayByGoodsIdArray(array $goodsIdArray, $ttl = 0)
    {
        if (!is_array($goodsIdArray) || empty($goodsIdArray)) {
            throw new \InvalidArgumentException('goodsIdArray must be an array not empty');
        }

        // 参数验证
        $validator = new Validator(array('ttl' => $ttl));
        $ttl       = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        $dataMapper  = new DataMapper('goods_gallery');
        $sqlInClause = QueryBuilder::buildInCondition('goods_id', $goodsIdArray, \PDO::PARAM_INT);

        return $dataMapper->find(
            array($sqlInClause),
            array('order' => 'goods_id asc , img_sort_order desc, img_id asc'),
            $ttl
        );
    }

}
