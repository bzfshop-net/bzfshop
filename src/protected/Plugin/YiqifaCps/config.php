<?php

/**
 * @author QiangYu
 *
 * 这里我们返回插件缺省配置
 *
 */
return array(

    // 插件基本信息
    'version'               => '1.0.0',
    'display_name'          => '亿起发CPS',
    'description'           => <<<DESC
<p>开发商：棒主妇开源</p>
<p>主&nbsp;&nbsp;页：<a href="http://www.bzfshop.net" target="_blank">www.bzfshop.net</a></p>
<p>适用系统：groupon, shop, mobile</p>
<p>实现 亿起发CPS 功能，包括实现了QQ彩贝的接入，插件扩展了 3 个访问地址:</p>
<ul>
    <li>用户跳转地址: /Thirdpart/YiqifaCps/Redirect</li>
    <li>订单查询地址: /Thirdpart/YiqifaCps/QueryOrder</li>
    <li>彩贝联合登陆: /Thirdpart/YiqifaCps/CaibeiLogin</li>
</ul>
DESC
,
    'yiqifacps_rate_web'    => '0.1',
    'yiqifacps_rate_mobile' => '0.1',
    'yiqifacps_duration'    => '2592000',
    'qqcaibei_key1'         => 'qqcaibei_key1',
    'qqcaibei_key2'         => 'qqcaibei_key2',
);

