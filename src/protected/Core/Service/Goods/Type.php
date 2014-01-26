<?php

/**
 *
 * @author QiangYu
 *
 * 商品类型管理，比如商品是书籍、手机、衣服、裤子 ... 我们采用 Meta 来保持数据
 *
 * */

namespace Core\Service\Goods;

use Core\Service\Meta\Meta as MetaBasicService;

class Type extends MetaBasicService
{
    const META_TYPE_GOODS_TYPE = 'goods_type';

    /**
     * 加载一个 goods_type 数据
     *
     * @param int $typeId
     * @param int $ttl
     * @return object
     * @throws \InvalidArgumentException
     */
    public function loadGoodsTypeById($typeId, $ttl = 0)
    {
        $meta = $this->loadMetaById($typeId, $ttl);

        // 检查 category 是否合法
        if (!$meta->isEmpty()) {
            if (self::META_TYPE_GOODS_TYPE != $meta['meta_type']) {
                throw new \InvalidArgumentException('typeId[' . $typeId . '] is illegal');
            }
        } else {
            $meta->meta_type = self::META_TYPE_GOODS_TYPE;
        }

        return $meta;
    }

    /**
     * 取得 goods_type 数组
     *
     * @param array $condArray
     * @param int $offset
     * @param int $limit
     * @param int $ttl
     * @return array
     */
    public function fetchGoodsTypeArray(array $condArray, $offset = 0, $limit = 10, $ttl = 0)
    {
        $condArray[] = array('meta_type = ?', self::META_TYPE_GOODS_TYPE);
        return $this->_fetchArray(
            'meta',
            '*', // table , fields
            $condArray,
            array('order' => 'meta_sort_order desc, meta_id desc'),
            $offset,
            $limit,
            $ttl
        );
    }

    /**
     * 计算 goods_type 的数量，用于分页
     *
     * @param array $condArray
     * @param int $ttl
     * @return int
     */
    public function countGoodsTypeArray(array $condArray, $ttl = 0)
    {
        $condArray[] = array('meta_type = ?', self::META_TYPE_GOODS_TYPE);
        return $this->_countArray('meta', $condArray, null, $ttl);
    }

}
