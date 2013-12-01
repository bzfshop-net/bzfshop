<?php
/**
 * 搜索 goods => g left join goods_promote => gp 表，任何字段都可以作为搜索条件
 *
 * @author QiangYu
 *
 */

namespace Core\Search\Sql;

use Core\Helper\Utility\Route as RouteHelper;
use Core\Modal\SqlMapper as DataMapper;

class GoodsGoodsPromote extends Goods
{
    public function __construct()
    {
        $this->searchTable =
            DataMapper::tableName('goods') . ' as g LEFT JOIN ' . DataMapper::tableName('goods_promote')
            . ' as gp on g.goods_id = gp.goods_id';
    }


}