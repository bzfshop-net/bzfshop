<?php

/**
 *
 * @author QiangYu
 *
 * 商品类型管理，比如商品是书籍、手机、衣服、裤子 ... 我们采用 Meta 来保持数据
 *
 * goods_type 数据说明：
 *
 * goods_group 数据说明：
 *
 *
 * goods_type_attr_item 数据说明
 *
 * meta_key： 用于存储 goods_group 的 ID
 * meta_ename： 用于存储属性类型，单选、单行输入、多行输入
 * meta_data：用于存储选项列表，数值用逗号分隔
 *
 *
 * */

namespace Core\Service\Goods;

use Core\Service\Meta\Meta as MetaBasicService;

class Type extends MetaBasicService
{
    // 商品类型
    const META_TYPE_GOODS_TYPE = 'goods_type';
    // 属性组
    const META_TYPE_GOODS_TYPE_ATTR_GROUP = 'goods_type_attr_group';
    // 属性项
    const META_TYPE_GOODS_TYPE_ATTR_ITEM = 'goods_type_attr_item';

    // attrItem 的类型说明
    public static $attrItemTypeDesc = array(
        'select' => '单选',
        'input' => '手动输入-单行',
        'textarea' => '手动输入-多行',
    );

    /**
     * 加载一个 goods_type 数据
     *
     * @param int $meta_id
     * @param int $ttl
     * @return object
     * @throws \InvalidArgumentException
     */
    public function loadGoodsTypeById($meta_id, $ttl = 0)
    {
        $meta = $this->loadMetaById($meta_id, $ttl);

        // 检查 category 是否合法
        if (!$meta->isEmpty()) {
            if (self::META_TYPE_GOODS_TYPE != $meta['meta_type']) {
                throw new \InvalidArgumentException('meta_id[' . $meta_id . '] is illegal');
            }
        } else {
            $meta->meta_type = self::META_TYPE_GOODS_TYPE;
        }

        return $meta;
    }

    /**
     * 加载一个属性组数据
     *
     * @param $meta_id
     * @param int $ttl
     * @return object
     * @throws \InvalidArgumentException
     */
    public function loadGoodsTypeAttrGroupById($meta_id, $ttl = 0)
    {
        $meta = $this->loadMetaById($meta_id, $ttl);

        // 检查 category 是否合法
        if (!$meta->isEmpty()) {
            if (self::META_TYPE_GOODS_TYPE_ATTR_GROUP != $meta['meta_type']) {
                throw new \InvalidArgumentException('meta_id[' . $meta_id . '] is illegal');
            }
        } else {
            $meta->meta_type = self::META_TYPE_GOODS_TYPE_ATTR_GROUP;
        }

        return $meta;
    }

    /**
     * 加载一个属性数据
     *
     * @param $meta_id
     * @param int $ttl
     * @return object
     * @throws \InvalidArgumentException
     */
    public function loadGoodsTypeAttrItemById($meta_id, $ttl = 0)
    {
        $meta = $this->loadMetaById($meta_id, $ttl);

        // 检查 category 是否合法
        if (!$meta->isEmpty()) {
            if (self::META_TYPE_GOODS_TYPE_ATTR_ITEM != $meta['meta_type']) {
                throw new \InvalidArgumentException('typeId[' . $meta_id . '] is illegal');
            }
        } else {
            $meta->meta_type = self::META_TYPE_GOODS_TYPE_ATTR_ITEM;
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

    /**
     * 返回商品属性组的树形结构，如下：
     *
     * array(
     *    attrGroup['itemArray'] = array(..属性列表..),
     *    attrGroup['itemArray'] = array(..属性列表..),
     *    attrItem // 不属于任何属性组的属性
     * )
     *
     * @param int $meta_id 商品类型 ID
     * @return array
     */
    public function getGoodsTypeAttrTree($meta_id)
    {
        // 取属性组
        $attrGroupArray = $this->fetchGoodsTypeAttrGroupArray($meta_id);

        // 建立映射表
        $groupIdToItemArraymap = array();
        foreach ($attrGroupArray as &$attrGroup) {
            $attrGroup['itemArray'] = array();
            $groupIdToItemArraymap[$attrGroup['meta_id']] = & $attrGroup['itemArray'];
        }
        unset($attrGroup);

        $attrItemArray = $this->fetchGoodsTypeAttrItemArray($meta_id);
        foreach ($attrItemArray as $attrItemArray) {

            // 商品有属性组就放到属性组里，没有就作为独立商品
            if (isset($groupIdToItemArraymap[intval($attrItemArray['meta_key'])])) {
                $groupIdToItemArraymap[intval($attrItemArray['meta_key'])][] = $attrItemArray;
            } else {
                $attrGroupArray[] = $attrItemArray;
            }
        }

        return $attrGroupArray;
    }

    /**
     * 取得 goods_type 下面的 attr_group 列表
     *
     * @param $meta_id  goods_type 的 ID
     * @param int $ttl
     * @return array
     */
    public function fetchGoodsTypeAttrGroupArray($meta_id, $ttl = 0)
    {
        return $this->_fetchArray(
            'meta',
            '*', // table , fields
            array(array('meta_type = ? and parent_meta_id = ?', self::META_TYPE_GOODS_TYPE_ATTR_GROUP, $meta_id)),
            array('order' => 'meta_sort_order desc, meta_id asc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 取得 goods_type 下面的 attr_item 列表
     *
     * @param $typeId  goods_type 的 ID
     * @param $groupId attr_group 的 ID
     * @param int $ttl
     * @return array
     */
    public function fetchGoodsTypeAttrItemArray($typeId, $groupId = 0, $ttl = 0)
    {
        $condArray = array(array('meta_type = ? and parent_meta_id = ?', self::META_TYPE_GOODS_TYPE_ATTR_ITEM, $typeId));
        if ($groupId > 0) {
            // 我们采用了 meta_key 来保存 attr_group 的 ID
            $condArray[] = array('meta_key = ?', $groupId);
        }
        return $this->_fetchArray(
            'meta',
            '*', // table , fields
            $condArray,
            array('order' => 'meta_key asc, meta_sort_order desc, meta_id asc'),
            0,
            0,
            $ttl
        );
    }
}
