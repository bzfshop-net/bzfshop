<?php

/**
 *
 * @author QiangYu
 *
 * 商品的单个属性值
 *
 * */

namespace Core\Service\Meta;

use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;

class GoodsAttrItem extends Meta
{
    const META_TYPE = 'goods_attr_item';

    // ATTR 允许用户如何输入
    const ATTR_INPUT_TYPE_MANUAL = 0, // 手动输入
        ATTR_INPUT_TYPE_CHOOSE   = 1, // 从 attr_value 中选择
        ATTR_INPUT_TYPE_TEXTAREA = 2; // 多行文本输入

    // ATTR 类型
    const ATTR_TYPE_DISPLAY       = 0, // 属性只是用于显示，比如 材质：纯棉
        ATTR_TYPE_CHOOSE_RADIO    = 1, //属性用于选择，单选，对应不同价格，比如 红色款价格 +10 元
        ATTR_TYPE_CHOOSE_CHECKBOX = 2; // 属性用于选择，多选，对应不同价格，比如 红色款价格 +10 元

    /**
     * 取得商品属性值
     *
     * @param string $itemName
     *
     * @return \Core\Modal\SqlMapper
     */
    public function loadGoodsAttrItem($groupId, $itemName)
    {
        // 参数验证
        $validator = new Validator(array('groupId' => $groupId, 'itemName' => $itemName));
        $groupId   = $validator->required()->digits()->min(1)->validate('groupId');
        $itemName  = $validator->required()->validate('itemName');
        $this->validate($validator);

        // 数据查询
        $dataMapper = new DataMapper('meta');
        $dataMapper->loadOne(
            array(
                 'meta_type = ? and parent_meta_id = ? and meta_name = ?',
                 GoodsAttrItem::META_TYPE,
                 $groupId,
                 $itemName
            )
        );
        return $dataMapper;
    }

    /**
     * 新建或者更新一个属性值
     *
     * @param string $itemName
     * @param string $itemDesc
     * @param string $itemData 这里的 data 是 json 结构
     * @param int    $status
     *
     * @return \Core\Modal\SqlMapper
     */
    public function saveGoodsAttrItem($groupId, $itemName, $itemDesc, $itemData)
    {
        $meta                 = $this->loadGoodsAttrItem($groupId, $itemName);
        $meta->meta_type      = GoodsAttrItem::META_TYPE;
        $meta->parent_meta_id = $groupId;
        $meta->meta_name      = $itemName;
        $meta->meta_desc      = $itemDesc;
        $meta->meta_data      = $itemData;
        $meta->save();

        return $meta;
    }

    /**
     * 删除一个属性值
     *
     * @param int    $groupId
     * @param string $itemName
     */
    public function removeGoodsAttrItem($groupId, $itemName)
    {
        $goodsAttrItem = $this->loadGoodsAttrItem($groupId, $itemName);
        if (!$goodsAttrItem->isEmpty()) {
            $goodsAttrItem->erase();
        }
    }

    /**
     * 对 GoodsAttrItem 的 meta_data 字段解码
     *
     * @param $data
     *
     * @return  array
     */
    public static function decodeGoodsAttrItemData($data)
    {
        $dataArray = json_decode($data, true);

        if (!is_array($dataArray)) {
            $dataArray = array();
        }

        if (!isset($dataArray['attr_input_type'])) {
            $dataArray['attr_input_type'] = GoodsAttrItem::ATTR_INPUT_TYPE_MANUAL;
        }

        if (!isset($dataArray['attr_type'])) {
            $dataArray['attr_type'] = GoodsAttrItem::ATTR_TYPE_DISPLAY;
        }

        return $dataArray;
    }

    /**
     * 对 GoodsAttrItem 的 dataArray 进行编码
     *
     * @param $dataArray
     *
     * @return string
     */
    public static function encodeGoodsAttrItemData($dataArray)
    {
        return json_encode($dataArray);
    }


