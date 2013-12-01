<?php

/**
 *
 * @author QiangYu
 *
 * 用户角色信息操作
 *
 * */

namespace Core\Service\Meta;

use Core\Helper\Utility\Validator;

class Role extends Meta
{
    const META_TYPE = 'privilege_role';

    /**
     * 取得所有角色列表
     *
     * @param int $ttl 缓存时间
     *
     * @return array
     */
    public function fetchRoleArray($ttl = 0)
    {
        return $this->_fetchArray(
            'meta',
            '*',
            array(array('meta_type = ?', Role::META_TYPE)),
            array('order' => 'meta_sort_order desc, meta_id desc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 取得唯一的 role
     *
     * @param     $id
     * @param int $ttl
     *
     * @return \Core\Modal\SqlMapper
     */
    public function loadRoleById($id, $ttl = 0)
    {
        return $this->loadMetaByTypeAndId(Role::META_TYPE, $id, $ttl);
    }

    /**
     * 保存或者新建一个 role
     *
     * @param int    $id
     * @param string $roleName
     * @param string $roleDesc
     * @param string $roleActionListStr
     *
     * @return \Core\Modal\SqlMapper
     */
    public function saveRole($id, $roleName, $roleDesc, $roleActionListStr)
    {
        $roleItem            = $this->loadRoleById($id);
        $roleItem->meta_type = Role::META_TYPE;
        $roleItem->meta_name = $roleName;
        $roleItem->meta_desc = $roleDesc;
        $roleItem->meta_data = $roleActionListStr;
        $roleItem->save();

        return $roleItem;
    }

    /**
     * 删除一个角色
     *
     * @param int $id
     */
    public function removeRole($id)
    {
        $roleItem = $this->loadRoleById($id);
        if (!$roleItem->isEmpty()) {
            $roleItem->erase();
        }
    }
}
