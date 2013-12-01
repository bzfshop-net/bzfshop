<?php

/**
 *
 * @author QiangYu
 *
 * 用 Meta 实现 树形数据的存储，比如一级一级的分类，省、市、区 这种层级结构
 *
 * */

namespace Core\Service\Meta;

use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;

class Tree extends Meta
{
    const META_TYPE = 'tree';

    /**
     * 加载一个树节点，其中 $treeKey, $parentId, $name  唯一标识这个节点
     *
     * @param  string $treeKey
     * @param  int    $parentId
     * @param  string $name
     * @param int     $ttl
     *
     * @return DataMapper
     * @throws \InvalidArgumentException
     */
    public function loadTreeNode($treeKey, $parentId, $name, $ttl = 0)
    {

        if (empty($treeKey) || empty($name)) {
            throw new \InvalidArgumentException('treeKey, name can not be empty');
        }

        $dataMapper = new DataMapper('meta');
        $dataMapper->loadOne(
            array(
                 'meta_type = ? and meta_key = ? and parent_meta_id = ? and meta_name = ?',
                 Tree::META_TYPE,
                 $treeKey,
                 $parentId,
                 $name
            ),
            null,
            $ttl
        );
        return $dataMapper;
    }

    /**
     * 只通过 treeKey 和 name 来加载一个 节点
     *
     * 注意： treeKey , name  组合未必是唯一的，只能你自己保证唯一性了
     *
     * @param  string $treeKey
     * @param  string $name
     * @param int     $ttl
     *
     * @return DataMapper
     * @throws \InvalidArgumentException
     */
    public function loadTreeNodeWithTreeKeyAndName($treeKey, $name, $ttl = 0)
    {

        if (empty($treeKey) || empty($name)) {
            throw new \InvalidArgumentException('treeKey, name can not be empty');
        }

        $dataMapper = new DataMapper('meta');
        $dataMapper->loadOne(
            array(
                 'meta_type = ? and meta_key = ? and meta_name = ?',
                 Tree::META_TYPE,
                 $treeKey,
                 $name
            ),
            null,
            $ttl
        );
        return $dataMapper;
    }

    /**
     * 保存、更新 一个树节点
     *
     * @param string $treeKey
     * @param int    $parentId
     * @param string $name
     * @param string $desc
     * @param string $data
     * @param int    $sortOrder
     * @param int    $status
     *
     * @return DataMapper
     */
    public function saveTreeNode($treeKey, $parentId, $name, $desc = null, $data = null, $sortOrder = 0, $status = 1)
    {
        $meta                  = $this->loadTreeNode($treeKey, $parentId, $name);
        $meta->meta_type       = Tree::META_TYPE;
        $meta->parent_meta_id  = $parentId;
        $meta->meta_key        = $treeKey;
        $meta->meta_name       = $name;
        $meta->meta_desc       = $desc;
        $meta->meta_data       = $data;
        $meta->meta_sort_order = $sortOrder;
        $meta->meta_status     = $status;
        $meta->save();
        return $meta;
    }

    /**
     * 删除一个树节点，其中 $treeKey, $parentId, $name  唯一标识这个节点
     *
     * @param  string $treeKey
     * @param  int    $parentId
     * @param  string $name
     * @param int     $ttl
     *
     */
    public function removeTreeNode($treeKey, $parentId, $name)
    {
        $treeNode = $this->loadTreeNode($treeKey, $parentId, $name);
        if (!$treeNode->isEmpty()) {
            $treeNode->erase();
        }
    }

    /**
     * 取得一组树节点
     *
     * @param  string $treeKey
     * @param  int    $parentId
     * @param int     $ttl
     *
     * @return array
     */
    public function fetchTreeNodeArray($treeKey, $parentId, $ttl = 0)
    {

        return $this->_fetchArray(
            'meta',
            '*',
            array(
                 array(
                     'meta_type = ? and parent_meta_id = ? and meta_key = ? and meta_status = 1',
                     Tree::META_TYPE,
                     $parentId,
                     $treeKey
                 )
            ),
            array('order' => 'meta_sort_order desc, meta_id desc'),
            0,
            0,
            $ttl
        );

    }


    // 递归建立子节点
    public function buildChildArrayTree(&$treeNodeArray, $parentIdToMetaArray)
    {
        foreach ($treeNodeArray as &$treeNodeItem) {

            if (isset($parentIdToMetaArray[$treeNodeItem['meta_id']])) {
                $treeNodeItem['child_list'] = $parentIdToMetaArray[$treeNodeItem['meta_id']];
            }

            if (isset($treeNodeItem['child_list'])) {
                $this->buildChildArrayTree($treeNodeItem['child_list'], $parentIdToMetaArray);
            }
        }
    }

    /**
     * 取得一个节点下面所有的子节点，并且通过 child_list -> array() 构建层级结构
     *
     * @param  string $treeKey
     * @param  int    $parentId
     * @param int     $ttl
     *
     * @return array
     */
    public function fetchChildTreeNodeArrayAll($treeKey, $parentId, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('treeKey' => $treeKey, 'parentId' => $parentId));

        $treeKey  = $validator->required()->validate('treeKey');
        $parentId = $validator->digits()->min(0)->validate('parentId');
        $parentId = $parentId ? : 0;

        $this->validate($validator);

        // 获取整个树的节点
        $treeNodeArray = $this->_fetchArray(
            'meta',
            '*',
            array(
                 array(
                     'meta_type = ? and meta_key = ? and meta_status = 1',
                     Tree::META_TYPE,
                     $treeKey
                 )
            ),
            array('order' => 'meta_sort_order desc, meta_id desc'),
            0,
            0,
            $ttl
        );

        if (empty($treeNodeArray)) {
            // 没有数据，返回空数组
            return array();
        }

        // 建立 parentId --> array(meta)
        $parentIdToMetaArray = array();
        foreach ($treeNodeArray as $treeNodeItem) {
            if (!isset($parentIdToMetaArray[$treeNodeItem['parent_meta_id']])) {
                $parentIdToMetaArray[$treeNodeItem['parent_meta_id']] = array();
            }
            $parentIdToMetaArray[$treeNodeItem['parent_meta_id']][] = $treeNodeItem;
        }

        if (empty($parentIdToMetaArray[$parentId])) {
            // 没有这一层级的数据，返回空数组
            return array();
        }

        $this->buildChildArrayTree($parentIdToMetaArray[$parentId], $parentIdToMetaArray);

        return $parentIdToMetaArray[$parentId];
    }

}
