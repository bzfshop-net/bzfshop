<?php

/**
 *
 * @author QiangYu
 *
 * 提供快递公司信息操作的服务
 *
 * */

namespace Core\Service\Meta;

use Core\Helper\Utility\Validator;

class Express extends Meta
{
    const META_TYPE = 'express';

    /**
     * 取得目前可以使用的快递公司列表
     *
     * @param int $ttl
     */
    public function fetchAvailableExpressArray($ttl = 0)
    {
        return $this->_fetchArray(
            'meta',
            '*',
            array(array('meta_type = ? and meta_status = 1', Express::META_TYPE)),
            array('order' => 'meta_sort_order desc, meta_id desc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 取得所有快递公司列表
     *
     * @param int $ttl 缓存时间
     *
     * @return array
     */
    public function fetchExpressArray($ttl = 0)
    {
        return $this->_fetchArray(
            'meta',
            '*',
            array(array('meta_type = ?', Express::META_TYPE)),
            array('order' => 'meta_sort_order desc, meta_id desc'),
            0,
            0,
            $ttl
        );
    }

}
