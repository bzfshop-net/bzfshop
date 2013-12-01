<?php

/**
 * @author QiangYu
 *
 * 一个工具集合类
 *
 * */

namespace Core\Helper\Utility;

final class Ajax {

    /**
     * 输出 ajax 头信息
     */
    public static function header() {
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * @return array
     * 
     * 统一所有 ajax 的输出结果格式，如下：
     * 
     * array(
     *  'error' => array('code' => 1, 'message' => 'xxxxx',)
     *  'data' => mixed
     * )
     * 
     * 如果 error 为空说明调用成功，可以去解析 data 字段，
     * 如果 error 有内容，说明调用失败，不要去解析 data 字段了
     * 
     * @param int $errorCode 错误码
     * @param string $errorMessage 错误消息
     * @param mixed data 返回数据
     * 
     */
    public static function buildResult($errorCode, $errorMessage, $data) {
        $result = array();

        if (null != $errorCode) {
            $result['error'] = array('code' => $errorCode, 'message' => $errorMessage);
        }

        if ($data) {
            $result['data'] = $data;
        }

        return json_encode($result);
    }

}