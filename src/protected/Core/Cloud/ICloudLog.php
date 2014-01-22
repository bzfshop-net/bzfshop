<?php
/**
 * @author QiangYu
 *
 * 云平台的日志系统实现，主要用于程序的日志输出
 *
 */

namespace Core\Cloud;


interface ICloudLog
{

    /**
     * 初始化日志系统
     *
     * @param string $logFileName
     *
     * @return mixed
     */
    public function initLog($logFileName = null);

    /**
     * @param string $level  log级别
     * @param string $source log来源
     * @param string $msg    log消息
     *
     * @return mixed
     */
    public function addLogInfo($level, $source, $msg);

} 