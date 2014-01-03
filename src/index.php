<?php

/*
 * @author QiangYu
 *
 * 这个 index.php 是专门为 小白 用户增加的
 *
 * 虽然程序在发布的时候明确的写了必须 PHP > 5.3.4 ，但是还是有很多小白用户拿着 PHP 5.2 去安装，程序一打开就出现编译错误
 *
 * 这些小白用户根本不知道什么是 5.2, 5.3.4 ，他们甚至不知道该如何看自己的 PHP 版本是多少，
 * 只会一遍一遍的QQ去问 为什么不能跑？ 为什么不能跑？ ....
 *
 * 他们只会 FTP 上传，然后打开浏览器访问程序，除此之外什么都不会，完全不懂技术，一点基本的常识都没有
 *
 * 我们需要一次一次的给他们解释 PHP 5.2 不能跑，那个编译错误是因为 PHP 5.2 根本不支持 PHP 5.3 的语法，
 * 而这些小白用户的反应基本上就是“不知道你在说什么，我就是想看到网站跑起来，你帮我远程调试一下？”
 *
 * 这个程序的目的很简单，如果你是 PHP 5.2 ，不要出现编译错误（因为小白用户根本看不懂这个错误），我们需要的是直接打印错误消息，
 * 告诉小白用户，“都写了必须 > PHP 5.3.4，你丫就别用 5.2 去折腾了行不行？”
 *
 */

define('XIAOBAI_INDEX_PATH', dirname(__FILE__));

// 如果已经安装过了，这里直接启动程序
if (
    // 普通本地环境
    file_exists(XIAOBAI_INDEX_PATH . '/data/install.lock')
    // Sae 环境
    || (function_exists('sae_debug') && file_exists('saestor://data/install.lock'))
) {
    include_once('bootstrap.php');
    die();
}

// 如果还没有安装过，到安装程序去，提示小白用户，
// "请你丫的别用 PHP 5.2 行不行，我们的说明都已经说得很清楚了必须 PHP > 5.3.4，你丫就不能认真的仔细看看？"

header('Location:' . preg_replace('/\/[^\/]+$/', '', $_SERVER['SCRIPT_NAME']) . '/install/');

