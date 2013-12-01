<?php

/**
 * @author QiangYu
 *
 * 输出日志到 Console
 *
 * */

namespace Core\Log;

use Core\Helper\Utility\Time;

class Console extends Base
{

    /**
     * 加入一条日志信息
     *
     * @param string $level   日志等级
     * @param string $source  日志的来源，比如 'SQL'
     * @param string $msg     日志消息
     * */
    public function addLogInfo($level, $source, $msg)
    {
        $msg = '[' . Time::localTimeStr('Y-m-d H:i:s') . '][' . $level . '][' . $source . '][' . trim($msg) . ']'
            . PHP_EOL;
        echo $msg;
        flush();
        unset($msg);
    }

}