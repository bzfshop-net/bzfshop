<?php

/*
 * @author QiangYu
 *
 * 虽然我们在网站上写明了 bzfshop 必须要求 PHP > 5.3.4 才能安装，但是还是有 50% 的人根本不看要求，直接就拿着 PHP 5.2 硬装，
 * 结果安装程序都进不去，然后到处提问为什么安装不了，一幅很委屈的样子
 *
 * 我们对这些不看要求自己瞎折腾出来的问题已经很厌烦了，所以这里安装程序首先就检测 PHP 版本，那些拿着 PHP 5.2 来安装的人直接在
 * 这里就会挂掉，然后给出错提示，你们不用再去提问了，看这个出错提示就知道你们错在哪里了
 *
 * 注意，中国的空间商都比较烂，所以大部分虚拟主机都是 PHP 5.2 的，基本上安装不了，安装前请检查你的安装环境
 *
 * bzfshop 也不建议你安装在虚拟主机上，好歹弄个 VPS 可以自己配置环境吧
 *
 */

// 如果已经安装过了，不能重复安装
if (
    // 普通本地环境
    file_exists(dirname(__FILE__) . '/../data/install.lock')
    // Sae 环境
    || (function_exists('sae_debug') && file_exists('saestor://data/install.lock'))
) {
    die('你已经安装过了，如果需要再次安装请手动删除 data 目录下的 install.lock 文件');
}

define('BZF_PHP_VERSION_REQUIRE', '5.3.4');

// 检查 PHP 版本，用 PHP 5.2 的人就不要瞎折腾了

$requirePHPVersion = BZF_PHP_VERSION_REQUIRE;
$currentPHPVersion = phpversion();
$isCurrentPHPOk    = version_compare($currentPHPVersion, BZF_PHP_VERSION_REQUIRE, '>=');

if ($isCurrentPHPOk) {
    include_once('bootstrap.php');
    die();
}

// 这里开始错误处理

$phpErrorMsg = <<<MSG
<html>
<head>
    <meta charset="utf-8">
    <link href="./Asset/bootstrap-custom/css/bootstrap-1206.css" type="text/css" rel="stylesheet">
</head>
<body>
<div class="navbar navbar-inverse navbar-static-top">
        <div class="navbar-inner">
            <div style="text-align:center;" class="container">
                <h5>错误：bzfshop 要求最低 PHP 版本为 {$requirePHPVersion} ，你当前的 PHP 版本为 {$currentPHPVersion}，不符合要求无法安装</h5>
            </div>
        </div>
</div>
</body>
</html>
<br/><br/>
MSG;

echo $phpErrorMsg;

// 显示 PHP 信息
if (function_exists('phpinfo')) {
    call_user_func('phpinfo');
}
