<?php
/**
 *
 * 搜索 goods => g，任何 goods 表的字段都可以作为搜索的条件
 *
 * $searchType: Goods
 * $searchParam: array(
 *      array('category_id' ,'=', 5120), // 取得这个分类以及下面所有子分类包含的商品
 *      array('suppliers_id' ,'=', 120), // 供货商 id
 *      array('goods_name' ,'like', 'aaaa'),
 * )
 *
 * @author QiangYu
 */

namespace Core\Search\Sql;


use Core\Helper\Utility\QueryBuilder;
use Core\Service\Goods\Category as GoodsCategoryService;

class Goods extends BaseSqlSearch
{

    public function __construct()
    {
        $this->searchTable = array('goods' => 'g');
    }

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

        if (!is_array($searchParamArray)) {
            throw new \InvalidArgumentException('searchParam illegal : ' . var_export($searchParamArray, true));
        }

        $resultParamArray = array();
        foreach ($searchParamArray as $searchParam) {

            if (is_array($searchParam) && count($searchParam) == 3) {
                switch ($searchParam[0]) {

                    // 取分类下面的商品，包括子分类的商品，我们需要取得所有子分类的 ID 然后重新构造查询条件
                    case 'category_id':
                    case 'g.category_id':
                    case 'cat_id':
                    case 'g.cat_id':
                        $goodsCategoryService = new GoodsCategoryService();
                        // 最多取 5 层分类，缓存 10 分钟
                        $childrenIdArray   =
                            $goodsCategoryService->fetchCategoryChildrenIdArray($searchParam[2], 5, 600);
                        $childrenIdArray[] = $searchParam[2]; // 加入父节点
                        // 构造新的查询条件
                        $searchParam =
                            array(QueryBuilder::buildInCondition('g.cat_id', $childrenIdArray, \PDO::PARAM_INT));
                        break;

                    // 允许多品牌查询，需要生成 OR 查询条件，多品牌查询的情况 brand_id = 123_45_65_35 ，采用 _ 作为分隔
                    case 'brand_id':
                    case 'g.brand_id':
                        // 只处理 = 的情况
                        if ('=' != $searchParam[1] || false === strpos($searchParam[2], '_')) {
                            break;
                        }
                        $brandValueArray     = explode('_', $searchParam[2]);
                        $brandQueryCondArray = array();
                        foreach ($brandValueArray as $brandValue) {
                            if (!empty($brandValue)) {
                                $brandQueryCondArray[] = array('g.brand_id = ?', intval($brandValue));
                            }
                        }
                        // 生成一串的 OR 查询
                        $searchParam = QueryBuilder::buildOrFilter($brandQueryCondArray);
                        break;

                    default:
                        break;
                }
            }

            $resultParamArray[] = $searchParam;
        }

        return $resultParamArray;
    }

}