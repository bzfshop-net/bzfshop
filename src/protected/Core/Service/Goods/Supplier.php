<?php

/**
 *
 * @author QiangYu
 *
 * 根据供货商查询商品列表
 *
 * */

namespace Core\Service\Goods;

use Core\Helper\Utility\Validator;

class Supplier extends \Core\Service\BaseService
{

    /**
     * 取得供货商下面对应的商品描述列表
     * 由于这是为分类列表用的，所以我们尽量取少的数据，不是整个商品详情都取
     * 少 select 一些字段有利于提高性能
     *
     * @return array 商品列表，格式 array(array(商品详情), array(商品详情) ...)
     *
     * @param int $suppliers_id 分类的ID
     * @param int $offset
     * @param int $limit        限制一次取得多少个商品
     * @param int $ttl          缓存时间
     */
    public function fetchSupplierGoodsArray($suppliers_id, $offset = 0, $limit = 10, $ttl = 0)
    {
        // 参数验证
        $validator    = new Validator(array(
                                           'suppliers_id' => $suppliers_id,
                                           'offset'       => $offset,
                                           'limit'        => $limit,
                                           'ttl'          => $ttl
                                      ));
        $suppliers_id = $validator->required()->digits()->min(0)->validate('suppliers_id');
        $offset       = $validator->digits()->min(0)->validate('offset');
        $limit        = $validator->digits()->min(1)->validate('limit');
        $ttl          = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        return $this->_fetchArray(
            'goods',
            ' goods_id, cat_id, goods_sn, goods_name, brand_id, goods_number, market_price, shop_price, promote_price, '
            . ' promote_start_date, promote_end_date, '
            . ' is_real, is_shipping, sort_order, goods_type, suppliers_id ',
            // fields
            array(array('suppliers_id = ? AND is_delete = 0 AND is_on_sale = 1 AND is_alone_sale = 1', $suppliers_id)),
            // filter
            array('order' => 'sort_order desc, goods_id desc'), // options
            $offset,
            $limit,
            $ttl
        );
    }

    /**
     * 取得供货商下面商品的总数，用于分页显示
     *
     * @return int 商品总数
     *
     * @param int $suppliers_id 供货商的ID
     * @param int $ttl          缓存时间
     */
    public function countSupplierGoodsArray($suppliers_id, $ttl = 0)
    {
        // 参数验证
        $validator    = new Validator(array('suppliers_id' => $suppliers_id, 'ttl' => $ttl));
        $suppliers_id = $validator->required()->digits()->min(1)->validate('suppliers_id');
        $ttl          = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        return $this->_countArray(
            'goods',
            // filter
            array(array('suppliers_id = ? AND is_delete = 0 AND is_on_sale = 1 AND is_alone_sale = 1', $suppliers_id)),
            null,
            $ttl
        );
    }

}
