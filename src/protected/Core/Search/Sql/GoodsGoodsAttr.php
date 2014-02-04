<?php
/**
 *
 * 搜索 goods => g，任何 goods 表的字段都可以作为搜索的条件
 *
 * $searchType: Goods
 * $searchParam: array(
 *      array('category_id' ,'=', 5120), // 取得这个分类以及下面所有子分类包含的商品
 *      array('suppliers_id' ,'=', 120), // 供货商 id
 *      array('goods_name' ,'like', 'aaaa'),
 * )
 *
 * @author QiangYu
 */

namespace Core\Search\Sql;

use Core\Modal\SqlMapper as DataMapper;

class GoodsGoodsAttr extends Goods
{

    public function __construct()
    {
        $this->searchTable = DataMapper::tableName('goods') . ' as g INNER JOIN ' . DataMapper::tableName('goods_attr')
            . ' as ga on g.goods_id = ga.goods_id';
    }

}