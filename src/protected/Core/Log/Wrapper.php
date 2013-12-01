<?php

/**
 * @author QiangYu
 *
 * Wrapper 用于包含多个 logger，这样同一份日志可以在多个地方被输出
 *
 * */

namespace Core\Log;

class Wrapper extends Base {

    private $loggerArray = array();

    /**
     * 增加一个 Logger
     */
    public function addLogger($logger) {
        if (!$logger instanceof Base) {
            throw new \InvalidArgumentException('logger should be instance of \Core\Log\Base');
        }
        $this->loggerArray[] = $logger;
    }

    /**
     * 把日志信息分到不同的 logger 中
     * 
     * @param string $level  日志等级
     * @param string $source  日志的来源，比如 'SQL'
     * @param string $msg  日志消息
     * */
    public function addLogInfo($level, $source, $msg) {
        foreach ($this->loggerArray as $logger) {
            if ($logger->isAllow($level, $source)) {
                $logger->addLogInfo($level, $source, $msg);
            }
        }
    }

}