<?php
/**
 * 搜索商品, 我们可以根据商品的属性做精细的过滤
 *
 * 注意，这里为了提高效率，我们采用了子查询
 *
 * @author QiangYu
 */

namespace Core\Search\Sql;

use Core\Helper\Utility\QueryBuilder;
use Core\Modal\SqlMapper as DataMapper;
use Core\Service\Goods\Type as GoodsTypeService;

class GoodsGoodsAttr extends Goods
{

    protected function prepareSearchParam($searchParamArray)
    {

        if (!is_array($searchParamArray)) {
            throw new \InvalidArgumentException('searchParam illegal : ' . var_export($searchParamArray, true));
        }

        // 调用父类先处理
        $searchParamArray = parent::prepareSearchParam($searchParamArray);

        $resultParamArray = array();
        foreach ($searchParamArray as $searchParam) {

            $addParam = true;

            if (is_array($searchParam) && count($searchParam) == 3) {
                switch ($searchParam[0]) {

                    /** 根据过滤规则，我们构造子查询
                     *  结构 array('ga.filter', '123.321.45', '100_20.34.67_78')
                     *  其中 123.321.45 为 attr_item_id
                     *  100_20.34.67_78 为 goods_attr_id 对应的值
                     */
                    case 'ga.filter':

                        // 不加入这个参数
                        $addParam = false;

                        // 没有值，不需要过滤
                        $trimSearchParam2 = trim(str_replace('.', '', $searchParam[2])); // 有可能没有值，全部为点 "..."
                        if (empty($searchParam[1]) || empty($searchParam[2]) || empty($trimSearchParam2)) {
                            break;
                        }

                        $goodsTypeService = new GoodsTypeService();

                        // 构造子查询
                        $queryJoinTable = '';
                        $firstJoinTable = '';
                        $queryCondArray = array();

                        // 构造子查询
                        $attrItemIdArray     = explode('.', $searchParam[1]);
                        $goodsAttrIdStrArray = explode('.', $searchParam[2]);

                        $count = min(count($attrItemIdArray), count($goodsAttrIdStrArray));
                        for ($index = 0; $index < $count; $index++) {
                            $attrItemId       = abs(intval($attrItemIdArray[$index]));
                            $goodsAttrIdArray = explode('_', $goodsAttrIdStrArray[$index]);
                            // 跳过无效值
                            if ($attrItemId <= 0 || empty($goodsAttrIdArray)) {
                                continue;
                            }

                            $goodsAttrItemCond = array();
                            foreach ($goodsAttrIdArray as $goodsAttrId) {
                                $goodsAttrId = abs(intval($goodsAttrId));
                                $goodsAttr   = $goodsTypeService->loadGoodsAttrById($goodsAttrId);
                                // 无效的属性，返回
                                if ($goodsAttr->isEmpty()) {
                                    continue;
                                }
                                $goodsAttrItemCond[]
                                    = array("attr_item_value = ?", $goodsAttr['attr_item_value']);
                            }

                            if (!empty($goodsAttrItemCond)) {
                                $condArray = QueryBuilder::buildAndFilter(
                                    array(
                                        array('attr_item_id = ?', $attrItemId),
                                        QueryBuilder::buildOrFilter($goodsAttrItemCond)
                                    )
                                );

                                $tmpTableName   = 'ga' . $index;
                                $tmpTable       = '(select distinct(goods_id) from '
                                    . DataMapper::tableName('goods_attr')
                                    . ' where ' . array_shift($condArray) . ') as ' . $tmpTableName;
                                $queryCondArray = array_merge($queryCondArray, $condArray);

                                if (empty($queryJoinTable)) {
                                    $queryJoinTable = $tmpTable;
                                    $firstJoinTable = $tmpTableName;
                                } else {
                                    $queryJoinTable .= ' INNER JOIN '
                                        . $tmpTable . ' on '
                                        . $firstJoinTable . '.goods_id = '
                                        . $tmpTableName . '.goods_id ';
                                }
                            }
                        }

                        // 构造子查询
                        $this->searchTable =
                            DataMapper::tableName('goods') . ' as g INNER JOIN '
                            . '(select distinct(' . $firstJoinTable . '.goods_id) from ('
                            . $queryJoinTable . ')) as ga on g.goods_id = ga.goods_id';

                        /**
                         * 这里是一个很 tricky 的构造查询的方法
                         *
                         * 我们不想拼接 SQL 语句，比如 attr_item_value = $attr_item_value，
                         * 而是采用 array('attr_item_value = ?', $attr_item_value)，这样可以 SQL Bind 避免 SQL 注入
                         *
                         * 由于前面的 子查询带了很多 ? 查询，所以我们需要把参数值 unshift 到第一个的位置
                         *
                         */
                        // 头部压入一个空条件
                        array_unshift($queryCondArray, '1=1');
                        // 把这个参数压入到头部
                        array_unshift($resultParamArray, $queryCondArray);

                        break;

                    default:
                        break;
                }
            }
            //  是否加入参数
            if ($addParam) {
                $resultParamArray[] = $searchParam;
            }
        }

        return $resultParamArray;
    }

}