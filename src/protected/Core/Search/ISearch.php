<?php

namespace Core\Search;

/**
 * 通用的搜索接口
 *
 * 网站流量小的时候我们使用普通的 SQL Like 搜索就足够了，但是网站流量如果大了，我们就需要采用专门的搜索服务了，
 * 比如 Sphinx, Solr, Elastics Search
 *
 * 定义统一的搜索接口方便我们将来切换到专有的搜索服务器上
 *
 * @author QiangYu
 */
interface ISearch
{

    /**
     * 搜索服务初始化
     *
     * @param array $paramArray 在这里传递任何你需要的初始化参数
     *
     * @return bool  成功初始化返回 true ，失败返回 false
     */
    public function init($paramArray);


    /**
     * 实现搜索
     *
     * @param string       $searchType    搜索类型，比如你是搜索 goods 还是 order
     * @param string       $fieldSelector 选择需要哪些个字段，比如 'g.goods_name, g.price'
     * @param string|array $searchParam   搜索需要提供的参数
     * @param int          $offset
     * @param int          $limit
     * @param string       $groupByStr    比如 age,gender
     *
     * $searchParam 格式为  array(array(), array(), array(), ...)，所有的条件之间为 AND 逻辑，目前不支持 OR 逻辑
     *
     * 里面每一个搜索条件的格式为  array($field, $compare, $value) ，其中 $compare 为比较符，包括 '=' ,'>','<','<>','like'
     * 例如 array('age' ,'>', 10), array('name', 'like' ,'xxx')
     *
     * @param array        $orderByParam  排序字段，格式 array(array('age','asc'), array('id', 'desc'))
     *
     * @return mixed  失败返回 false，成功返回搜索的结果，如果结果为空则返回 null 或者 空的 array()
     */
    public function search(
        $searchType,
        $fieldSelector,
        $searchParam,
        $orderByParam,
        $offset,
        $limit,
        $groupByStr = null
    );

    /**
     * 返回这个搜索条件下总计有多少结果
     *
     * @param int          $searchType
     * @param string|array $searchParam
     * @param string       $groupByStr    比如 age,gender
     *
     * @return int
     */
    public function count($searchType, $searchParam, $groupByStr = null);
}

