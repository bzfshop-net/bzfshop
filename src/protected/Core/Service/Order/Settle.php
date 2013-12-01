<?php

/**
 *
 * @author QiangYu
 *
 * 订单的来源记录操作
 *
 * */

namespace Core\Service\Order;

use Core\Helper\Utility\Validator;

class Settle extends \Core\Service\BaseService
{
    /**
     * 通过 settle_id 取得 order_settle 记录
     *
     * @param  int $settle_id
     * @param int  $ttl
     *
     * @return object
     */
    public function loadOrderSettleBySettleId($settle_id, $ttl = 0)
    {
        return $this->_loadById('order_settle', 'settle_id = ?', $settle_id, $ttl);
    }


    /**
     * 取得一组 order_settle 的列表
     *
     * @return array 商品列表
     *
     * @param array $condArray 查询条件数组，例如：
     *                         array(
     *                         array('supplier_id = ?', $supplier_id)
     *                         array('is_on_sale = ?', 1)
     *                         array('create_time > ? or create_time < ?', $timeMin, $timeMax)
     * )
     *
     * @param int   $offset    用于分页的开始 >= 0
     * @param int   $limit     每页多少条
     * @param int   $ttl       缓存多少时间
     *
     */
    public function fetchOrderSettleArray(array $condArray, $offset = 0, $limit = 10, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('condArray' => $condArray), '');
        $condArray = $validator->requireArray(true)->validate('condArray');
        $this->validate($validator);

        return $this->_fetchArray(
            'order_settle', //table
            '*', // fields
            $condArray,
            array('order' => 'settle_id desc'),
            $offset,
            $limit,
            $ttl
        );
    }

    /**
     *
     * 取得一组记录的数目，用于分页
     *
     * @return int 查询条数
     *
     * @param array $condArray 查询条件数组，例如：
     *                         array(
     *                         array('supplier_id = ?', $supplier_id)
     *                         array('is_on_sale = ?', 1)
     *                         array('create_time > ? or create_time < ?', $timeMin, $timeMax)
     * )
     *
     * @param int   $ttl       缓存多少时间
     *
     */
    public function countOrderSettleArray(array $condArray, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('condArray' => $condArray), '');
        $condArray = $validator->requireArray(true)->validate('condArray');
        $this->validate($validator);

        return $this->_countArray(
            'order_settle', //table
            $condArray,
            null,
            $ttl
        );
    }

}

