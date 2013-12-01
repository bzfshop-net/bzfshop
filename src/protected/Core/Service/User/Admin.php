<?php

/**
 *
 * @author QiangYu
 *
 * 管理员用户信息的操作
 *
 * */

namespace Core\Service\User;

use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;

class Admin extends \Core\Service\BaseService
{
    /**
     * 一个特殊权限，这个值代表所有权限，即现在已有和将来可能会增加的所有权限
     */
    const privilegeAll = 'all';

    /**
     * 验证是否有操作权限
     *
     * @return boolean
     *
     * @param string $needPrivilege     需要的权限，比如 'goods_manage'
     * @param string $privilegeListStr  用户所拥有的所有权限
     */
    public static function verifyPrivilege($needPrivilege, $privilegeListStr)
    {
        // 比如有 2 个权限， goods 和 goods_manage， 我们匹配 goods 有可能匹配到的其实是 goods_manage
        // 加上 , 后缀去匹配就不会出现这种错误了

        $needPrivilege = ',' . $needPrivilege . ',';

        if (false !== strpos($privilegeListStr, Admin::privilegeAll)) {
            return true;
        }

        $privilegeListStr = ',' . $privilegeListStr . ',';

        if (false === strpos($privilegeListStr, $needPrivilege)) {
            return false;
        }

        return true;
    }

    /**
     * 对密码做统一的加密操作
     *
     * @return string 加密后的密码
     *
     * @param string $password  加密前的密码明文
     * */
    private function encryptPassword($password, $salt)
    {
        /*
        $encrypt = md5($password);
        if (isset($salt)) {
            $encrypt = md5($encrypt . $salt);
        }
        return $encrypt;
        */

        // 为了兼容最土团购程序，这里改为最土的加密方式
        if (!empty($salt)) {
            return md5($password . $salt);
        }
        return md5($password);
    }

    /**
     * 验证用户密码是否正确
     *
     * @return boolean
     *
     * @param int    $userId   用户 id
     * @param string $password 密码
     */
    public function verifyPassword($userId, $password)
    {
        if (Utils::isEmpty($userId) || Utils::isBlank($password)) {
            throw new \InvalidArgumentException('user_id, password can not be empty');
        }

        $admin = $this->loadAdminById($userId);

        // 用户不存在，返回 false
        if ($admin->isEmpty()) {
            return false;
        }

        return ($admin->password === $this->encryptPassword($password, $admin->ec_salt));
    }

    /**
     * 根据用户 id 取得用户信息
     *
     * @return object 用户信息记录
     *
     * @param int $id  用户数字 ID
     * @param int $ttl 缓存时间
     *
     * */
    public function loadAdminById($id, $ttl = 0)
    {
        return $this->_loadById('admin_user', 'user_id=?', $id, $ttl);
    }

    /**
     * 根据管理员的账号取得唯一账号
     *
     * @param  string $user_name
     * @param int     $ttl
     *
     * @return DataMapper
     */
    public function loadAdminByUserName($user_name, $ttl = 0)
    {
        $dataMapper = new DataMapper('admin_user');
        $dataMapper->loadOne(array('user_name = ?', $user_name), array('order' => 'user_id asc'), $ttl);
        return $dataMapper;
    }

    /**
     * 用户认证，检查用户名密码是否正确
     *
     * @return mixed 失败-返回false，成功-返回用户信息
     *
     * @param string $username  用户名
     * @param string $email     邮箱
     * @param string $password  密码原文
     * */
    public function doAuthAdmin($username, $email, $password)
    {

        // 参数验证
        if (Utils::isBlank($username) && Utils::isBlank($email)) {
            throw new \InvalidArgumentException('user_name, email can not both empty');
        }

        $validator = new Validator(array('password' => $password));
        $password  = $validator->required()->validate('password');
        $this->validate($validator);

        $sqlPrepare  = array();
        $sqlParam    = array();
        $sqlParam[0] = ''; // 查询语句

        if (!Utils::isBlank($username)) {
            $sqlPrepare[] = 'user_name=?';
            $sqlParam[]   = $username;
        }
        if (!Utils::isBlank($email)) {
            $sqlPrepare[] = 'email=?';
            $sqlParam[]   = $email;
        }

        $sqlParam[0] = implode(' or ', $sqlPrepare);

        $admin = new DataMapper('admin_user');
        $admin->loadOne($sqlParam);

        if ($admin->isEmpty()) {
            return false;
        }

        // 禁止登陆
        if ($admin['disable']) {
            return false;
        }

        // 验证密码
        if ($admin->password !== $this->encryptPassword($password, $admin->ec_salt)) {
            return false;
        }

        return $admin;
    }

    /**
     * 更新用户信息到数据库
     *
     * @return object 更新之后的 user 对象
     *
     * @param object $admin  user对象
     * @param array  $input  需要更新的字段
     *
     * */
    public function updateAdmin($admin, array $input)
    {
        if (Utils::isEmpty($admin) || Utils::isEmpty($input)) {
            throw new \InvalidArgumentException('$admin, $input can not be empty');
        }

        // 去除掉一些关键的字段，不允许更新
        $password = null;
        if (isset($input['password']) && !Utils::isBlank($input['password'])) {
            $password = $input['password'];
        }
        unset($input['id']);
        unset($input['user_id']);
        unset($input['password']);

        $admin->copyFrom($input);
        if (!Utils::isBlank($password)) {
            $admin->ec_salt  = substr(uniqid(), -10); //修改密码同时修改 salt，增强安全性
            $admin->password = $this->encryptPassword($password, $admin->ec_salt);
        }

        $admin->save();
        return $admin;
    }

    /**
     * 取得管理员账号列表
     *
     * @return array 格式 array(array('key'=>'value', 'key'=>'value', ...))
     *
     * @param array $condArray 查询条件
     * @param int   $offset    用于分页的开始 >= 0
     * @param int   $limit     每页多少条
     * @param int   $ttl       缓存多少时间
     */
    public function fetchAdminArray(array $condArray, $offset = 0, $limit = 10, $ttl = 0)
    {
        return $this->_fetchArray(
            'admin_user',
            '*', // table , fields
            $condArray,
            array('order' => 'user_id desc'),
            $offset,
            $limit,
            $ttl
        );
    }

    /**
     * 取得管理员总数，可用于分页
     *
     * @return int
     *
     * @param array $condArray 查询条件
     * @param int   $ttl       缓存多少时间
     */
    public function countAdminArray(array $condArray, $ttl = 0)
    {
        return $this->_countArray('admin_user', $condArray, null, $ttl);
    }


}
