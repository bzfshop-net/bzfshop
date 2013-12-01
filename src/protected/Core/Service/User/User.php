<?php

/**
 *
 * @author QiangYu
 *
 * 基本用户信息的操作
 *
 * */

namespace Core\Service\User;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;

class User extends \Core\Service\BaseService
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

        $user = $this->loadUserById($userId);

        // 用户不存在，返回 false
        if ($user->isEmpty()) {
            return false;
        }

        return ($user->password === $this->encryptPassword($password, $user->ec_salt));
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
    public function loadUserById($id, $ttl = 0)
    {
        return $this->_loadById('users', 'user_id=?', $id, $ttl);
    }

    /**
     * 判断用户是否已经存在，在我们的系统里，用户名和email都必须唯一，因为都可以用来登陆
     *
     * @return mixed 用户名已经存在则返回 $username，邮件已经存在则返回 $email，都不重复返回 false
     *
     * @param string $username 用户名
     * @param string $email    邮箱
     *
     * */
    public function isUserExist($username, $email)
    {

        // 参数验证
        if (Utils::isBlank($username) && Utils::isBlank($email)) {
            throw new \InvalidArgumentException('user_name, email can not both empty');
        }

        $user = new DataMapper('users');

        $sqlPrepare  = array();
        $sqlParam    = array();
        $sqlParam[0] = ''; // 查询语句

        if (!empty($username)) {
            $sqlPrepare[] = ' user_name = ? ';
            $sqlParam[]   = $username;
        }

        if (!empty($email)) {
            $sqlPrepare[] = ' email = ? ';
            $sqlParam[]   = $email;
        }

        $sqlParam[0] = implode(' or ', $sqlPrepare);
        $user->loadOne($sqlParam);

        // 用户不存在
        if ($user->isEmpty()) {
            return false;
        }

        if ($username == $user->user_name) {
            return $username;
        }

        return $email;
    }

    /**
     * 注册新用户
     *
     * @return mixed 成功-返回用户信息数组，失败-返回false
     *
     * @param array $userInfo  包含用户信息的数组，最终于需要包括 $userInfo['user_name'],$userInfo['email']
     *
     * */
    public function registerUser(array $userInfo)
    {

        // 参数验证
        if (Utils::isBlank($userInfo['user_name']) && Utils::isBlank($userInfo['email'])) {
            throw new \InvalidArgumentException('user_name, email can not both empty');
        }
        $validator = new Validator($userInfo);
        $validator->required()->validate('password');
        $this->validate($validator);

        if ($this->isUserExist($userInfo['user_name'], $userInfo['email'])) {
            return false;
        }

        $user = new DataMapper('users');
        $user->copyFrom($userInfo); //复制数据
        $user->ec_salt  = substr(uniqid(), -5);
        $user->password = $this->encryptPassword($user->password, $user->ec_salt); // 加密密码
        // 记录登录时间和IP地址
        $user->last_login = $user->reg_time = Time::gmTime();
        global $f3;
        $user->last_ip = $user->reg_ip = $f3->get('IP');
        return $user->save(); // 插入数据库
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
    public function doAuthUser($username, $email, $password)
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

        $user = new DataMapper('users');
        $user->loadOne($sqlParam);

        if ($user->isEmpty()) {
            return false;
        }

        // 验证密码
        if ($user->password !== $this->encryptPassword($password, $user->ec_salt)) {
            return false;
        }

        // 登录成功，记录登录时间和IP地址
        $user->last_login = Time::gmTime();
        global $f3;
        $user->last_ip = $f3->get('IP');
        $user->save();

        return $user;
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
    public function updateUser($user, array $input)
    {
        if (Utils::isEmpty($user) || Utils::isEmpty($input)) {
            throw new \InvalidArgumentException('$user, $input can not be empty');
        }

        // 去除掉一些关键的字段，不允许更新
        $password = null;
        if (!Utils::isBlank($input['password'])) {
            $password = $input['password'];
        }
        unset($input['id']);
        unset($input['user_id']);
        unset($input['password']);

        $user->copyFrom($input);
        if (!Utils::isBlank($password)) {
            $user->ec_salt  = substr(uniqid(), -10); //修改密码同时修改 salt，增强安全性
            $user->password = $this->encryptPassword($password, $user->ec_salt);
        }

        $user->save();
        return $user;
    }

    /**
     * 通过用户一组用户 id 取得所有用户信息列表
     *
     * @return array 用户信息列表
     *
     * @param array $userIdArray 用户 id 数组，例如： array(100,23,56,89)
     * @param int   $ttl         缓存时间
     * * */
    public function fetchUserArrayByUserIdArray(array $userIdArray, $ttl = 0)
    {
        // 参数验证
        $validator   = new Validator(array('userIdArray' => $userIdArray), '');
        $userIdArray = $validator->required()->requireArray(false)->validate('userIdArray');
        $this->validate($validator);

        return $this->_fetchArray(
            'users',
            '*', // table fields
            array(array(QueryBuilder::buildInCondition('user_id', $userIdArray, \PDO::PARAM_INT))), // condArray
            array('order' => 'user_id asc'),
            0,
            0,
            $ttl
        );
    }

    public function doAuthSnsUser($sns_login, $user_name, $email, $autoRegister = true)
    {
        global $f3;

        $user = new DataMapper('users');
        $user->loadOne(array('sns_login = ?', $sns_login), array('order' => 'user_id asc'));

        if (!$user->isEmpty()) {
            // 记录登录时间和IP地址
            $user->last_login = Time::gmTime();
            $user->last_ip    = $f3->get('IP');
            $user->save();
            return $user;
        }

        if (!$autoRegister) {
            return false;
        }

        // 自动注册用户
        $user->sns_login = $sns_login;
        $user->user_name = $user_name;
        $user->email     = $email;
        $user->password  = uniqid();

        // 记录登录时间和IP地址
        $user->last_login = $user->reg_time = Time::gmTime();
        $user->last_ip    = $user->reg_ip = $f3->get('IP');

        $user->save();

        return $user;
    }

}
