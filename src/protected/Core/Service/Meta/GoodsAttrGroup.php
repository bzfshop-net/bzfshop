<?php

/**
 *
 * @author QiangYu
 *
 * 商品的属性组，比如商品是 “手机”，它可能就对应一组属性 “屏幕、CPU、内存 ...”
 *
 * 设计考虑：
 *
 * meta_name : 组名
 * meta_desc : 组的描述
 * meta_data : 组内部的显示分类 array('屏幕' , 'CPU', ...)  一个显示分类可以对应多个属性，比如 '屏幕' 可能有 分辨率、材质、尺寸 ...
 *
 * 注意，meta_data 只是一个简单的 json 一维数组，里面的显示分类仅仅只用于显示
 *
 * */

namespace Core\Service\Meta;

use Core\Helper\Utility\Validator;

class GoodsAttrGroup extends Meta
{
    const META_TYPE = 'goods_attr_group';

    /**
     * 根据组名取得商品属性组
     *
     * @param string $groupName
     *
     * @return \Core\Modal\SqlMapper
     */
    public function loadGoodsAttrGroupByName($groupName)
    {
        // 参数验证
        $validator = new Validator(array('groupName' => $groupName));
        $groupName = $validator->required()->validate('groupName');
        $this->validate($validator);

        return $this->loadMetaByTypeAndName(GoodsAttrGroup::META_TYPE, $groupName);
    }

    /**
     * 新建或者更新一个属性组
     *
     * @param string $groupName
     * @param string $groupDesc
     * @param string $groupData 这里的 data 是 json 一维数组
     * @param int    $groupStatus
     *
     * @return \Core\Modal\SqlMapper
     */
    public function saveGoodsAttrGroup($groupName, $groupDesc, $groupData, $groupStatus = 1)
    {
        $meta              = $this->loadGoodsAttrGroupByName($groupName);
        $meta->meta_type   = GoodsAttrGroup::META_TYPE;
        $meta->meta_name   = $groupName;
        $meta->meta_desc   = $groupDesc;
        $meta->meta_data   = $groupData;
        $meta->meta_status = $groupStatus;
        $meta->save();

        return $meta;
    }


    /**
     * 取得商品属性组列表
     *
     * @param int $offset
     * @param int $limit
     * @param int $ttl
     *
     * @return array
     */
    public function fetchGoodsAttrGroupArray($condArray = null, $offset = 0, $limit = 0, $ttl = 0)
    {
        $filterArray = array(array('meta_type = ? and meta_status = 1', GoodsAttrGroup::META_TYPE));
        if ($condArray && is_array($condArray)) {
            $filterArray = array_merge($filterArray, $condArray);
        }

        return $this->_fetchArray(
            'meta',
            '*',
            $filterArray,
            array('order' => 'meta_sort_order desc, meta_id desc'),
            $offset,
            $limit,
            $ttl
        );
    }

    /**
     * 取商品属性组的数量，用于分页
     *
     * @param int $ttl
     *
     * @return int
     */
    public function countGoodsAttrGroupArray($condArray = null, $ttl = 0)
    {
        $filterArray = array(array('meta_type = ? and meta_status = 1', GoodsAttrGroup::META_TYPE));
        if ($condArray && is_array($condArray)) {
            $filterArray = array_merge($filterArray, $condArray);
        }
        return $this->_countArray(
            'meta',
            $filterArray,
            null,
            $ttl
        );
    }
}
