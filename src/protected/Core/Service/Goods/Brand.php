<?php

/**
 *
 * @author QiangYu
 *
 * 商品品牌管理
 *
 * */

namespace Core\Service\Goods;

use Core\Helper\Utility\QueryBuilder;

class Brand extends \Core\Service\BaseService
{

    /**
     * 加载一个品牌
     *
     * @param int $brand_id
     * @param int $ttl
     * @return object
     */
    public function loadBrandById($brand_id, $ttl = 0)
    {
        return $this->_loadById('brand', 'brand_id = ?', $brand_id, $ttl);
    }

    /**
     * 取得所有品牌的 ID 和 名字
     *
     * @param int $ttl 缓存时间
     *
     * @return array
     */
    public function fetchBrandIdNameArray($ttl = 0)
    {
        return $this->_fetchArray(
            'brand',
            'brand_id, brand_name', // 只取 id 和 名字
            array(),
            array('order' => 'sort_order desc, brand_id desc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 根据 id array 取得一组 brand 的名字
     *
     * @param array $idArray
     * @param int $ttl
     * @return array
     */
    public function fetchBrandArrayByIdArray($idArray, $ttl = 0)
    {
        return $this->_fetchArray(
            'brand',
            '*', // 只取 id 和 名字
            array(array(QueryBuilder::buildInCondition('brand_id', $idArray, \PDO::PARAM_INT))),
            array('order' => 'sort_order desc, brand_id desc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 取得品牌列表
     *
     * @return array 格式 array(array('key'=>'value', 'key'=>'value', ...))
     *
     * @param array $condArray 查询条件
     * @param int $offset 用于分页的开始 >= 0
     * @param int $limit 每页多少条
     * @param int $ttl 缓存多少时间
     */
    public function fetchBrandArray(array $condArray, $offset = 0, $limit = 10, $ttl = 0)
    {
        return $this->_fetchArray(
            'brand',
            '*', // table , fields
            $condArray,
            array('order' => 'brand_id desc'),
            $offset,
            $limit,
            $ttl
        );
    }

    /**
     * 取得商品品牌总数，可用于分页
     *
     * @return int
     *
     * @param array $condArray 查询条件
     * @param int $ttl 缓存多少时间
     */
    public function countBrandArray(array $condArray, $ttl = 0)
    {
        return $this->_countArray('brand', $condArray, null, $ttl);
    }


}
