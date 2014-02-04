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

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;
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
        'select'   => '单选',
        'input'    => '手动输入-单行',
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
     * 加载 goods_attr 记录
     *
     * @param $goods_attr_id
     * @param int $ttl
     * @return object
     */
    public function loadGoodsAttrById($goods_attr_id, $ttl = 0)
    {
        return $this->_loadById('goods_attr', 'goods_attr_id = ?', $goods_attr_id, $ttl);
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
     * 根据类型ID数组取得一组类型
     *
     * @param array $typeIdArray 类型ID数组
     * @param int $ttl
     * @return array
     */
    public function fetchGoodsTypeArrayByTypeIdArray(array $typeIdArray, $ttl = 0)
    {
        return $this->fetchGoodsTypeArray(
            array(array(QueryBuilder::buildInCondition('meta_id', $typeIdArray, \PDO::PARAM_INT))),
            0, 0, $ttl);
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
    public function fetchGoodsTypeAttrTree($meta_id, $ttl = 0)
    {
        // 取属性组
        $attrGroupArray = $this->fetchGoodsTypeAttrGroupArray($meta_id, $ttl);

        // 建立映射表
        $groupIdToItemArraymap = array();
        foreach ($attrGroupArray as &$attrGroup) {
            $attrGroup['itemArray']                       = array();
            $groupIdToItemArraymap[$attrGroup['meta_id']] = & $attrGroup['itemArray'];
        }
        unset($attrGroup);

        $attrItemArray = $this->fetchGoodsTypeAttrItemArray($meta_id, $ttl);
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
     * 获得扁平的树形结构利于显示
     *
     * array(
     *      attrGroup,
     *      attrItemOfThisGroup,
     *      attrItemOfThisGroup,
     *      attrGroup,
     *      attrItemOfThisGroup,
     *      ....
     * )
     *
     * @param $meta_id
     * @param int $ttl
     * @return array
     */
    public function fetchGoodsTypeAttrTreeTable($meta_id, $ttl = 0)
    {

        $goodsTypeAttrTree = $this->fetchGoodsTypeAttrTree($meta_id, $ttl);

        // 把树变成扁平结构利于显示
        $goodsAttrTreeTable = array();
        foreach ($goodsTypeAttrTree as &$attrItem) {
            $goodsAttrTreeTable[] = & $attrItem;
            if (isset($attrItem['itemArray'])) {
                foreach ($attrItem['itemArray'] as $subItem) {
                    $goodsAttrTreeTable[] = $subItem;
                }
                unset($attrItem['itemArray']);
            }
        }
        unset($attrItem);

        return $goodsAttrTreeTable;
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
     * 删除一个属性组
     *
     * @param int $meta_id
     */
    public function removeGoodsTypeAttrGroup($meta_id)
    {
        $attrGroup = $this->loadGoodsTypeAttrGroupById($meta_id);

        // 把 Group 下的 Item 都设置为 没有Group
        $sql      = 'update ' . DataMapper::tableName('meta') . ' set meta_key = "" where meta_type = ? and meta_key = ?';
        $dbEngine = DataMapper::getDbEngine();
        $dbEngine->exec($sql, array(1 => self::META_TYPE_GOODS_TYPE_ATTR_ITEM, strval($meta_id)));

        // 删除 Group 自身
        $attrGroup->erase();
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
        // 首先验证参数
        $validator = new Validator(array('typeId' => $typeId, 'groupId' => $groupId));
        $typeId    = $validator->required()->digits()->min(1)->validate('typeId');
        $groupId   = $validator->digits()->validate('groupId');
        $this->validate($validator);

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

    /**
     * 删除一个属性值，需要把所有商品设置的属性也一并删除
     *
     * @param $meta_id
     */
    public function removeGoodsTypeAttrItem($meta_id)
    {
        $attrItem = $this->loadGoodsTypeAttrItemById($meta_id);

        // 把 商品设置了这个属性值全部删除
        $dataMapper = new DataMapper('goods_attr');
        $dataMapper->erase(array('attr_item_id = ?', $meta_id));

        // 删除 Item 自身
        $attrItem->erase();
    }

    /**
     * 取得商品的属性值列表
     *
     * @param int $goods_id 商品 ID
     * @param int $typeId 商品类型 ID
     * @param int $ttl 缓存时间
     * @return array
     */
    public function fetchGoodsAttrItemValueArray($goods_id, $typeId, $ttl = 0)
    {
        // 首先验证参数
        $validator = new Validator(array('goods_id' => $goods_id, 'typeId' => $typeId));
        $goods_id  = $validator->required()->digits()->min(1)->validate('goods_id');
        $typeId    = $validator->required()->digits()->min(1)->validate('typeId');
        $this->validate($validator);

        $tableJoin = DataMapper::tableName('meta') . ' as m LEFT JOIN (select * from ' . DataMapper::tableName('goods_attr') . ' where goods_id = ' . $goods_id . ')' . ' as ga on m.meta_id = ga.attr_item_id';

        return $this->_fetchArray(
            $tableJoin,
            // table , fields
            'm.meta_id, m.meta_type, m.meta_name, m.meta_key, m.meta_ename, m.meta_data, ga.goods_attr_id, ga.attr_item_value',
            // 查询条件
            array(
                array('m.meta_type = ? and m.parent_meta_id = ? ', self::META_TYPE_GOODS_TYPE_ATTR_ITEM, $typeId)
            ),
            array('order' => 'm.meta_key asc, m.meta_sort_order desc, m.meta_id asc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 返回商品属性值的树形结构，如下：
     *
     * array(
     *    attrGroup['itemArray'] = array(..属性列表..),
     *    attrGroup['itemArray'] = array(..属性列表..),
     *    attrItem // 不属于任何属性组的属性
     * )
     *
     * @param $goods_id
     * @param $typeId
     * @param int $ttl
     * @return array
     */
    public function fetchGoodsAttrItemValueTree($goods_id, $typeId, $ttl = 0)
    {
        // 首先验证参数
        $validator = new Validator(array('goods_id' => $goods_id, 'typeId' => $typeId));
        $goods_id  = $validator->required()->digits()->min(1)->validate('goods_id');
        $typeId    = $validator->required()->digits()->min(1)->validate('typeId');
        $this->validate($validator);

        // 取得分组
        $attrGroupArray    = $this->fetchGoodsTypeAttrGroupArray($typeId, $ttl);
        $groupIdToItemList = array();
        foreach ($attrGroupArray as &$attrGroup) {
            $attrGroup['itemArray']                   = array();
            $groupIdToItemList[$attrGroup['meta_id']] = & $attrGroup['itemArray'];
        }

        // 取得属性值
        $goodsAttrValueArray = $this->fetchGoodsAttrItemValueArray($goods_id, $typeId, $ttl);
        // 把属性值放到对应的分组里面
        foreach ($goodsAttrValueArray as $goodsAttrValue) {
            if (isset($groupIdToItemList[intval($goodsAttrValue['meta_key'])])) {
                $groupIdToItemList[intval($goodsAttrValue['meta_key'])][] = $goodsAttrValue;
            } else {
                $attrGroupArray[] = $goodsAttrValue;
            }
        }

        return $attrGroupArray;
    }

    /**
     * 获得扁平的树形结构利于显示
     *
     * array(
     *      attrGroup,
     *      attrItemOfThisGroup,
     *      attrItemOfThisGroup,
     *      attrGroup,
     *      attrItemOfThisGroup,
     *      ....
     * )
     *
     * @param $goods_id
     * @param $typeId
     * @param int $ttl
     * @return array
     */
    public function fetchGoodsAttrItemValueTreeTable($goods_id, $typeId, $ttl = 0)
    {
        $goodsAttrValueTree = $this->fetchGoodsAttrItemValueTree($goods_id, $typeId, $ttl);

        // 把树变成扁平结构利于显示
        $goodsAttrValueTreeTable = array();
        foreach ($goodsAttrValueTree as &$attrItem) {
            $goodsAttrValueTreeTable[] = & $attrItem;
            if (isset($attrItem['itemArray'])) {
                foreach ($attrItem['itemArray'] as $subItem) {
                    $goodsAttrValueTreeTable[] = $subItem;
                }
                unset($attrItem['itemArray']);
            }
        }

        return $goodsAttrValueTreeTable;
    }

    /**
     * 删除商品的所有属性值
     *
     * @param int $goods_id 商品ID
     */
    public function removeAllGoodsAttrItemValue($goods_id)
    {
        // 首先验证参数
        $validator = new Validator(array('goods_id' => $goods_id));
        $goods_id  = $validator->required()->digits()->min(1)->validate('goods_id');
        $this->validate($validator);

        $DataMapper = new DataMapper('goods_attr');
        $DataMapper->erase(array('goods_id = ?', $goods_id));
    }
}
