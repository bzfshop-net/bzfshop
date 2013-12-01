<?php

/**
 *
 * @author QiangYu
 *
 * 供货商用户信息的操作
 *
 * */

namespace Core\Service\User;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;

class Supplier extends \Core\Service\BaseService
{

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

        $supplier = $this->loadSupplierById($userId);

        // 用户不存在，返回 false
        if ($supplier->isEmpty()) {
            return false;
        }

        return ($supplier->password === $this->encryptPassword($password, $supplier->ec_salt));
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
    public function loadSupplierById($id, $ttl = 0)
    {
        return $this->_loadById('suppliers', 'suppliers_id=?', $id, $ttl);
    }

    /**
     * 根据供货商账号取得供货商
     *
     * @param  string $suppliers_account
     * @param int     $ttl
     *
     * @return DataMapper
     */
    public function loadSupplierBySupplierAccount($suppliers_account, $ttl = 0)
    {
        $dataMapper = new DataMapper('suppliers');
        $dataMapper->loadOne(
            array('suppliers_account = ?', $suppliers_account),
            array('order' => 'suppliers_id asc'),
            $ttl
        );
        return $dataMapper;
    }

    /**
     * 用户认证，检查用户名密码是否正确
     *
     * @return mixed 失败-返回false，成功-返回用户信息
     *
     * @param string $username  用户名
     * @param string $password  密码原文
     * */
    public function doAuthSupplier($username, $password)
    {
        // 参数验证
        $validator = new Validator(array('username' => $username, 'password' => $password));
        $username  = $validator->required()->validate('username');
        $password  = $validator->required()->validate('password');
        $this->validate($validator);

        $supplier = new DataMapper('suppliers');
        $supplier->loadOne(array('suppliers_account = ?', $username));

        if ($supplier->isEmpty()) {
            return false;
        }

        // 验证密码
        if ($supplier->password !== $this->encryptPassword($password, $supplier->ec_salt)) {
            return false;
        }

        return $supplier;
    }

    /**
     * 更新用户信息到数据库
     *
     * @return object 更新之后的 user 对象
     *
     * @param object $user   user对象
     * @param array  $input  需要更新的字段
     *
     * */
    public function updateSupplier($supplier, array $input)
    {
        if (Utils::isEmpty($supplier) || Utils::isEmpty($input)) {
            throw new \InvalidArgumentException('supplier, $input can not be empty');
        }

        // 去除掉一些关键的字段，不允许更新   
        $password = null;
        if (isset($input['password']) && !Utils::isBlank($input['password'])) {
            $password = $input['password'];
        }
        unset($input['suppliers_id']);
        unset($input['password']);

        $supplier->copyFrom($input);
        if (!Utils::isBlank($password)) {
            $supplier->ec_salt  = substr(uniqid(), -10); //修改密码同时修改 salt，增强安全性
            $supplier->password = $this->encryptPassword($password, $supplier->ec_salt);
        }

        $supplier->save();
        return $supplier;
    }

    /**
     * 取得供货商账号列表
     *
     * @return array 格式 array(array('key'=>'value', 'key'=>'value', ...))
     *
     * @param array $condArray 查询条件
     * @param int   $offset    用于分页的开始 >= 0
     * @param int   $limit     每页多少条
     * @param int   $ttl       缓存多少时间
     */
    public function fetchSupplierArray(array $condArray, $offset = 0, $limit = 10, $ttl = 0)
    {
        return $this->_fetchArray(
            'suppliers',
            '*', // table , fields
            $condArray,
            array('order' => 'suppliers_id desc'),
            $offset,
            $limit,
            $ttl
        );
    }

    /**
     * 取得供货商总数，可用于分页
     *
     * @return int
     *
     * @param array $condArray 查询条件
     * @param int   $ttl       缓存多少时间
     */
    public function countSupplierArray(array $condArray, $ttl = 0)
    {
        return $this->_countArray('suppliers', $condArray, null, $ttl);
    }


    /**
     * 用一组供货商 ID 一次批量取得供货商记录
     *
     * @param array $supplierIdArray
     * @param int   $ttl
     *
     * @return array
     */
    public function fetchSupplierArrayBySupplierIdArray(array $supplierIdArray, $ttl = 0)
    {
        // 参数验证
        $validator       = new Validator(array('supplierIdArray' => $supplierIdArray), '');
        $supplierIdArray = $validator->required()->requireArray(false)->validate('supplierIdArray');
        $this->validate($validator);

        return $this->_fetchArray(
            'suppliers',
            '*', // table fields
            array(array(QueryBuilder::buildInCondition('suppliers_id', $supplierIdArray, \PDO::PARAM_INT))),
            // condArray
            array('order' => 'suppliers_id asc'),
            0,
            0,
            $ttl
        );
    }

}
