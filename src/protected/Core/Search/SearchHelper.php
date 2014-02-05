<?php
/**
 * 搜索的 Helper 类
 *
 * @author QiangYu
 */

namespace Core\Search;

/**
 * 搜索的 Helper 工具类
 *
 */
final class SearchHelper
{
    /**
     *
     * 目前支持的搜索模块列表
     *
     */
    const
        // 用户搜索
        Module_User                          = 'User',

        // 商品搜索
        Module_Goods                         = 'Goods',
        Module_GoodsGoodsAttr                = 'GoodsGoodsAttr',
        Module_GoodsGoodsPromote             = 'GoodsGoodsPromote',

        // 商品属性搜索
        Module_GoodsAttrGoods                = 'GoodsAttrGoods',

        // 文章搜索
        Module_Article                       = 'Article',

        // 订单搜索
        Module_OrderInfo                     = 'OrderInfo',
        Module_OrderGoods                    = 'OrderGoods',
        Module_OrderInfoOrderRefer           = 'OrderInfoOrderRefer',
        Module_OrderGoodsOrderInfo           = 'OrderGoodsOrderInfo',
        Module_OrderGoodsOrderInfoOrderRefer = 'OrderGoodsOrderInfoOrderRefer';


    /**
     * 缺省我们采用 SQL 搜索
     *
     * 优点：简单
     * 缺点：数据量很大的时候效率很差，数据量大的时候需要考虑换成专门的搜索服务器提供搜索，比如 sphinx, solr, elastic search
     */
    public static $searchImplementClass = '\Core\Search\Sql\SqlSearch';

    /**
     * 如果搜索实现需要初始化参数的话，这里提供初始化参数
     */
    public static $searchImplementInitParamArray = array();

    protected static $searchInstance = null;

    /**
     * 生成搜索的具体 instance
     *
     * @return ISearch
     * @throws \InvalidArgumentException
     */
    protected static function getSearchInstance()
    {
        if (!static::$searchInstance) {
            static::$searchInstance = new static::$searchImplementClass;
            if (!static::$searchInstance instanceof ISearch) {
                throw new \InvalidArgumentException(static::$searchImplementClass . ' is invalid search implement');
            }

            if (!static::$searchInstance->init(static::$searchImplementInitParamArray)) {
                printLog(
                    static::$searchImplementClass . ' init failed with param ' . print_r(
                        static::$searchImplementInitParamArray,
                        true
                    ),
                    __CLASS__,
                    \Core\Log\Base::ERROR
                );
                throw new \InvalidArgumentException('can not init ' . static::$searchImplementClass);
            }
        }
        return static::$searchInstance;
    }

    /**
     * 调用对应的对象方法
     *
     * @param       $func
     * @param array $args
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    static function __callstatic($func, array $args)
    {
        $searchInstance = static::getSearchInstance();
        if (!$searchInstance || !$searchInstance instanceof ISearch) {
            throw new \InvalidArgumentException(static::$searchImplementClass . ' is invalid');
        }
        return call_user_func_array(array($searchInstance, $func), $args);
    }
}