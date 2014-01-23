<?php

/**
 * @author QiangYu
 *
 * 管理员操作日志记录
 *
 * */

namespace Core\Service\User;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Time;
use Core\Modal\SqlMapper as DataMapper;

class AdminLog extends \Core\Service\BaseService
{

    /**
     * 记录管理员的操作日志
     *
     * @param string $operate 操作
     * @param string $operate_desc 操作描述
     * @param string $operate_data 操作数据，用于记录一些重要数据
     */
    public static function logAdminOperate($operate, $operate_desc, $operate_data)
    {

        $dataMapper = new DataMapper('admin_log');
        $authAdminUser = AuthHelper::getAuthUser();
        $dataMapper->user_id = $authAdminUser['user_id'];
        $dataMapper->user_name = $authAdminUser['user_name'];
        $dataMapper->operate = $operate;
        $dataMapper->operate_desc = $operate_desc;
        $dataMapper->operate_time = Time::gmTime();
        $dataMapper->operate_data = $operate_data;
        $dataMapper->save();
        unset($dataMapper);
    }

    /**
     * 取得管理员日志列表
     *
     * @return array 格式 array(array('key'=>'value', 'key'=>'value', ...))
     *
     * @param array $condArray 查询条件
     * @param int $offset 用于分页的开始 >= 0
     * @param int $limit 每页多少条
     * @param int $ttl 缓存多少时间
     */
    public function fetchAdminLogArray(array $condArray, $offset = 0, $limit = 10, $ttl = 0)
    {
        return $this->_fetchArray(
            'admin_log',
            '*', // table , fields
            $condArray,
            array('order' => 'log_id desc'),
            $offset,
            $limit,
            $ttl
        );
    }

    /**
     * 取得管理员日志总数，可用于分页
     *
     * @return int
     *
     * @param array $condArray 查询条件
     * @param int $ttl 缓存多少时间
     */
    public function countAdminLogArray(array $condArray, $ttl = 0)
    {
        return $this->_countArray('admin_log', $condArray, null, $ttl);
    }

}
