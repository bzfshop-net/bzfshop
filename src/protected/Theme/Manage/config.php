<?php

/**
 * @author QiangYu
 *
 * 这里我们返回插件缺省配置
 *
 */
return array(

    // 插件基本信息
    'version'               => '1.0.1',
    'display_name'          => 'Manage管理后台',
    'description'           => <<<DESC
<p>开发商：棒主妇开源</p>
<p>主&nbsp;&nbsp;页：<a href="http://www.bzfshop.net" target="_blank">www.bzfshop.net</a></p>
<p>棒主妇 Manage 管理后台，不可卸载、不可删除、不可停用</p>
DESC
,
    /**
     * 其它主题的 url base，比如 商城 http://www.bangzhufu.com ，团购 http://tuan.bangzhufu.com
     * 这里格式如下
     * array(
     *      'shop' => array('name' => '商城', 'desc' => '这里是描述', 'base' => 'http://www.bangzhufu.com'),
     *      'groupon' => array('name' => '团购', 'desc' => '这里是描述', 'base' => 'http://tuan.bangzhufu.com'),
     * )
     */
    'system_url_base_array' => null,
);

