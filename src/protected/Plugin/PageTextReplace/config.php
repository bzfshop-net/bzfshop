<?php

/**
 * @author QiangYu
 *
 * 这里我们返回插件缺省配置
 *
 */
return array(

    // 插件基本信息
    'version'      => '1.0.0',
    'display_name' => '页面文本替换',
    'description'  => <<<DESC
<p>开发商：棒主妇开源</p>
<p>主&nbsp;&nbsp;页：<a href="http://www.bzfshop.net" target="_blank">www.bzfshop.net</a></p>
<p>适用系统：groupon, shop, mobile, supplier, aimeidaren</p>
<p>实现最后输出页面的文本替换，比如把 "http://img.bangzhufu.com" 替换成 "http://cdn.bzfshop.net"</p>
DESC
,
    'pattern'      => 'http://img.bangzhufu.com',
    'replace'      => 'http://img.bzfshop.com',
);

