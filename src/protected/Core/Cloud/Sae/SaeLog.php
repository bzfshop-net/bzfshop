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

class SaeLog implements ICloudLog
{

    /**
     *
     * @param string $logFileName 日志文件名
     */
    public function initLog($logFileName = null)
    {
        sae_set_display_errors(false); //关闭信息输出
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
        $msg = '[' . Time::localTimeStr('Y-m-d H:i:s') . '][' . $level . '][' . $source . '][' . trim($msg) . ']'
            . PHP_EOL;
        sae_debug($msg); //记录日志
    }

}