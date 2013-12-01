<?php

/**
 *
 * @author QiangYu
 *
 * 商品评论的操作
 *
 * */

namespace Core\Service\Goods;

use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;

class Comment extends \Core\Service\BaseService
{

    /**
     * 加载一个 goods_comment 记录
     *
     * @param  int $id
     * @param int  $ttl
     *
     * @return object
     */
    public function loadGoodsCommentById($id, $ttl = 0)
    {
        return $this->_loadById('goods_comment', 'comment_id = ?', $id, $ttl);
    }

    /**
     * 根据 order_goods 的 rec_id 取得对应的 goods_comment 记录
     *
     * @param  int $rec_id
     * @param int  $ttl
     *
     * @return object
     */
    public function loadGoodsCommentByOrderGoodsRecId($rec_id, $ttl = 0)
    {
        return $this->_loadById('goods_comment', 'rec_id = ?', $rec_id, $ttl);
    }

    /**
     * 检查 order_goods 对应的评论记录是否已经存在
     *
     * @param int $rec_id
     *
     * @return bool
     */
    public function isOrderGoodsCommentExist($rec_id)
    {
        if (!$rec_id) {
            return false;
        }

        // 参数验证
        $validator = new Validator(array('rec_id' => $rec_id));
        $rec_id    = $validator->required()->digits()->min(1)->validate('rec_id');
        $this->validate($validator);

        $dataMapper = new DataMapper('goods_comment');
        $dataMapper->loadOne(array('rec_id = ?', $rec_id), null, 0);

        return !($dataMapper->isEmpty());
    }
}
