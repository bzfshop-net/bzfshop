<?php

/**
 * @author QiangYu
 *
 * 收集所有的 log 日志
 *
 * */

namespace Core\Log;

class Collector extends Base
{

    private $logArray = array();

    /**
     * 取得日志数组
     *
     * @return array
     */
    public function getLogArray()
    {
        return $this->logArray;
    }

    /**
     * 加入一条日志信息
     *
     * @param string $level   日志等级
     * @param string $source  日志的来源，比如 'SQL'
     * @param string $msg     日志消息
     * */
    public function addLogInfo($level, $source, $msg)
    {
        // 简单的把日志放到数组中而已
        $this->logArray[] = array('level' => $level, 'source' => $source, 'msg' => $msg);
    }

}