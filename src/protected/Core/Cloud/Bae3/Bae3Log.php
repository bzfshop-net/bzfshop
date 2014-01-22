<?php

/**
 * @author QiangYu
 *
 * 输出日志到 File
 *
 * */

namespace Core\Cloud\Bae3;

use Core\Cloud\ICloudLog;

class Bae3Log extends \Prefab implements ICloudLog
{

    public $system = 'bzfshop';

    private $file = 'bzfshop.log';

    private $logKeyArray = array();

    /**
     *
     * @param string $logFileName 日志文件名
     */
    public function initLog($logFileName = null)
    {
        global $f3;
        $this->file = $f3->get('LOGS') . $this->system . '.log';

        if (!is_file($this->file)) {
            // 创建文件
            $fp = fopen($this->file, 'w');
            if (!$fp) {
                throw new \InvalidArgumentException('can not create file : ' . $this->file);
            }
            fclose($fp);
        }
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
        global $f3;
        if (!is_file($this->file)) {
            return;
        }

        $logKey = md5('##' . $level . '##' . $source . '##' . $msg . '##');

        // 重复的 log 信息就不要放进来了
        if (in_array($logKey, $this->logKeyArray)) {
            return;
        }

        $this->logKeyArray[] = $logKey;

        $msg = '[' . $level . '][' . $source . '][' . trim($msg) . ']'
            . PHP_EOL;
        $f3->write($this->file, $msg, true);
    }

}