<?php

/**
 * @author QiangYu
 *
 * 用于调试，显示运行信息
 *
 * */

namespace Core\Log;

abstract class Base {
    /**
     * 定义不同的 Level
     */

    const CRITICAL = 'CRITICAL', ERROR = 'ERROR', WARN = 'ERROR', NOTICE = 'NOTICE', INFO = 'INFO', DEBUG = 'DEBUG';

    /**
     * 只接受这些 Level 的日志被输出
     */
    public $levelAllow = array();

    /**
     * 只接受这些 source 的日志被输出
     */
    public $sourceAllow = array();

    /**
     * 判段是否允许日志输出
     * 
     * @return boolean 
     * 
     * @param string $level 输出级别
     * @param string $source 输出源
     */
    public function isAllow($level, $source) {
        if (!empty($this->levelAllow) && !in_array($level, $this->levelAllow)) {
            return false;
        }

        if (!empty($this->sourceAllow) && !in_array($source, $this->sourceAllow)) {
            return false;
        }

        return true;
    }

    /**
     * 增加一个 Logger
     */
    public function addLogger($logger) {
        // do nothing
    }

    /**
     * 加入一条日志信息
     * 
     * @param string $level  日志等级
     * @param string $source  日志的来源，比如 'SQL'
     * @param string $msg  日志消息
     * */
    abstract public function addLogInfo($level, $source, $msg);
}