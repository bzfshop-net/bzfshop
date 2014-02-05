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
                        if (empty($searchParam[1]) || empty($searchParam[2])) {
                            break;
                        }

                        $goodsTypeService = new GoodsTypeService();

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
                                $goodsAttr = $goodsTypeService->loadGoodsAttrById($goodsAttrId);
                                // 无效的属性，返回
                                if ($goodsAttr->isEmpty()) {
                                    continue;
                                }
                                $goodsAttrItemCond[]
                                    = array("ga.attr_item_value = '" . $goodsAttr['attr_item_value'] . "'");
                            }

                            if (!empty($goodsAttrItemCond)) {
                                $queryCondArray[] = QueryBuilder::buildAndFilter(array(
                                    array('ga.attr_item_id = ' . $attrItemId),
                                    QueryBuilder::buildOrFilter($goodsAttrItemCond)
                                ));
                            }
                        }

                        // 合并查询参数
                        if (count($queryCondArray) > 1) {
                            $queryCondArray = QueryBuilder::buildAndFilter($queryCondArray);
                        } else {
                            // 取得第一个记录
                            $queryCondArray = $queryCondArray[0];
                        }

                        // 构造子查询
                        $this->searchTable =
                            DataMapper::tableName('goods') . ' as g INNER JOIN '
                            . '(select distinct(goods_id) from ' . DataMapper::tableName('goods_attr')
                            . ' as ga where ' . array_shift($queryCondArray) . ' ) as ga on g.goods_id = ga.goods_id';

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