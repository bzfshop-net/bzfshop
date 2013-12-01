<?php

/**
 * @author QiangYu
 *
 * 这里我们返回插件缺省配置
 *
 */
return array(

    // 插件基本信息
    'version'       => '1.0.0',
    'display_name'  => 'QQ登陆',
    'description'   => <<<DESC
<p>开发商：棒主妇开源</p>
<p>主&nbsp;&nbsp;页：<a href="http://www.bzfshop.net" target="_blank">www.bzfshop.net</a></p>
<p>适用系统：groupon, shop</p>
<p>实现 QQ 登陆功能，用户可以使用 QQ 账号直接在你的网站登陆无需额外注册。插件扩展了 2 个访问地址:</p>
<ul>
    <li>用户登陆地址: /Thirdpart/QQAuth/Login</li>
    <li>QQ 回调地址: /Thirdpart/QQAuth/Callback</li>
</ul>
DESC
,
    'qqauth_appid'  => '1000',
    'qqauth_appkey' => 'appkey',
);

