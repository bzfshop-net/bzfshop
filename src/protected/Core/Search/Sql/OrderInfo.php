<?php
/**
 * 搜索 order_info => oi 表，任何字段都可以作为搜索条件
 *
 * @author QiangYu
 *
 */

namespace Core\Search\Sql;

class OrderInfo extends BaseSqlSearch
{
    public function __construct()
    {
        $this->searchTable = array('order_info' => 'oi');
    }

}