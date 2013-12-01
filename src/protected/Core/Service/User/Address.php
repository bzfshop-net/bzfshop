<?php

/**
 * @author QiangYu
 *
 * 用户的地址管理服务类
 *
 * */

namespace Core\Service\User;

/**
 *
 * @author QiangYu
 *
 * 基本用户信息的操作
 *
 * */
use \Core\Helper\Utility\Validator;
use \Core\Modal\SqlMapper as DataMapper;

class Address extends \Core\Service\BaseService {

    /**
     * 根据用户 id 取得用户的第一个地址
     *
     * @return object 用户信息记录
     * @param int $userId  用户数字 ID
     * @param int $ttl 缓存时间
     *
     * */
    public function loadUserFirstAddress($userId, $ttl = 0) {
        // 参数验证
        $validator = new Validator(array('userId' => $userId, 'ttl' => $ttl));
        $userId = $validator->required()->digits()->min(1)->validate('userId');
        $ttl = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        $user = new DataMapper('user_address');
        $user->loadOne(array('user_id=?', $userId), array('order' => 'address_id asc', 'offset' => 0, 'limit' => 1), $ttl);
        return $user;
    }

    /**
     * 更新用户的第一个地址信息
     * 
     * @return object 返回新的地址对象
     * 
     * @param int $userId  用户数字 ID
     * @param array $addressInfo 包含地址信息的数组
     */
    public function updateUserFirstAddress($userId, array $addressInfo) {
        // 参数验证
        $validator = new Validator(array('userId' => $userId, 'addressInfo' => $addressInfo));
        $userId = $validator->required()->digits()->min(1)->validate('userId');
        $addressInfo = $validator->required()->requireArray(false)->validate('addressInfo');
        $this->validate($validator);

        $firstAddress = $this->loadUserFirstAddress($userId);
        // 补充、修正数据
        $addressInfo['user_id'] = $userId;
        $firstAddress->copyFrom($addressInfo);
        $firstAddress->save();

        return $firstAddress;
    }

}
