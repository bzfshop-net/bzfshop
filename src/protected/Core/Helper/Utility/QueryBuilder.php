<?php

/**
 * @author QiangYu
 *
 * 用于 build 查询语句
 *
 * */

namespace Core\Helper\Utility;

final class QueryBuilder
{

    /**
     * 创建查询 filter
     *
     * @return array 用于 DataModal 查询的 filter
     *
     * @param array $condArray 查询条件数组，例如：
     *                         array(
     *                         array('supplier_id = ?', $supplier_id)
     *                         array('is_on_sale = ?', 1)
     *                         array('supplier_price > ? or supplier_price < ?', $priceMin, $priceMax)
     *                         )
     *
     * @param string $glue 连接条件， AND  或者 OR
     *
     */
    public static function buildFilter(array $condArray, $glue = 'AND')
    {

        if (empty($condArray)) {
            return null;
        }

        $filterArray = array();
        $paramArray  = array();

        foreach ($condArray as $cond) {
            $filterArray[] = array_shift($cond);
            $paramArray    = array_merge($paramArray, $cond);
        }

        $filterStr = '(' . implode(') ' . $glue . ' (', $filterArray) . ')';

        // 把 filterStr 推到第一个位置，组成最后的 $filter 查询数组
        array_unshift($paramArray, $filterStr);
        return $paramArray;
    }

    /**
     * 建立 AND 查询条件
     *
     * @param array $condArray
     * @return array
     */
    public static function buildAndFilter(array $condArray)
    {
        return self::buildFilter($condArray, 'AND');
    }

    /**
     * 建立 OR 查询条件
     *
     * @param array $condArray
     * @return array
     */
    public static function buildOrFilter(array $condArray)
    {
        return self::buildFilter($condArray, 'OR');
    }

    /**
     * 建立SQL的 IN 查询条件，例如： user_id in (123,45,67)  或者 user_name in ('xxxxx', 'wwwwww', 'lalalal')
     *
     * @return string 建立好的查询条件语句
     *
     * @param string $field 字段名，比如 user_id
     * @param array $valueArray 字段的不同取值
     * @param int $valueType 取值的类型，比如  \PDO::PARAM_INT , \PDO::PARAM_STR
     */
    public static function buildInCondition($field, array $valueArray, $valueType = \PDO::PARAM_STR)
    {
        $glue      = ",";
        $leftSign  = "(";
        $rightSign = ")";

        // 字符串，需要用 '' 包含
        if (\PDO::PARAM_STR == $valueType || \PDO::PARAM_NULL == $valueType) {
            $glue      = "','";
            $leftSign  = "('";
            $rightSign = "')";
        }

        return ' ' . $field . ' in ' . $leftSign . implode($glue, $valueArray) . $rightSign . ' ';
    }

    /**
     * 建立SQL的 NOT IN 查询条件，例如： user_id not in (123,45,67)  或者 user_name not in ('xxxxx', 'wwwwww', 'lalalal')
     *
     * @return string 建立好的查询条件语句
     *
     * @param string $field 字段名，比如 user_id
     * @param array $valueArray 字段的不同取值
     * @param int $valueType 取值的类型，比如  \PDO::PARAM_INT , \PDO::PARAM_STR
     */
    public static function buildNotInCondition($field, array $valueArray, $valueType = \PDO::PARAM_STR)
    {
        $glue      = ",";
        $leftSign  = "(";
        $rightSign = ")";

        // 字符串，需要用 '' 包含
        if (\PDO::PARAM_STR == $valueType || \PDO::PARAM_NULL == $valueType) {
            $glue      = "','";
            $leftSign  = "('";
            $rightSign = "')";
        }

        return ' ' . $field . ' not in ' . $leftSign . implode($glue, $valueArray) . $rightSign . ' ';
    }

