<?php

/**
 * @author QiangYu
 *
 * 时间操作工具类
 *
 * */

namespace Core\Helper\Utility;

final class Time {

    /**
     * 获得格林威治时间戳
     * 
     * @return int
     */
    public static function gmTime() {
        return (time() - date('Z'));
    }

    /**
     * GM 时间转换为本地时间，一般用于从数据库中读取出的时间转换为本地时间显示
     * 
     * @return int 返回本地时间
     * @param int $gmTime GM时间
     * 
     */
    public static function gmTimeToLocalTime($gmTime) {
        if ($gmTime <= 0) {
            return 0;
        }
        return ($gmTime + date('Z'));
    }

    /**
     * GM 时间转换为中国时间，某些服务集成需要中国时间，比如财付通的支付网关
     * 
     * @return int 返回中国时间
     * @param int $gmTime GM时间
     * 
     */
    public static function gmTimeToChinaTime($gmTime) {
        if ($gmTime <= 0) {
            return 0;
        }
        return ($gmTime + 8 * 3600); //加 8 个小时
    }

    /**
     * 本地时间转换为 GM时间
     * 
     * @return int GM时间
     * @param int $localTime 本地时间
     * 
     */
    public static function localTimeToGmTime($localTime) {
        $gmTime = $localTime - date('Z');

        return $gmTime > 0 ? $gmTime : 0;
    }

    /**
     * 把字符串的时间转换为 GM 时间
     * 
     * @return int 时间戳
     * @param string $str 时间字符串
     */
    public static function gmStrToTime($str) {
        $time = strtotime($str);
        return ($time > 0) ? Time::localTimeToGmTime($time) : $time;
    }

    /**
     * 取得本地时间的字符串表示
     * 
     * @param string $format 时间格式显示
     */
    public static function localTimeStr($format = 'Y-m-d H:i:s') {
        return date($format);
    }

    /**
     * 把 gm 时间转换为本地时间，然后按照一定格式显示
     * 
     * @return string 
     * 
     * @param int $gmTime gm时间
     * @param string $format 时间显示格式，例如：'Y-m-d H:i:s'
     */
    public static function gmTimeToLocalTimeStr($gmTime, $format = 'Y-m-d H:i:s') {
        if ($gmTime <= 0) {
            return '';
        }
        $format = isset($format) ? $format : 'Y-m-d H:i:s';
        $localTime = Time::gmTimeToLocalTime($gmTime);
        return date($format, $localTime);
    }

}