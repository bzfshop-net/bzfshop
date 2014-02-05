<?php
/**
 * 搜索商品属性，我们和 Goods 表做 INNER JOIN，这样可以使用 Goods 的查询条件来操作
 *
 * @author QiangYu
 */

namespace Core\Search\Sql;

use Core\Modal\SqlMapper as DataMapper;

class GoodsAttrGoods extends Goods
{

    public function __construct()
    {
        $this->searchTable = DataMapper::tableName('goods_attr') . ' as ga INNER JOIN ' . DataMapper::tableName('goods')
            . ' as g on ga.goods_id = g.goods_id';
    }

}