    /**
     * 根据参数建立查询条件数组，方便组合各种复杂的查询条件
     * 例如： $formQueryArray = array('user_id' => 100, 'user_name' =>'qiangyu', 'age' => array(20, 30), 'age' => array('>', 20))
     * 建立的查询数组为 return array(
     *          array('user_id = ?', 100),
     *          array('user_name like ?' , '%qiangyu%'),
     *          array('age > ?  and age < ?', 20, 30)
     * )
     * 返回的查询数组可以和别的条件继续组合，成为一个复杂的查询
     *
     * @return array
     *
     * @param array $formQueryArray 查询数组
     *
     * * */
    public static function buildQueryCondArray(array $formQueryArray)
    {
        $condArray = array();

        foreach ($formQueryArray as $queryKey => $queryValue) {

            // 跳过 null
            if (is_null($queryValue)) {
                continue;
            }

            // string 用 like 查询
            if (is_string($queryValue) && !empty($queryValue)) {
                $condArray[] = array($queryKey . ' like ? ', '%' . $queryValue . '%');
                continue;
            }

            // int 用 = 查询
            if (is_int($queryValue)) {
                $condArray[] = array($queryKey . ' = ? ', $queryValue);
                continue;
            }

            // 数组，比较查询 或者 区间查询
            if (is_array($queryValue)) {

                // 我们支持  age => array('>', 100) 这种写法
                if (in_array($queryValue[0], array('=', '>', '>=', '<', '<=', '<>'))) {
                    if (null === $queryValue[1] || (is_string($queryValue[1]) && empty($queryValue[1]))) {
                        continue;
                    }
                    $condArray[] = array($queryKey . ' ' . $queryValue[0] . ' ? ', $queryValue[1]);
                    continue;
                }

                $minValue = $queryValue[0];
                $maxValue = $queryValue[1];

                if (null != $minValue) {
                    $condArray[] = array($queryKey . ' >= ? ', intval($minValue));
                }

                if (null != $maxValue) {
                    $condArray[] = array($queryKey . ' < ? ', intval($maxValue));
                }

                continue;
            }

        }

        return $condArray;
    }

    /**
     * 构建搜索查询参数数组
     *
     * @param array $searchFormQueryArray
     *
     * 例如： $searchFormQueryArray = array('user_id' => 100, 'user_name' =>'qiangyu', 'age' => array(20, 30), 'age' => array('>', 20))
     * 建立的查询数组为 return array(
     *          array('user_id', '=', 100),
     *          array('user_name', 'like' , '%qiangyu%'),
     *
     *          array('age', '>=',  20)
     *          array('age', '<', 30)
     *
     *          array('age', '>', 20)
     * )
     *
     * @return array
     */
    public static function buildSearchParamArray(array $searchFormQueryArray)
    {
        $searchParamArray = array();

        foreach ($searchFormQueryArray as $queryKey => $queryValue) {

            // 跳过 null
            if (is_null($queryValue)) {
                continue;
            }

            // string 用 like 查询
            if (is_string($queryValue) && !empty($queryValue)) {
                $searchParamArray[] = array($queryKey, 'like', $queryValue);
                continue;
            }

            // int 用 = 查询
            if (is_int($queryValue)) {
                $searchParamArray[] = array($queryKey, '=', $queryValue);
                continue;
            }

            // 数组，比较查询 或者 区间查询
            if (is_array($queryValue)) {
                // 我们支持  age => array('>', 100) 这种写法
                if (in_array($queryValue[0], array('=', '>', '>=', '<', '<=', '<>'))) {
                    if (null === $queryValue[1] || (is_string($queryValue[1]) && empty($queryValue[1]))) {
                        continue;
                    }
                    $searchParamArray[] = array_merge(array($queryKey), $queryValue);
                    continue;
                }

                $minValue = $queryValue[0];
                $maxValue = $queryValue[1];

                if (null != $minValue) {
                    $searchParamArray[] = array($queryKey, '>=', intval($minValue));
                }

                if (null != $maxValue) {
                    $searchParamArray[] = array($queryKey, '<', intval($maxValue));
                }

                continue;
            }
        }

        return $searchParamArray;
    }

    /**
     * 商品搜索的时候用于过滤某些系统的商品
     *
     * @param   array $systemArray
     * @param string $goodsTableAlias
     *
     * @return string
     */
    public static function buildGoodsFilterForSystem($systemArray, $goodsTableAlias = null)
    {
        if (empty($systemArray) || !is_array($systemArray)) {
            return '';
        }

        if (empty($goodsTableAlias)) {
            $goodsTableAlias = 'system_tag_list';
        } else {
            $goodsTableAlias = $goodsTableAlias . '.system_tag_list';
        }

        $condArray = array();
        foreach ($systemArray as $system) {
            $condArray[] = $goodsTableAlias . " like '%" . Utils::makeTagString(array($system)) . "%'";
        }

        return implode(' or ', $condArray);
    }

    /**
     * 用于订单查询时候过滤来自不同系统的订单
     *
     * @param  array $systemArray
     * @param string $orderTableAlias
     *
     * @return string
     */
    public static function buildOrderFilterForSystem($systemArray, $orderTableAlias = null)
    {
        if (empty($systemArray) || !is_array($systemArray)) {
            return '';
        }

        if (empty($orderTableAlias)) {
            $orderTableAlias = 'system_id';
        } else {
            $orderTableAlias = $orderTableAlias . '.system_id';
        }

        $condArray = array();
        foreach ($systemArray as $system) {
            $condArray[] = $orderTableAlias . " = '" . $system . "'";
        }

        return implode(' or ', $condArray);
    }
}