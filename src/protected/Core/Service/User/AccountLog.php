<?php

/**
 * @author QiangYu
 *
 * 用户的账户资金变动管理
 *
 * */

namespace Core\Service\User;

use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;

/**
 *
 * @author QiangYu
 *
 * 基本用户信息的操作
 *
 * */
class AccountLog extends \Core\Service\BaseService
{
    /* 帐号变动类型 */

    const ACT_SAVING  = 0, // 帐户冲值
        ACT_DRAWING   = 1, // 帐户提款
        ACT_ADJUSTING = 2, // 调节帐户
        ACT_OTHER     = 99; // 其他类型

    public static $changeTypeDesc = array(
        AccountLog::ACT_SAVING    => '帐户冲值',
        AccountLog::ACT_DRAWING   => '帐户提款',
        AccountLog::ACT_ADJUSTING => '帐户条件',
        AccountLog::ACT_OTHER     => '其他类型',
    );

    /**
     * 记录帐户变动
     * @param   int    $userId        用户id
     * @param   float  $userMoney     可用余额变动
     * @param   float  $frozenMoney   冻结余额变动
     * @param   int    $rankPoints    等级积分变动
     * @param   int    $payPoints     消费积分变动
     * @param   string $changeDesc    变动说明
     * @param   int    $changeType    变动类型：参见常量文件
     *
     * @return  void
     */
    function logChange(
        $userId,
        $userMoney = 0,
        $frozenMoney = 0,
        $rankPoints = 0,
        $payPoints = 0,
        $changeDesc = '',
        $changeType = AccountLog::ACT_OTHER,
        $adminUserId = 0
    ) {
        /* 插入帐户变动记录 */
        $accountLogInfo = array(
            'user_id'       => $userId,
            'user_money'    => $userMoney,
            'frozen_money'  => $frozenMoney,
            'rank_points'   => $rankPoints,
            'pay_points'    => $payPoints,
            'change_time'   => Time::gmTime(),
            'change_desc'   => $changeDesc,
            'change_type'   => $changeType,
            'admin_user_id' => $adminUserId
        );
        // 插入一条记录
        $dataMapper = new DataMapper('account_log');
        $dataMapper->copyFrom($accountLogInfo);
        $dataMapper->save();

        // 更新用户信息
        $sql      = "UPDATE " . DataMapper::tableName('users') .
            " SET user_money = user_money + ('$userMoney')," .
            " frozen_money = frozen_money + ('$frozenMoney')," .
            " rank_points = rank_points + ('$rankPoints')," .
            " pay_points = pay_points + ('$payPoints')" .
            " WHERE user_id = ? Order By user_id asc LIMIT 1 ";
        $dbEngine = DataMapper::getDbEngine();
        $dbEngine->exec($sql, $userId);
    }

    /**
     * 返回用户有多少 acount_log 数量
     *
     * @return int
     *
     * @param int    $userId 用户 ID
     * @param string $filter 查询条件
     * @param int    $ttl    缓存时间
     */
    public function countUserAccountLogArray($userId, $filter, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('userId' => $userId, 'filter' => $filter));
        $userId    = $validator->required()->digits()->min(1)->validate('userId');
        $filter    = $validator->validate('filter');
        $this->validate($validator);

        if (!empty($filter)) {
            $filter .= ' and user_id = ?';
        } else {
            $filter = 'user_id = ?';
        }

        return $this->_countArray('account_log', array(array($filter, $userId)), null, $ttl);
    }

    /**
     * 取得用户的 account_log 列表
     *
     * @return array 格式 array(array('key'=>'value', 'key'=>'value', ...))
     *
     * @param int    $userId 用户 ID
     * @param string $filter 查询条件
     * @param int    $offset 用于分页的开始 >= 0
     * @param int    $limit  每页多少条
     * @param int    $ttl    缓存多少时间
     */
    public function fetchUserAccountLogArray($userId, $filter, $offset = 0, $limit = 10, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array(
                                        'userId' => $userId,
                                        'filter' => $filter,
                                        'offset' => $offset,
                                        'limit'  => $limit,
                                        'ttl'    => $ttl
                                   ));
        $userId    = $validator->required()->digits()->min(1)->validate('userId');
        $filter    = $validator->validate('filter');
        $offset    = $validator->digits()->min(0)->validate('offset');
        $limit     = $validator->required()->digits()->min(1)->validate('limit');
        $ttl       = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        // 需要联表查询
        $accountLog = new DataMapper('account_log');
        if (!empty($filter)) {
            $filter .= ' and user_id = ?';
        } else {
            $filter = 'user_id = ?';
        }
        return $accountLog->find(
            array($filter, $userId),
            array('order' => 'log_id desc', 'offset' => $offset, 'limit' => $limit),
            $ttl
        );
    }

    /**
     * 返回用户有多少 acount_log (资金变动) 数量
     *
     * @return int
     *
     * @param int $userId 用户 ID
     * @param int $ttl    缓存时间
     */
    public function countUserMoneyArray($userId, $ttl = 0)
    {
        return $this->countUserAccountLogArray($userId, ' user_money != 0 ', $ttl);
    }

    /**
     * 取得用户的 account_log (资金变动) 列表
     *
     * @return array 格式 array(array('key'=>'value', 'key'=>'value', ...))
     *
     * @param int $userId 用户 ID
     * @param int $offset 用于分页的开始 >= 0
     * @param int $limit  每页多少条
     * @param int $ttl    缓存多少时间
     */
    public function fetchUserMoneyArray($userId, $offset = 0, $limit = 10, $ttl = 0)
    {
        return $this->fetchUserAccountLogArray($userId, ' user_money != 0 ', $offset, $limit, $ttl);
    }

}