    /**
     * 取得一个属性组中所有的属性值
     *
     * @param  int $groupId
     * @param int  $ttl
     *
     * @return array
     */
    public function fetchGoodsAttrItemArray($groupId, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('groupId' => $groupId));
        $groupId   = $validator->required()->digits()->min(1)->validate('groupId');
        $this->validate($validator);

        return $this->_fetchArray(
            'meta',
            '*',
            array(
                 array(
                     'meta_type = ? and parent_meta_id = ? and meta_status = 1',
                     GoodsAttrItem::META_TYPE,
                     $groupId
                 )
            ),
            array('order' => 'meta_sort_order asc, meta_id asc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 取得一个商品在某个属性组中的属性
     *
     * @param  int $groupId
     * @param  int $goods_id
     * @param int  $ttl
     *
     * @return array|null
     */
    public function fetchGoodsAttributeArrayOfAttrGroup($groupId, $goods_id, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('groupId' => $groupId, 'goods_id' => $goods_id, 'ttl' => $ttl));
        $groupId   = $validator->required()->digits()->min(1)->validate('groupId');
        $goods_id  = $validator->required()->digits()->min(1)->validate('goods_id');
        $ttl       = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        // 取得商品的属性
        $sql      = "SELECT a.*, " .
            "g.goods_attr_id, g.attr_value, g.attr_price " .
            'FROM ' . DataMapper::tableName('meta') . ' AS a ' .
            'LEFT JOIN ' . DataMapper::tableName('goods_attr') . ' AS g ON a.meta_id = g.attr_id and g.goods_id = ? ' .
            "WHERE a.parent_meta_id = ? and a.meta_type = ? " .
            'ORDER BY a.meta_sort_order, g.attr_price, g.goods_attr_id';
        $dbEngine = DataMapper::getDbEngine();
        $result   = $dbEngine->exec($sql, array(1 => $goods_id, $groupId, GoodsAttrItem::META_TYPE), $ttl);

        if (empty($result)) { // 没有属性，则返回
            return null;
        }

        // 处理属性，属性分为 2 类，一类用于显示，一类用于选择价格
        $attrubuteArray         = array();
        $attrubuteArray['prop'] = array(); //属性  0: 直接用于显示的属性
        $attrubuteArray['spec'] = array(); //规格 1: 用户单选属性，不同价格   2: 用户多选属性，不同价格

        foreach ($result as $attrItem) {

            $attrData = GoodsAttrItem::decodeGoodsAttrItemData($attrItem['meta_data']);

            if (GoodsAttrItem::ATTR_TYPE_DISPLAY == $attrData['attr_type']) {
                // 只是用于显示的属性
                $attrubuteArray['prop'][$attrItem['meta_id']] = array(
                    'name'            => $attrItem['meta_name'],
                    'value'           => $attrItem['attr_value'],
                    'goods_attr_id'   => $attrItem['goods_attr_id'],
                    'attr_input_type' => $attrData['attr_input_type'],
                    'attr_type'       => $attrData['attr_type'],
                    'attr_desc'       => $attrItem['meta_desc'],
                    'meta_id'         => $attrItem['meta_id'],
                );
            } else {
                // 用于选择不同价格的属性
                $attrubuteArray['spec'][$attrItem['meta_id']]['meta_id']         = $attrItem['meta_id'];
                $attrubuteArray['spec'][$attrItem['meta_id']]['attr_input_type'] = $attrData['attr_input_type'];
                $attrubuteArray['spec'][$attrItem['meta_id']]['attr_type']       = $attrData['attr_type'];
                $attrubuteArray['spec'][$attrItem['meta_id']]['attr_desc']       = $attrItem['meta_desc'];
                $attrubuteArray['spec'][$attrItem['meta_id']]['name']            = $attrItem['meta_name'];
                $attrubuteArray['spec'][$attrItem['meta_id']]['values'][]        = array(
                    'label'         => $attrItem['attr_value'],
                    'price'         => $attrItem['attr_price'],
                    'goods_attr_id' => $attrItem['goods_attr_id']
                );
            }
        }

        return $attrubuteArray;
    }

}
