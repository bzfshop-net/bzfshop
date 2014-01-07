<?php

/**
 * @author QiangYu
 *
 * SAE 日志输出，我们采用 sae_debug 输出到 debug 日志
 *
 * */

namespace Core\Cloud\Sae;

use Core\Cloud\ICloudLog;
use Core\Helper\Utility\Time;

class SaeLog extends \Prefab implements ICloudLog
{
    private $logArray = array();

    private $logKeyArray = array();

    function __construct()
    {
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
            'time'   => Time::localTimeStr('Y-m-d H:i:s'),
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

        sae_set_display_errors(false); //关闭信息输出
        foreach ($this->logArray as $logItem) {
            // 采用 sae_debug 输出日志
            sae_debug(
                '[' . $logItem['time'] . '][' . $logItem['level'] . '][' . $logItem['source'] . ']['
                . trim($logItem['msg']) . ']'
            );
        }
    }
}


