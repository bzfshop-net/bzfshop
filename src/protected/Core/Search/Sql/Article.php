<?php
/**
 *
 * 搜索 article => a，任何 article 表的字段都可以作为搜索的条件
 *
 *
 * @author QiangYu
 */

namespace Core\Search\Sql;

use Core\Helper\Utility\Route as RouteHelper;

class Article extends BaseSqlSearch
{

    public function __construct()
    {
        $this->searchTable = array('article' => 'a');
    }

}