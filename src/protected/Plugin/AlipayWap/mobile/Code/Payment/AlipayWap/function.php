<?php
/**
 *功能：支付宝接口公用函数
 *详细：该页面是请求、通知返回两个文件所调用的公用函数核心处理文件，不需要修改
 *版本：2.0
 *日期：2011-09-01
 *说明：
 *以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 *该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */

/**生成签名结果
 * $array要签名的数组
 * return 签名结果字符串
 */
function build_mysign($sort_array, $key, $sign_type = "MD5")
{
    $prestr = create_linkstring($sort_array); //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
    $prestr = $prestr . $key; //把拼接后的字符串再与安全校验码直接连接起来
    $mysgin = sign($prestr, $sign_type); //把最终的字符串签名，获得签名结果
    return $mysgin;
}

/********************************************************************************/

/**把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * $array 需要拼接的数组
 * return 拼接完成以后的字符串
 */
function create_linkstring($array)
{
    $arg = "";
    while (list ($key, $val) = each($array)) {
        $arg .= $key . "=" . $val . "&";
    }
    $arg = substr($arg, 0, count($arg) - 2); //去掉最后一个&字符
    return $arg;
}

/********************************************************************************/

/**除去数组中的空值和签名参数
 * $parameter 签名参数组
 * return 去掉空值与签名参数后的新签名参数组
 */
function para_filter($parameter)
{
    $para = array();
    while (list ($key, $val) = each($parameter)) {
        if ($key == "sign" || $key == "sign_type" || $val == "") {
            continue;
        }
        else    {
            $para[$key] = $parameter[$key];
        }
    }
    return $para;
}

/********************************************************************************/

/**对数组排序
 * $array 排序前的数组
 * return 排序后的数组
 */
function arg_sort($array)
{
    ksort($array);
    reset($array);
    return $array;
}

/********************************************************************************/

/**签名字符串
 * $prestr 需要签名的字符串
 * $sign_type 签名类型，也就是sec_id
 * return 签名结果
 */
function sign($prestr, $sign_type)
{
    $sign = '';
    if ($sign_type == 'MD5') {
        $sign = md5($prestr);
    } elseif ($sign_type == 'DSA') {
        //DSA 签名方法待后续开发
        die("DSA 签名方法待后续开发，请先使用MD5签名方式");
    } else {
        die("支付宝暂不支持" . $sign_type . "类型的签名方式");
    }
    return $sign;
}

/********************************************************************************/

/**日志消息,把支付宝返回的参数记录下来
 * 请注意服务器是否开通fopen配置
 */
function  log_result($word)
{
    $fp = fopen("log.txt", "a");
    flock($fp, LOCK_EX);
    fwrite($fp, "执行日期：" . strftime("%Y%m%d%H%M%S", time()) . "\n" . $word . "\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

/********************************************************************************/

/**实现多种字符编码方式
 *$input 需要编码的字符串
 *$_output_charset 输出的编码格式
 *$_input_charset 输入的编码格式
 *return 编码后的字符串
 */
function charset_encode($input, $_output_charset, $_input_charset)
{
    $output = "";
    if (!isset($_output_charset)) {
        $_output_charset = $_input_charset;
    }
    if ($_input_charset == $_output_charset || $input == null) {
        $output = $input;
    } elseif (function_exists("mb_convert_encoding")) {
        $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
    } elseif (function_exists("iconv")) {
        $output = iconv($_input_charset, $_output_charset, $input);
    } else {
        die("sorry, you have no libs support for charset change.");
    }
    return $output;
}

/********************************************************************************/

/** 实现多种字符解码方式
 * $input 需要解码的字符串
 * $_output_charset 输出的解码格式
 * $_input_charset 输入的解码格式
 * return 解码后的字符串
 */
function charset_decode($input, $_input_charset, $_output_charset)
{
    $output = "";
    if (!isset($_input_charset)) {
        $_input_charset = $_input_charset;
    }
    if ($_input_charset == $_output_charset || $input == null) {
        $output = $input;
    } elseif (function_exists("mb_convert_encoding")) {
        $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
    } elseif (function_exists("iconv")) {
        $output = iconv($_input_charset, $_output_charset, $input);
    } else {
        die("sorry, you have no libs support for charset changes.");
    }
    return $output;
}

/********************************************************************************/

/**
 * 通过节点路径返回字符串的某个节点值
 * $res_data——XML 格式字符串
 * 返回节点参数
 */
function getDataForXML($res_data, $node)
{
    $xml    = simplexml_load_string($res_data);
    $result = $xml->xpath($node);

    while (list(, $node) = each($result)) {
        return $node;
    }
}
