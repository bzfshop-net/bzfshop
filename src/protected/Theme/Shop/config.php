<?php

/**
 * @author QiangYu
 *
 * 这里我们返回插件缺省配置
 *
 */
return array(

    // 插件基本信息
    'version'                               => '1.0.5',
    'display_name'                          => '棒主妇商城主题',
    'description'                           => <<<DESC
<p>开发商：棒主妇开源</p>
<p>主&nbsp;&nbsp;页：<a href="http://www.bzfshop.net" target="_blank">www.bzfshop.net</a></p>
<p>这套主题提供了一个 B2C 商城所需要的全部功能，功能类似天猫和京东</p>
DESC
,
    // 商城主题配置信息
    'site_name'                             => '棒主妇开源',
    'seo_title'                             => '棒主妇开源 - 做轻松主妇 享家居生活,棒主妇商城,网上购物',
    'seo_keywords'                          => '棒主妇,棒主妇商城,特价商品,全国,网上购物,团购,打折,精品消费,男装,女装,童装,T恤,衬衣,西装,皮鞋,裙子,上衣,休闲裤,牛仔裤,牛仔,卡其裤,鞋,家居,配饰,棉线衫,开衫,针织衫,外套,被子,棉被,蚕丝被,羽绒被,断码,打折,浴巾,面巾,毛巾,玩具,儿童玩具',
    'seo_description'                       => '棒主妇开源,新一代的互联网电子商城,网上购物首选商城,提供男装、女装、童装、鞋、箱包、家居、家纺、日用、化妆品等多种商品网购,特价商品、优惠券、折扣券、闪电发货，买服装家纺网站首选。',
    'merchant_name'                         => '棒主妇开源',
    'merchant_address'                      => '北京市',
    'kefu_telephone'                        => '010-10086',
    'kefu_qq'                               => '800018599',
    'business_qq'                           => '800018599',
    'icp'                                   => '京ICP备100号',
    'goods_after_service'                   => '这里是售后服务说明',
    'statistics_code'                       => <<<CODE
<!-- 网站统计代码 -->

<script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-10632150-6']);
    _gaq.push(['_setDomainName', '.bzfshop.net']);
    _gaq.push(['_trackPageview']);
    (function () {
        var ga = document.createElement('script');
        ga.type = 'text/javascript';
        ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ga, s);
    })();
</script>

<!-- /网站统计代码 -->
CODE
,
    // 页面缓存时间设置
    'smarty_cache_time_ajax_category'       => '1800',
    'smarty_cache_time_shop_index'          => '1800',
    'smarty_cache_time_goods_view'          => '1800',
    'smarty_cache_time_goods_search'        => '1800',
    'smarty_cache_time_article_view'        => '86400',
    // 广告设置
    'shop_index_adv_recommand_goods_idlist' => null,
    'shop_index_adv_slider'                 => null,
    'shop_index_advblock_json_data'         => null,
    'shop_index_notice'                     => '这里是网站公告',
    'goods_view_detail_notice'              => null,
    'goods_search_adv_slider'               => null,
    'goods_view_adv_slider'                 => null,
    'goods_view_adv_slider'                 => null,
    'foot_nav_json_data'                    => json_encode(
        array(
             0 => array(
                 'title' => '新手指南',
                 'item'  => array(
                     0 => array('title' => '账户注册', 'url' => '#'),
                     1 => array('title' => '购物流程', 'url' => '#'),
                     3 => array('title' => '付款方式', 'url' => '#'),
                     4 => array('title' => '常见问题', 'url' => '#'),
                 )
             ),
             1 => array(
                 'title' => '配送方式',
                 'item'  => array(
                     0 => array('title' => '配送说明', 'url' => '#'),
                     1 => array('title' => '订单拆分', 'url' => '#'),
                 )
             ),
             2 => array(
                 'title' => '支付方式',
                 'item'  => array(
                     0 => array('title' => '在线支付', 'url' => '#'),
                     1 => array('title' => '其它支付方式', 'url' => '#'),
                 )
             ),
             3 => array(
                 'title' => '售后服务',
                 'item'  => array(
                     0 => array('title' => '退换货政策', 'url' => '#'),
                     1 => array('title' => '退换货办理流程', 'url' => '#'),
                 )
             ),
             4 => array(
                 'title' => '购物保障',
                 'item'  => array(
                     0 => array('title' => '免责声明', 'url' => '#'),
                     1 => array('title' => '隐私保护', 'url' => '#'),
                     2 => array('title' => '注册协议', 'url' => '#'),
                     3 => array('title' => '正品保障', 'url' => '#'),
                 )
             ),
        )
    ),
    'head_nav_json_data'                    => json_encode(array(array('title' => '导航1', 'url' => '#'))),
    'friend_link_json_data'                 => json_encode(array(array('title' => '友链1', 'url' => '#'))),
);

