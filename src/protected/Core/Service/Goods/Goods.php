<?php

/**
 *
 * @author QiangYu
 *
 * 基本商品操作
 *
 * */

namespace Core\Service\Goods;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Validator;

class Goods extends \Core\Service\BaseService
{

    /**
     * 根据商品 id 取得商品信息
     *
     * @return object 商品的详细信息
     *
     * @param int $id  商品数字 ID
     * @param int $ttl 缓存时间
     */
    public function loadGoodsById($id, $ttl = 0)
    {
        return $this->_loadById('goods', 'goods_id=?', $id, $ttl);
    }


    /**
     * 取得商品的团购设置信息
     *
     * @param  int $goodsId  商品ID
     * @param int  $ttl      缓存时间
     *
     * @return object
     */
    public function loadGoodsTeamByGoodsId($goodsId, $ttl = 0)
    {
        return $this->_loadById('goods_team', 'goods_id=?', $goodsId, $ttl);
    }

    /**
     * 取得商品的团购设置信息
     *
     * @param  int $goodsId  商品ID
     * @param int  $ttl      缓存时间
     *
     * @return object
     */
    public function loadGoodsPromoteByGoodsId($goodsId, $ttl = 0)
    {
        return $this->_loadById('goods_promote', 'goods_id=?', $goodsId, $ttl);
    }

    /**
     * 取得相互关联的商品列表
     *
     * @return array 商品列表
     *
     * @param int $goods_id  商品数字 ID
     * @param int $ttl       缓存时间
     */
    public function fetchLinkGoodsArray($goods_id, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('goods_id' => $goods_id));
        $goods_id  = $validator->required()->digits()->min(1)->validate('goods_id');
        $this->validate($validator);

        return $this->_fetchArray(
            array('goods' => 'g', 'link_goods' => 'lg'),
            'g.*, lg.link_id',
            array(array('g.goods_id = lg.link_goods_id and lg.goods_id = ?', $goods_id)),
            array('order' => 'lg.link_id desc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 取得简单的 link_goods 记录
     *
     * @param  int $goods_id
     * @param int  $ttl
     *
     * @return array
     */
    public function fetchSimpleLinkGoodsArray($goods_id, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('goods_id' => $goods_id));
        $goods_id  = $validator->required()->digits()->min(1)->validate('goods_id');
        $this->validate($validator);

        return $this->_fetchArray(
            'link_goods',
            '*',
            array(array('goods_id = ?', $goods_id)),
            array('order' => 'link_id desc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 取得商品被谁关联了
     *
     * @param     $link_goods_id
     * @param int $ttl
     *
     * @return array
     */
    public function fetchLinkByGoodsArray($link_goods_id, $ttl = 0)
    {
        // 参数验证
        $validator     = new Validator(array('link_goods_id' => $link_goods_id));
        $link_goods_id = $validator->required()->digits()->min(1)->validate('link_goods_id');
        $this->validate($validator);

        return $this->_fetchArray(
            array('goods' => 'g', 'link_goods' => 'lg'),
            'g.*, lg.link_id',
            array(array('g.goods_id = lg.goods_id and lg.link_goods_id = ?', $link_goods_id)),
            array('order' => 'lg.link_id desc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 取得一组商品的列表
     *
     * @return array 商品列表
     *
     * @param array $condArray 查询条件数组，例如：
     *                         array(
     *                         array('supplier_id = ?', $supplier_id)
     *                         array('is_on_sale = ?', 1)
     *                         array('supplier_price > ? or supplier_price < ?', $priceMin, $priceMax)
     * )
     *
     * @param int   $offset    用于分页的开始 >= 0
     * @param int   $limit     每页多少条
     * @param int   $ttl       缓存多少时间
     *
     */
    public function fetchGoodsArray(array $condArray, $offset = 0, $limit = 10, $ttl = 0)
    {
        return $this->_fetchArray('goods', '*', $condArray, array('order' => 'goods_id desc'), $offset, $limit, $ttl);
    }

    /**
     * 根据 goods_id 数组取得一组商品
     *
     * @param array $goodsIdArray
     * @param int   $ttl
     *
     * @return array
     */
    public function fetchGoodsArrayByGoodsIdArray(array $goodsIdArray, $ttl = 0)
    {
        return $this->_fetchArray(
            'goods',
            '*',
            array(array(QueryBuilder::buildInCondition('goods_id', $goodsIdArray))),
            null,
            0,
            0,
            $ttl
        );
    }

    /**
     * 取得一组商品的数目，用于分页
     *
     * @return int 查询条数
     *
     * @param array $condArray 查询条件数组，例如：
     *                         array(
     *                         array('supplier_id = ?', $supplier_id)
     *                         array('is_on_sale = ?', 1)
     *                         array('supplier_price > ? or supplier_price < ?', $priceMin, $priceMax)
     * )
     *
     * @param int   $ttl       缓存多少时间
     *
     */
    public function countGoodsArray(array $condArray, $ttl = 0)
    {
        return $this->_countArray('goods', $condArray, null, $ttl);
    }

}
