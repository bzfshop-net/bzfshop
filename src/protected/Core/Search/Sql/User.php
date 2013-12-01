<?php
/**
 *
 * 搜索 users => u，任何 user 表的字段都可以作为搜索的条件
 *
 * $searchType: User
 * $searchParam: array(
 *      array('user_id' ,'=', 5120), // 取得这个分类以及下面所有子分类包含的商品
 *      array('email' ,'like', 'aaaa@gmail.com'),
 * )
 *
 * @author QiangYu
 */

namespace Core\Search\Sql;

class User extends BaseSqlSearch
{

    public function __construct()
    {
        $this->searchTable = array('users' => 'u');
    }

}