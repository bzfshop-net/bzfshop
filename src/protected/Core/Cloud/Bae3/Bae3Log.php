<?php

/**
 * @author QiangYu
 *
 * BAE3 的 log 实现，我们采用 BAE 的 log 服务输出日志
 *
 * */

namespace Core\Cloud\Bae3;

use Core\Cloud\ICloudLog;
use Core\Log\Base as BaseLog;

class Bae3Log extends \Prefab implements ICloudLog
{
    private $logArray = array();

    private $logKeyArray = array();

    function __construct()
    {
        // 在 php 程序结束的时候才输出 log，
        // 因为 bae log 服务是网络操作，可能会拖慢整个程序的执行
        register_shutdown_function(array($this, 'outputLog'));
    }

    /**
     *
     * @param string $logFileName 日志文件名
     */
    public function initLog($logFileName = null)
    {

    }

    /**
     * 加入一条日志信息
     *
     * @param string $level  日志等级
     * @param string $source 日志的来源，比如 'SQL'
     * @param string $msg    日志消息
     * */
    public function addLogInfo($level, $source, $msg)
    {
        $logKey = md5('##' . $level . '##' . $source . '##' . $msg . '##');

        // 重复的 log 信息就不要放进来了
        if (in_array($logKey, $this->logKeyArray)) {
            return;
        }

        $this->logKeyArray[] = $logKey;

        // 简单的把日志放到数组中而已
        $this->logArray[] = array(
            'level'  => $level,
            'source' => $source,
            'msg'    => $msg
        );
    }

    public function outputLog()
    {
        if (empty($this->logArray)) {
            return;
        }

        require_once dirname(__FILE__) . '/sdk/BaeLog.class.php';

        global $f3;
        $logger = BaeLog::getInstance(
            array('user' => $f3->get('sysConfig[bae3_api_key]'), 'passwd' => $f3->get('sysConfig[bae3_secret_key]'))
        );

        if (!$logger) {
            return;
        }
        if ($f3->get('DEBUG')) {
            $logger->setLogLevel(16);
        }

        // 输出 日志
        foreach ($this->logArray as $logItem) {

            $logger->setLogTag($logItem['source']);

            switch ($logItem['level']) {
                case BaseLog::CRITICAL:
                case BaseLog::ERROR:
                    $logger->Fatal($logItem['msg']);
                    break;
                case BaseLog::WARN:
                    $logger->Warning($logItem['msg']);
                    break;
                case BaseLog::NOTICE:
                case BaseLog::INFO:
                    $logger->Notice($logItem['msg']);
                    break;
                case BaseLog::DEBUG:
                    $logger->Debug($logItem['msg']);
                    break;
                default: // do nothing
            }
        }
    }
}


