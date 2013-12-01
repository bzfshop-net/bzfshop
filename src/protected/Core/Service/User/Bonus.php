<?php

/**
 *
 * @author QiangYu
 *
 * 用户红包的操作
 *
 * */

namespace Core\Service\User;

use \Core\Helper\Utility\Validator;
use \Core\Modal\SqlMapper as DataMapper;
use \Core\Helper\Utility\Time;

class Bonus extends \Core\Service\BaseService {

    const BONUS_AVAILABLE = 0, /** bonus 可用 */
            BONUS_NOT_EXIST = 1, /** bonus sn 不存在 */
            BONUS_OCCUPIED = 2, /** bonus 已经被别人用了 */
            BONUS_EXPIRED = 3, /** bonus 过期了 */
            BONUS_NOTSTART = 4;/** bonus 还没到使用期 */

    /**
     * 返回用户有多少个 bonus，数量
     * 
     * @return int
     * @param int $userId 用户 ID
     * @param int $ttl 缓存时间
     */
    public function countUserBonus($userId, $ttl = 0) {
        // 参数验证
        $validator = new Validator(array('userId' => $userId));
        $userId = $validator->required()->digits()->min(1)->validate('userId');
        $this->validate($validator);

        return $this->_countArray('user_bonus', array(array('user_id = ?', $userId)), null, $ttl);
    }

    /**
     * 返回用户有多少个可用 bonus，数量
     * 
     * @return int
     * @param int $userId 用户 ID
     * @param int $ttl 缓存时间
     */
    public function countValidUserBonus($userId, $ttl = 0) {
        // 参数验证
        $validator = new Validator(array('userId' => $userId));
        $userId = $validator->required()->digits()->min(1)->validate('userId');
        $this->validate($validator);

        return $this->_countArray('user_bonus', array(array('used_time = 0 and user_id = ?', $userId)), null, $ttl);
    }

    /**
     * 取得用户的 Bonus 列表
     * @return array 格式 array(array('key'=>'value', 'key'=>'value', ...))
     * @param int $userId 用户 ID
     * @param int $offset 用于分页的开始 >= 0
     * @param int $limit 每页多少条
     * @param int $ttl 缓存多少时间
     */
    public function fetchUserBonusArray($userId, $offset = 0, $limit = 10, $ttl = 0) {
        // 参数验证
        $validator = new Validator(array('userId' => $userId));
        $userId = $validator->required()->digits()->min(1)->validate('userId');
        $this->validate($validator);

        return $this->_fetchArray(array('user_bonus' => 'ub', 'bonus_type' => 'bt'), '*', // table fields
                        array(array('ub.bonus_type_id = bt.type_id and ub.user_id=?', $userId)), // condArray
                        array('order' => 'bonus_id desc'), // optionArray
                        $offset, $limit, $ttl);
    }

    /**
     * 通过 ID 取得 bonus 记录
     * 
     * @return object
     * @param int $id bonus的数字id
     * @param int $ttl 缓存时间
     */
    public function loadBonusById($id, $ttl = 0) {
        // 参数验证
        $validator = new Validator(array('id' => $id));
        $id = $validator->required()->digits()->min(1)->validate('id');
        $this->validate($validator);

        return $this->_loadById('user_bonus', 'bonus_id=?', $id, $ttl);
    }

    /**
     * 通过 sn 号取得 bonus 记录
     * 
     * @return object
     * @param string $sn SN号码
     * @param int $ttl 缓存时间
     */
    public function loadBonusBySn($sn, $ttl = 0) {
        // 参数验证
        $validator = new Validator(array('sn' => $sn, 'ttl' => $ttl));
        $sn = $validator->required()->validate('sn');
        $ttl = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        $userBonus = new DataMapper('user_bonus');
        $userBonus->loadOne(array('bonus_sn=?', $sn), null, $ttl);
        return $userBonus;
    }

    /**
     * Bonus 是否可用
     * 
     * @return string 枚举类型 BONUS_XXXX，见常量定义
     * 
     * @param string $sn  SN号码
     */
    public function isBonusAvailable($sn) {
        // 参数验证
        $validator = new Validator(array('sn' => $sn));
        $sn = $validator->required()->digits()->validate('sn');
        $this->validate($validator);

        // 需要联表查询
        $dataMapper = new DataMapper(null);
        $result = $dataMapper->selectComplex(array('user_bonus' => 'ub', 'bonus_type' => 'bt'), '*', array('ub.bonus_type_id = bt.type_id and ub.bonus_sn= ?', $sn), array('order' => 'bonus_id', 'offset' => 0, 'limit' => 1));

        if (empty($result) || empty($result[0])) {
            return Bonus::BONUS_NOT_EXIST;
        }

        $result = $result[0];

        if (!empty($result['user_id'])) {
            return Bonus::BONUS_OCCUPIED;
        }

        if ($result['use_end_date'] <= Time::gmTime()) {
            return Bonus::BONUS_EXPIRED;
        }

        return Bonus::BONUS_AVAILABLE;
    }

