<?php

/**
 *
 * @author QiangYu
 *
 * 基本商品操作
 *
 * */

namespace Core\Service\Brand;

use \Core\Helper\Utility\Validator;
use \Core\Modal\SqlMapper as DataMapper;

class Brand extends \Core\Service\BaseService
{

    /**
     * 取得所有品牌
     *
     * @param int $ttl 缓存时间
     *
     * @return array
     */
    public function fetchBrandArray($ttl = 0)
    {
        return $this->_fetchArray(
            'brand',
            '*',
            array(),
            array('order' => 'sort_order desc, brand_id desc'),
            0,
            0,
            $ttl
        );
    }

}
