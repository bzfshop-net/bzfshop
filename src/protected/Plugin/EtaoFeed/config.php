<?php

/**
 * @author QiangYu
 *
 * 这里我们返回插件缺省配置
 *
 */
return array(

    // 插件基本信息
    'version'                        => '1.0.1',
    'display_name'                   => '一淘Feed',
    'description'                    => <<<DESC
<p>开发商：棒主妇开源</p>
<p>主&nbsp;&nbsp;页：<a href="http://www.bzfshop.net" target="_blank">www.bzfshop.net</a></p>
<p>适用系统：shop</p>
<p>实现 一淘Feed 功能，用于给 一淘 提供商品Feed，插件扩展了 4 个访问地址:</p>
<ul>
    <li>商品分类: /Thirdpart/EtaoFeed/Category</li>
    <li>全量索引: /Thirdpart/EtaoFeed/FullIndex</li>
    <li>增量索引: /Thirdpart/EtaoFeed/IncIndex</li>
    <li>商品列表: /Thirdpart/EtaoFeed/Item/0.xml</li>
</ul>
DESC
,
    'etaofeed_seller_id'             => '棒主妇开源',
    'etaofeed_query_timestamp'       => '0',
    'etaofeed_goods_url_extra_param' => '?utm_source=etao',
);

