<?php
/**
 * @author QiangYu
 *
 * 提供用户权限控制数据服务
 *
 */

namespace Core\Service\Meta;

use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;

class Privilege extends Meta
{

    const META_TYPE_PRIVILEGE_GROUP = 'privilege_group';
    const META_TYPE_PRIVILEGE_ITEM  = 'privilege_item';

    /**
     * 缓存的 ID 值
     *
     * @var
     */
    private $privilegeArrayCacheId;

    public function __construct()
    {
        $this->privilegeArrayCacheId =
            md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__) . '_privilegeArrayCacheId';
    }

    /**
     *
     * 清除所有的定义，请谨慎使用这个方法
     *
     */
    public function clearPrivilege()
    {
        $dataMapper = new DataMapper('meta');
        $dataMapper->erase(array('meta_type = ?'), Privilege::META_TYPE_PRIVILEGE_ITEM);
        $dataMapper->erase(array('meta_type = ?'), Privilege::META_TYPE_PRIVILEGE_GROUP);
    }

    /**
     * 通过 groupKey 取得权限组
     *
     * @param string $groupKey
     * @param int    $ttl 缓存时间
     *
     * @return \Core\Modal\SqlMapper
     */
    public function loadPrivilegeGroup($groupKey, $ttl = 0)
    {
        return $this->loadMetaByTypeAndKey(Privilege::META_TYPE_PRIVILEGE_GROUP, $groupKey, $ttl);
    }

    /**
     * 创建或者更新一个用户权限组
     *
     * @param  string $groupKey     权限组的唯一 key
     * @param  string $groupName    权限组用于显示的名称
     * @param  string $groupDesc    权限组的描述解释
     * @param int     $sortOrder    权限组的排序
     *
     * @return \Core\Modal\SqlMapper
     *
     */
    public function savePrivilegeGroup($groupKey, $groupName, $groupDesc, $sortOrder = 0)
    {
        // 参数验证
        $validator = new Validator(array(
                                        'groupKey'  => $groupKey,
                                        'groupName' => $groupName,
                                        'groupDesc' => $groupDesc,
                                        'sortOrder' => $sortOrder
                                   ));
        $groupKey  = $validator->required()->validate('groupKey');
        $groupName = $validator->required()->validate('groupName');
        $groupDesc = $validator->validate('groupDesc');
        $sortOrder = $validator->digits()->min(0)->validate('sortOrder');
        $this->validate($validator);

        // 保存权限组信息
        $privilegeGroupItem                  = $this->loadPrivilegeGroup($groupKey);
        $privilegeGroupItem->meta_type       = Privilege::META_TYPE_PRIVILEGE_GROUP;
        $privilegeGroupItem->meta_key        = $groupKey;
        $privilegeGroupItem->meta_name       = $groupName;
        $privilegeGroupItem->meta_desc       = $groupDesc;
        $privilegeGroupItem->meta_sort_order = $sortOrder;
        $privilegeGroupItem->save();

        // 更新缓存
        global $f3;
        $f3->clear($this->privilegeArrayCacheId);

        return $privilegeGroupItem;
    }

    /**
     * 加载一个权限值
     *
     * @param string $itemKey
     * @param int    $ttl 缓存时间
     *
     * @return \Core\Modal\SqlMapper
     */
    public function loadPrivilegeItem($itemKey, $ttl = 0)
    {
        return $this->loadMetaByTypeAndKey(Privilege::META_TYPE_PRIVILEGE_ITEM, $itemKey, $ttl);
    }

    /**
     * 创建或者更新一个用户权限
     *
     * @param  int    $groupId
     * @param  string $itemKey      必须唯一，否则就更新到别的权限上面去了
     * @param  string $itemName
     * @param  string $itemDesc
     * @param int     $sortOrder
     *
     * @return \Core\Modal\SqlMapper
     *
     */
    public function savePrivilegeItem($groupId, $itemKey, $itemName, $itemDesc, $sortOrder = 0)
    {
        // 参数验证
        $validator = new Validator(array(
                                        'groupId'   => $groupId,
                                        'itemKey'   => $itemKey,
                                        'itemName'  => $itemName,
                                        'itemDesc'  => $itemDesc,
                                        'sortOrder' => $sortOrder
                                   ));
        $groupId   = $validator->required()->digits()->min(0)->validate('groupId');
        $itemKey   = $validator->required()->validate('itemKey');
        $itemName  = $validator->required()->validate('itemName');
        $itemDesc  = $validator->validate('itemDesc');
        $sortOrder = $validator->digits()->min(0)->validate('sortOrder');
        $this->validate($validator);

        // 保存权限信息
        $privilegeItem                  = $this->loadPrivilegeItem($itemKey);
        $privilegeItem->meta_type       = Privilege::META_TYPE_PRIVILEGE_ITEM;
        $privilegeItem->parent_meta_id  = $groupId;
        $privilegeItem->meta_key        = $itemKey;
        $privilegeItem->meta_name       = $itemName;
        $privilegeItem->meta_desc       = $itemDesc;
        $privilegeItem->meta_sort_order = $sortOrder;
        $privilegeItem->save();

        // 更新缓存
        global $f3;
        $f3->clear($this->privilegeArrayCacheId);

        return $privilegeItem;
    }

    /**
     * 删除一个权限
     *
     * @param string $itemKey
     */
    public function removePrivilegeItem($itemKey)
    {
        // 参数验证
        $validator = new Validator(array('itemKey' => $itemKey));
        $itemKey   = $validator->required()->validate('itemKey');
        $this->validate($validator);

        // 删除权限
        $privilegeItem = $this->loadPrivilegeItem($itemKey);
        if (!$privilegeItem->isEmpty()) {
            $privilegeItem->erase();

            // 更新缓存
            global $f3;
            $f3->clear($this->privilegeArrayCacheId);
        }
    }

    /**
     * 取得权限组结构，格式如下
     *
     * array(
     *      权限组1 -->   array(
     *          'meta_id' => 0, 'meta_key' => 'xxxx', 'meta_name' => '组名', 'item_array' => array (
     *               权限 1 ---> array('meta_id' , 'meta_key' , 'meta_name', ....),
     *               权限 2 ---> array('meta_id' , 'meta_key' , 'meta_name', ....),
     *               ....
     *          )
     *      ),
     *      权限组2 -->  array(...)
     * )
     *
     *
     * @return array
     */
    public function fetchPrivilegeArray()
    {
        // 检查缓存
        global $f3;
        $privilegeGroupArray = $f3->get($this->privilegeArrayCacheId);
        if (!empty($privilegeGroupArray)) {
            goto out;
        }

        // 取得所有的权限值
        $privilegeItemArray = $this->_fetchArray(
            'meta',
            '*',
            array(array('meta_type = ? ', Privilege::META_TYPE_PRIVILEGE_ITEM)),
            array('order' => 'parent_meta_id asc, meta_sort_order desc, meta_id asc'), //按照权限组排序
            0,
            0
        );

        // 取得所有的权限组
        $privilegeGroupArray = $this->_fetchArray(
            'meta',
            '*',
            array(array('meta_type = ? ', Privilege::META_TYPE_PRIVILEGE_GROUP)),
            array('order' => 'meta_sort_order desc, meta_id asc'),
            0,
            0
        );

        // 建议 权限组 id ---> 权限的映射关系
        $groupIdToItemArray = array();
        foreach ($privilegeItemArray as $privilegeItem) {
            if (!array_key_exists($privilegeItem['parent_meta_id'], $groupIdToItemArray)) {
                $groupIdToItemArray[$privilegeItem['parent_meta_id']] = array();
            }
            $groupIdToItemArray[$privilegeItem['parent_meta_id']][] = $privilegeItem;
        }

        // 如果没有权限组，就 fake 一个
        if (empty($privilegeGroupArray)) {
            $privilegeGroupArray = array(
                array(
                    'meta_id'   => 0,
                    'meta_key'  => 'fake_privilege_group',
                    'meta_name' => 'fake权限组',
                    'meta_desc' => 'fake权限组',
                )
            );
        }

        // 把用户权限加入到权限组中
        foreach ($privilegeGroupArray as &$privilegeGroupItem) {
            if (array_key_exists($privilegeGroupItem['meta_id'], $groupIdToItemArray)) {
                $privilegeGroupItem['item_array'] = $groupIdToItemArray[$privilegeGroupItem['meta_id']];
            }
        }
        unset($privilegeGroupItem);

        // 把结果放入缓存，缓存 1 个小时
        $f3->set($this->privilegeArrayCacheId, $privilegeGroupArray, 3600);

        out:
        return $privilegeGroupArray;
    }
}
