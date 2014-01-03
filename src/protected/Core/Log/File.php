<?php

/**
 * @author QiangYu
 *
 * 输出日志到 File
 *
 * */

namespace Core\Log;

use Core\Cloud\CloudHelper;

class File extends Base
{

    // 云平台对应的日志引擎
    private $cloudEngineLog = null;

    /**
     *
     * @param string $logFileName 日志文件名
     */
    function __construct($logFileName = null)
    {
        // 取得云平台 日志模块
        $this->cloudEngineLog = CloudHelper::getCloudModule(CloudHelper::CLOUD_MODULE_Log);
        // 初始化日志引擎
        $this->cloudEngineLog->initLog($logFileName);
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
        // 日志输出到云引擎中
        $this->cloudEngineLog->addLogInfo($level, $source, $msg);
    }

}