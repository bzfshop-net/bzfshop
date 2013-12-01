<?php
/**
 * 搜索  order_goods => og left join order_info => oi left join order_refer => orf 表，任何字段都可以作为搜索条件
 *
 * @author QiangYu
 *
 */

namespace Core\Search\Sql;

use Core\Helper\Utility\Route as RouteHelper;
use Core\Modal\SqlMapper as DataMapper;

class OrderGoodsOrderInfoOrderRefer extends BaseSqlSearch
{
    public function __construct()
    {
        $this->searchTable =
            DataMapper::tableName('order_goods') . ' as og LEFT JOIN ' . DataMapper::tableName('order_info')
            . ' as oi ON og.order_id = oi.order_id LEFT JOIN ' . DataMapper::tableName('order_refer')
            . ' as orf ON og.order_id = orf.order_id';
    }


}