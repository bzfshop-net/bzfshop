<?php

/**
 *
 * @author QiangYu
 *
 * 商品编辑的日志记录操作
 *
 * */

namespace Core\Service\Goods;

use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;

class Log extends \Core\Service\BaseService
{

    /**
     * @param $goods_id
     * @param $admin_user_id
     * @param $admin_user_name
     * @param $desc
     * @param $content
     */
    public function addGoodsLog(
        $goods_id,
        $admin_user_id,
        $admin_user_name,
        $desc,
        $content
    ) {

        // 参数验证
        $validator       = new Validator(array(
                                              'goods_id'        => $goods_id,
                                              'admin_user_id'   => $admin_user_id,
                                              'admin_user_name' => $admin_user_name
                                         ));
        $goods_id        = $validator->required()->digits()->min(1)->validate('goods_id');
        $admin_user_id   = $validator->required()->digits()->min(1)->validate('admin_user_id');
        $admin_user_name = $validator->required()->validate('admin_user_name');
        $this->validate($validator);

        $goodsLog = $this->_loadById('goods_log', 'log_id = ?', 0);

        $goodsLog->log_time        = Time::gmTime();
        $goodsLog->goods_id        = $goods_id;
        $goodsLog->admin_user_id   = $admin_user_id;
        $goodsLog->admin_user_name = $admin_user_name;
        $goodsLog->desc            = $desc;
        $goodsLog->content         = $content;

        $goodsLog->save();
    }


    /**
     * @param  int $goods_id
     * @param  int $offset
     * @param  int $limit
     * @param int  $ttl
     *
     * @return array
     */
    public function fetchGoodsLogArray($goods_id, $offset, $limit, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('goods_id' => $goods_id));
        $goods_id  = $validator->required()->digits()->min(1)->validate('goods_id');
        $this->validate($validator);

        return $this->_fetchArray(
            'goods_log',
            '*',
            array(array('goods_id = ?', $goods_id)),
            array('order' => 'log_id desc'),
            $offset,
            $limit,
            $ttl
        );
    }

    public function countGoodsLogArray($goods_id, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('goods_id' => $goods_id));
        $goods_id  = $validator->required()->digits()->min(1)->validate('goods_id');
        $this->validate($validator);

        return $this->_countArray(
            'goods_log',
            array(array('goods_id = ?', $goods_id)),
            null,
            $ttl

        );
    }
}
