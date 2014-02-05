<?php
/**
 * 采用普通 SQL 实现的搜索，数据量不大的时候可以使用，
 * 当网站数据量海量之后我们需要考虑采用专门的搜索服务器
 *
 * @author QiangYu
 */

namespace Core\Search\Sql;

use Core\Search\AbstractSearch;
use Core\Search\ISearch;

class SqlSearch extends AbstractSearch
{
    /**
     * 构建 SQL 查询条件
     *
     * @param $searchParam
     */
    public static function buildSqlCondArray($searchParam)
    {
        $condArray = array();
        foreach ($searchParam as $paramItem) {

            // 参数数组
            if (is_array($paramItem)) {

                // 3 个参数的，比如 array('name','like','xxxxx')
                if (count($paramItem) == 3
                    && in_array($paramItem[1], array('like', '=', '>', '>=', '<', '<=', '<>'))
                ) {
                    $condArray[] = array(
                        $paramItem[0] . ' ' . $paramItem[1] . ' ? ',
                        ('like' == $paramItem[1] ? '%' . $paramItem[2] . '%' : $paramItem[2])
                    );
                    continue;
                }

                // 我们允许直接的 Filter 查询方式，比如 array('age > ?', 10)
                // 我们允许用户直接输入查询条件，比如  array('age > 10')
                $condArray[] = $paramItem;
                continue;
            }

            // 非法的查询条件
            printLog(print_r($paramItem, true) . ' is illegal', __CLASS__, \Core\Log\Base::ERROR);

            global $f3;
            if ($f3->get('DEBUG')) {
                throw new \InvalidArgumentException('invalid searchParam [' . print_r($paramItem, true) . ']');
            }
        }
        return $condArray;
    }

    /**
     * 构造 SQL 排序字符串
     *
     * @param $orderByParam
     *
     * @return null|string
     */
    public static function buildSqlOrderByStr($orderByParam)
    {
        $orderByStr = null;
        if ($orderByParam) {
            $orderByArray = array();
            foreach ($orderByParam as $orderByItem) {
                $orderByArray[] = implode(' ', $orderByItem);
            }
            $orderByStr = implode(',', $orderByArray);
        }
        return $orderByStr;
    }

    private function loadModule($searchType)
    {

        // 检查对应的搜索模块是否存在
        $filePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . $searchType . '.php';
        if (!file_exists($filePath)) {
            // 没有对应的搜索模块，失败
            printLog($searchType . ' does not exist', __CLASS__, \Core\Log\Base::ERROR);
            return false;
        }
        require_once($filePath);

        // 生成搜索模块
        $searchType   = __NAMESPACE__ . '\\' . $searchType;
        $searchModule = new $searchType;
        if (!$searchModule || !$searchModule instanceof ISearch) {
            throw new \InvalidArgumentException($searchType . ' is an invalid ISearch');
        }

        return $searchModule;
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
        $searchModule = $this->loadModule($searchType);
        if (!$searchModule) {
            return false;
        }

        return $searchModule->search(null, $fieldSelector, $searchParam, $orderByParam, $offset, $limit, $groupByStr);
    }

    public function count($searchType, $searchParam, $groupByStr = null)
    {
        $searchModule = $this->loadModule($searchType);
        if (!$searchModule) {
            return 0;
        }

        return $searchModule->count(null, $searchParam, $groupByStr);
    }
}