<?php

/**
 * @author QiangYu
 *
 * Http 请求处理的工具类
 *
 * */

namespace Core\Helper\Utility;

final class Request
{

    /**
     * 判断请求是否 GET
     *
     * @return bool
     */
    public static function isRequestGet()
    {
        global $f3;
        return 'GET' == $f3->get('VERB');
    }

    /**
     * 判断请求是否 POST
     *
     * @return bool
     */
    public static function isRequestPost()
    {
        global $f3;
        return 'POST' == $f3->get('VERB');
    }

}