    /**
     * 领取这个 Bonus
     * 
     * @param int $userId 用户 ID
     * @param string $sn SN号码
     */
    public function occupyBonus($userId, $sn) {
        // 参数验证
        $validator = new Validator(array('userId' => $userId, 'sn' => $sn));
        $userId = $validator->required()->digits()->min(1)->validate('userId');
        $sn = $validator->required()->validate('sn');
        $this->validate($validator);

        $bonus = $this->loadBonusBySn($sn);
        if (empty($bonus)) {
            throw new \InvalidArgumentException('illegal $sn');
        }
        $bonus->user_id = $userId;
        $bonus->save();
    }

    /**
     * 使用这个 bonus
     * 
     * @param int $bonusId 红包的数字 ID
     * @param int $oderId  订单 ID
     */
    public function useBonus($bonusId, $orderId) {
        // 参数验证
        $validator = new Validator(array('bonusId' => $bonusId, 'orderId' => $orderId));
        $bonusId = $validator->required()->digits()->min(1)->validate('bonusId');
        $orderId = $validator->required()->digits()->min(1)->validate('orderId');
        $this->validate($validator);

        $bonus = $this->loadBonusById($bonusId);
        if ($bonus->isEmpty() || !empty($bonus->used_time)) {
            throw new \InvalidArgumentException('bonusId[' . $bonusId . '] invalid');
        }

        $bonus->used_time = Time::gmTime();
        $bonus->order_id = $orderId;
        $bonus->save();
    }

    /**
     * 取消 bonus 的使用
     * 
     * @param int $bonusId 红包的数字 ID
     */
    public function unUseBonus($bonusId) {
        // 参数验证
        $validator = new Validator(array('bonusId' => $bonusId));
        $bonusId = $validator->required()->digits()->min(1)->validate('bonusId');
        $this->validate($validator);

        $bonus = $this->loadBonusById($bonusId);
        if ($bonus->isEmpty() || empty($bonus->used_time)) {
            throw new \InvalidArgumentException('bonusId[' . $bonusId . '] invalid');
        }

        $bonus->used_time = 0;
        $bonus->order_id = 0;
        $bonus->save();
    }

    /**
     * 取得用户当前可用的红包列表，我们只返回最多 10 个红包
     * 
     * @return array 
     * 
     * @param int $userId 用户数字ID
     * @param float $price 订单金额，红包有一个最小使用金额，不能低于这个金额
     * @param int $limit 限制返回的数目，缺省为 10 
     * @param int $ttl 缓存时间
     * 
     */
    public function fetchAvailableBonusArray($userId, $price, $limit = 10, $ttl = 0) {
        // 参数验证
        $validator = new Validator(array('userId' => $userId, 'price' => $price));
        $userId = $validator->required()->digits()->min(1)->validate('userId');
        $price = $validator->required()->float()->min(0)->validate('price');
        $this->validate($validator);

        $currentGmTime = Time::gmTime();

        $condArray = array();
        $condArray[] = array('ub.bonus_type_id = bt.type_id and ub.used_time is null or ub.used_time = 0');
        $condArray[] = array('bt.use_start_date <= ? and bt.use_end_date > ?', $currentGmTime, $currentGmTime);
        $condArray[] = array('bt.min_goods_amount < ?', $price);
        $condArray[] = array('ub.user_id=?', $userId);

        return $this->_fetchArray(array('user_bonus' => 'ub', 'bonus_type' => 'bt'), '*', //table fields
                        $condArray, array('order' => 'bonus_id asc'), 0, $limit, $ttl);
    }

    /**
     * 取得用户现在可以使用的 bonus
     * 
     * @return array
     * 
     * @param int $userId 用户ID
     * @param float $price 订单金额
     * @param string $sn bonus 的 SN 编号
     */
    public function fetchUsableBonusBySn($userId, $price, $sn) {
        // 参数验证
        $validator = new Validator(array('userId' => $userId, 'price' => $price, 'sn' => $sn));
        $userId = $validator->required()->digits()->min(1)->validate('userId');
        $price = $validator->required()->float()->min(0)->validate('price');
        $sn = $validator->required()->digits()->validate('sn');
        $this->validate($validator);

        $currentGmTime = Time::gmTime();

        $condArray = array();
        $condArray[] = array('ub.bonus_type_id = bt.type_id and ub.used_time is null or ub.used_time = 0');
        $condArray[] = array('bt.use_start_date <= ? and bt.use_end_date > ?', $currentGmTime, $currentGmTime);
        $condArray[] = array('bt.min_goods_amount < ?', $price);
        $condArray[] = array('ub.user_id=?', $userId);
        $condArray[] = array('ub.bonus_sn = ?', $sn);

        $result = $this->_fetchArray(array('user_bonus' => 'ub', 'bonus_type' => 'bt'), '*', // table fields
                $condArray, array('order' => 'bonus_id asc'), 0, 1);

        if (empty($result)) {
            return null;
        }
        return $result[0];
    }

}
