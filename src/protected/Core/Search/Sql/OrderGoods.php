<?php
/**
 * 搜索 order_goods => og 表，任何字段都可以作为搜索条件
 *
 * @author QiangYu
 *
 */

namespace Core\Search\Sql;

class OrderGoods extends BaseSqlSearch
{
    public function __construct()
    {
        $this->searchTable = array('order_goods' => 'og');
    }

}