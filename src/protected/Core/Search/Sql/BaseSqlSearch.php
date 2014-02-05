<?php
/**
 * 基本的 SQL 搜索实现
 *
 * @author QiangYu
 */

namespace Core\Search\Sql;


use Core\Search\AbstractSearch;
use Core\Service\BaseService;

abstract class BaseSqlSearch extends AbstractSearch
{
    protected $searchTable = null;

    /**
     * 对查询参数做一些处理，使得它能适合我们的 sql 搜索
     *
     * @param $searchParamArray
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function prepareSearchParam($searchParamArray)
    {
        return $searchParamArray;
    }

    public function search(
        $searchType,
        $fieldSelector,
        $searchParam,
        $orderByParam,
        $offset,
        $limit,
        $groupByStr = null
    )
    {
        // 构造查询条件
        $searchParam = $this->prepareSearchParam($searchParam);
        $condArray   = SqlSearch::buildSqlCondArray($searchParam);

        $optionArray = array();
        if (!empty($orderByParam)) {
            $optionArray['order'] = SqlSearch::buildSqlOrderByStr($orderByParam);
        }
        if (!empty($groupByStr)) {
            $optionArray['group'] = $groupByStr;
        }

        if (empty($optionArray)) {
            $optionArray = null;
        }

        // 查询
        $baseService = new BaseService();
        return $baseService->_fetchArray(
            $this->searchTable,
            $fieldSelector,
            $condArray,
            $optionArray,
            $offset,
            $limit,
            0
        );
    }

    public function count($searchType, $searchParam, $groupByStr = null)
    {
        // 构造查询条件
        $searchParam = $this->prepareSearchParam($searchParam);
        $condArray   = SqlSearch::buildSqlCondArray($searchParam);

        $optionArray = array();
        if (!empty($groupByStr)) {
            $optionArray['group'] = $groupByStr;
        }

        if (empty($optionArray)) {
            $optionArray = null;
        }

        // 查询结果
        $baseService = new BaseService();
        return $baseService->_countArray($this->searchTable, $condArray, $optionArray, 0);
    }

}