<?php

/**
 * @author QiangYu
 *
 * 这里我们返回插件缺省配置
 *
 */
return array(

    // 插件基本信息
    'version'                           => '1.0.0',
    'display_name'                      => '360一站通登陆',
    'description'                       => <<<DESC
<p>开发商：棒主妇开源</p>
<p>主&nbsp;&nbsp;页：<a href="http://www.bzfshop.net" target="_blank">www.bzfshop.net</a></p>
<p>适用系统：shop, aimeidaren</p>
<p>实现 360 一站通登陆功能，用户可以使用 360 账号直接在你的网站登陆无需额外注册。插件扩展了 2 个访问地址:</p>
<ul>
    <li>用户登陆地址: /Thirdpart/Dev360Auth/Login</li>
    <li>360 回调地址: /Thirdpart/Dev360Auth/Callback</li>
</ul>
DESC
,
    // shop
    'shop_dev360auth_app_id'            => '1000',
    'shop_dev360auth_app_key'           => 'app_key',
    'shop_dev360auth_app_secrect'       => 'app_secrect',
    // aimeidaren
    'aimeidaren_dev360auth_app_id'      => '1000',
    'aimeidaren_dev360auth_app_key'     => 'app_key',
    'aimeidaren_dev360auth_app_secrect' => 'app_secrect',
);

