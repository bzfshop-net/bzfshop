<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>

    <!-- 让 IE 使用最新模式 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <title>{{$seo_title|default}}</title>
    <meta name="description" content="{{$seo_description|default}}"/>
    <meta name="keywords" content="{{$seo_keywords|default}}"/>
    <meta name="author" content="棒主妇开源"/>

    <!-- 指定360浏览器使用极速模式 -->
    <meta name="renderer" content="webkit"/>
    <!-- /指定360浏览器使用极速模式 -->

    {{if isset($currentPageMobileUrl) }}
        <!-- baidu 的 mobile 适配 -->
        <meta name="mobile-agent" content="format=html5;url={{$currentPageMobileUrl}}"/>
        <meta name="mobile-agent" content="format=xhtml;url={{$currentPageMobileUrl}}"/>
        <!-- /baidu 的 mobile 适配 -->
    {{/if}}

    <link rel="stylesheet" type="text/css"
          href="{{bzf_get_asset_url asset='bootstrap-custom/css/bootstrap-1002.css'}}"/>

    <!--[if lte IE 6]>
    <link rel="stylesheet" type="text/css"
          href="{{bzf_get_asset_url asset='bootstrap-custom/css/bootstrap-1002.ie6.css'}}"/>
    <![endif]-->

    <!--[if lte IE 7]>
    <link rel="stylesheet" type="text/css" href="{{bzf_get_asset_url asset='bootstrap-custom/css/ie.css'}}"/>
    <![endif]-->

    <!-- 合并所有的 Css 文件, 使用 merge=false 参数关闭合并，这样可以对单个文件做调试 -->
    {{bzf_dump_merged_asset_css_url
    asset='bootstrap-custom/css/bootstrap-1002.fix.css,
    bootstrap-custom/plugin/cloud-zoom/cloud-zoom.css,
    bootstrap-custom/plugin/smartspin/smartspinner.css,
    bootstrap-custom/plugin/pnotify/jquery.pnotify.default.css,
    bootstrap-custom/plugin/quake-slider/css/quake.slider.css,
    bootstrap-custom/plugin/popover-extra/popover-extra-placements.css,
    bootstrap-custom/plugin/treetable/css/jquery.treetable.css,
    css/dropdown_category.css,
    css/jcarousel.css,
    css/quake-slider/quake.skin.css,
    css/jquery.treetable.theme.bzfshop.css,
    css/shop.css,
    css/advblock.css
    '}}
    <!-- /合并所有的 Css 文件, 使用 merge=false 参数关闭合并，这样可以对单个文件做调试 -->

    <!-- 这里是页面专用的 css 代码 -->
    {{block name=page_css_block}}{{/block}}
    <!-- 这里是页面专用的 css 代码 -->

</head>
<body>

<!-- 用 JS 设置页面的导航菜单 -->
<script type="text/javascript">
    window.bzf_set_nav_status = []; // 用于设置导航栏状态的数组，里面是很多设置 function()
</script>

<!-- QQ彩贝用户登陆显示 -->
<div id="qqcaibei_header_panel" class="navbar navbar-inverse navbar-static-top">
    <div class="navbar-inner">
        <div class="container">
            <div class="row">
                <span id="qqcaibei_header_panel_headshow">这里是用户信息显示</span>
                <span id="qqcaibei_header_panel_showmsg">这里是用户消息</span>
                <span><a id="qqcaibei_header_panel_jifenurl" target="_blank" href="#">查看彩贝积分</a></span>
            </div>
        </div>
    </div>
</div>
<!-- /QQ彩贝用户登陆显示 -->

<!-- 头部，用户登录、注册栏 -->
<div class="navbar navbar-static-top">
    <div class="navbar-inner bzf_header_login_register">
        <div class="container">

            <!-- 欢迎信息，用户登录之后会被替换掉 -->
            <span class="bzf_welcome">
            您好，欢迎来到{{bzf_get_option_value optionKey="site_name"}}！&nbsp;&nbsp;
            <a href="{{bzf_make_url controller='/User/Login'}}">登陆</a>&nbsp;|
            <a href="{{bzf_make_url controller='/User/Register'}}">注册</a>&nbsp;
            </span>
            <!-- /欢迎信息，用户登录之后会被替换掉 -->

            |&nbsp;<a href="{{bzf_make_url controller='/My/Order'}}">个人中心</a>&nbsp;|&nbsp;客服QQ&nbsp;
            <span>
                <a style="color:red;font-weight: bold;" target="_blank"
                   href="http://wpa.qq.com/msgrd?v=3&uin={{bzf_get_option_value optionKey="kefu_qq"}}&site=qq&menu=yes">
                    {{bzf_get_option_value optionKey="kefu_qq"}}
                </a>
            </span>&nbsp;|&nbsp;客服电话&nbsp;
            <span style="color:red;font-weight: bold;">{{bzf_get_option_value optionKey="kefu_telephone"}}</span>
            &nbsp;&nbsp;
        </div>
    </div>
</div>
<!-- /头部，用户登录、注册栏 -->

<!-- 头部，Logo、搜索栏-->
<div class="navbar navbar-static-top">
    <div class="navbar-inner bzf_header_logo_search">
        <div class="container">
            <!-- 搜索 form -->
            <form method="GET" action="{{bzf_make_url controller='/Goods/Search' static=false }}">
                <div class="row">

                    <span style="width:225px; height: auto">
                        <a href="{{bzf_make_url controller='/'}}">
                            <img src="{{bzf_get_asset_url asset='img/header_logo.png'}}"/>
                        </a>
                    </span>

                    <span class="bzf_header_search_block">
                        <img src="{{bzf_get_asset_url asset='img/header_search_icon.png'}}"/>
                        <input type="text" name="keywords"/>
                        <button type="submit">搜&nbsp;索</button>
                    </span>

                    <span class="bzf_header_cart_block">
                        <a href="{{bzf_make_url controller='/My/Order'}}">
                            我的{{bzf_get_option_value optionKey="site_name"}}
                        </a>
                    </span>

                    <span id="bzf_header_cart_block" class="bzf_header_cart_block">
                        <span class="bzf_icon_cart_red"></span>
                        <a href="{{bzf_make_url controller='/Cart/Show'}}">
                            购物车<span id="cart_goods_count">0</span>件
                        </a>
                    </span>

                </div>
            </form>
            <!-- /搜索 form -->
        </div>
    </div>
</div>
<!-- /头部，Logo、搜索栏-->

<!-- 头部，菜单导航栏-->
<div class="navbar navbar-static-top">
    <div class="navbar-inner bzf_header_nav_menu_dropdown">
        <div class="container">
            <ul id="bzf_header_nav_menu" class="nav">
                <li>
                    <!-- 这个链接不能点击（见 JS 文件）我们在这里加上 href 目的是提供给搜索引擎抓取，起到 SEO 优化的作用 -->
                    <a id="bzf_header_dropdown_menu"
                       href="{{bzf_make_url controller='/Ajax/Category' static=false}}">全部商品分类<span
                                class="bzf_header_dropdown_menu_icon"></span></a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/'}}">首页</a>
                </li>
                <!-- 头部导航设置 -->
                {{bzf_assign_option_value optionKey="head_nav_json_data" decodeJson="true"}}
                {{foreach $head_nav_json_data as $headNavItem}}
                    {{if !empty($headNavItem['title'])}}
                        <li>
                            <a href="{{$headNavItem['url']}}">{{$headNavItem['title']}}</a>
                        </li>
                    {{/if}}
                {{/foreach}}
            </ul>
        </div>
    </div>
</div>
<!-- /头部，菜单导航栏-->


<!-- 网页主体内容 -->
<div id="main_body" class="container">

    <!-- =============================  网页主体内容  =========================================================== -->

    {{block name=main_body}}{{/block}}

    <!-- =============================  /网页主体内容  =========================================================== -->

</div>
<!-- /网页主体内容 main_body -->

<!-- 底部，一堆关于我们的链接-->
<div class="navbar navbar-static-top">
    <div class="navbar-inner bzf_footer_introduction_panel">
        <div class="container">

            <!-- 取得底部导航数据 -->
            {{bzf_assign_option_value optionKey="foot_nav_json_data" decodeJson="true"}}

            {{assign var="navItemListFirst" value=true}}

            {{foreach $foot_nav_json_data as $navItemList}}

            {{if $navItemListFirst}}
            <div class="span2 list_panel" style="border-left: none;">
                {{else}}
                <div class="span2 list_panel">
                    {{/if}}

                    {{assign var="navItemListFirst" value=false}}

                    <div>{{$navItemList['title']}}</div>
                    {{foreach $navItemList['item'] as $navItem}}
                        {{if ctype_digit($navItem['url'])}} <!-- 数字表明这是指向一个 文章ID -->
                            <p><a target="_blank"
                                  href="{{bzf_make_url controller='/Article/View' article_id=$navItem['url']}}">{{$navItem['title']}}</a>
                            </p>
                        {{else}}
                            <p><a target="_blank" href="{{$navItem['url']}}">{{$navItem['title']}}</a></p>
                        {{/if}}
                    {{/foreach}}
                </div>
                {{/foreach}}

                <div class="span2 tel_panel">
                    <p><span class="tel_icon"></span>客服热线</p>

                    <p>{{bzf_get_option_value optionKey="kefu_telephone"}}</p>
                </div>

            </div>
        </div>
    </div>
    <!-- /底部，一堆关于我们的链接-->

    <!-- 商品分类，由 ajax 程序异步加载 -->
    <div class="row navsort">

    </div>
    <!-- /商品分类，由 ajax 程序异步加载 -->

    <!-- 页面右边悬浮框 -->
    <div id="bzf_right_float_panel" class="bzf_right_float_panel">

        <div id="bzf_right_float_kefu_block" class="bzf_right_float_panel_block">
            <span class="icon_kefu"></span>

            <div class="bzf_popover_html" style="display: none;">

                <!-- 客服信息 -->
                <div class="bzf_supplier_pane" style="width:216px;">
                    <div class="header">
                        商家信息
                    </div>
                    <div class="supplier_pane_content">
                        <div class="bzf_show">
                            <div class="supplier_info">
                                <p>商 户 名 ：{{bzf_get_option_value optionKey="merchant_name"}}</p>

                                <p>所 在 地 ：{{bzf_get_option_value optionKey="merchant_address"}}</p>

                                <p>客服电话：{{bzf_get_option_value optionKey="kefu_telephone"}}</p>

                                <p>客服 QQ：{{bzf_get_option_value optionKey="kefu_qq"}}</p>
                            </div>
                            <div class="divider"></div>
                            <div class="supplier_info" style="text-align: center;">
                                <p>周一至周五 10:00-19:00</p>
                                <a target="_blank"
                                   href="http://wpa.qq.com/msgrd?v=3&uin={{bzf_get_option_value optionKey="kefu_qq"}}&site=qq&menu=yes">
                                    <img border="0"
                                         src="http://wpa.qq.com/pa?p=2:{{bzf_get_option_value optionKey="kefu_qq"}}:41"
                                         alt="QQ在线客服"
                                         title="QQ在线客服"/>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /客服信息 -->

            </div>

        </div>

        <div id="bzf_right_float_cart_block" class="bzf_right_float_panel_block">
            <span class="icon_cart"></span>
        </div>

        <div class="bzf_right_float_panel_block" onclick="jQuery(document).scrollTo('0px',800);">
            <span class="icon_back_to_top"></span>

            <div class="bzf_hide"
                 style="width:60px;font-weight: bold;text-align: center;line-height: 40px;">
                返回顶部
            </div>
        </div>
    </div>
    <!-- /页面右边悬浮框 -->

    <!-- 定义网站的起始路径，用于 JavaScript 的 Ajax 操作调用 -->
    <script type="text/javascript">
        var WEB_ROOT_HOST = '{{$WEB_ROOT_HOST}}';
        var WEB_ROOT_BASE = '{{$WEB_ROOT_BASE}}';
        var WEB_ROOT_BASE_RES = '{{$WEB_ROOT_BASE_RES}}';
        var BLANK_IMAGE_URL = '{{bzf_get_asset_url asset='img/blank.gif'}}';
    </script>

    <!-- 合并所有的 JS 文件, 使用 merge=false 参数关闭合并，这样可以对单个文件做调试 -->
    {{bzf_dump_merged_asset_js_url
    asset='bootstrap-custom/js/json2.js,
       bootstrap-custom/js/jquery-1.8.3.min.js,
       bootstrap-custom/js/jquery.cookie.js,
       bootstrap-custom/js/jstorage.min.js,
       bootstrap-custom/js/bootstrap.min.js,
       bootstrap-custom/js/bootstrap.ie.js,
       bootstrap-custom/js/validator.js,
       bootstrap-custom/plugin/cloud-zoom/cloud-zoom.1.0.3.js,
       bootstrap-custom/plugin/jcarousel/jquery.jcarousel.min.js,
       bootstrap-custom/plugin/smartspin/smartspinner.js,
       bootstrap-custom/plugin/stickyPanel/jquery.stickyPanel.min.js,
       bootstrap-custom/plugin/docktoright/docktoright.js,
       bootstrap-custom/plugin/scrollTo/jquery.scrollTo.min.js,
       bootstrap-custom/plugin/pnotify/jquery.pnotify.min.js,
       bootstrap-custom/plugin/quake-slider/js/quake.slider.js,
       bootstrap-custom/plugin/popover-extra/popover-extra-placements.js,
       bootstrap-custom/plugin/jrumble/jquery.jrumble.1.3.min.js,
       bootstrap-custom/plugin/purl/purl.js,
       bootstrap-custom/plugin/raty/jquery.raty.min.js,
       bootstrap-custom/plugin/treetable/js/jquery.treetable.js,
       bootstrap-custom/js/jquery.lazyload.js,
       js/dropdown_category.js,
       js/shop.js
'}}
    <!-- /合并所有的 JS 文件, 使用 merge=false 参数关闭合并，这样可以对单个文件做调试 -->

    <!-- 这里是页面专用的 JS 代码 -->
    {{block name=page_js_block}}{{/block}}
    <!-- 这里是页面专用的 JS 代码 -->

    <!-- 底部，各种资格认证 -->
    <div class="container">
        <div class="row bzf_footer_credit_panel">
            <span><i class="bzf_icon_zhengping"></i>正品保证</span>
            <span><i class="bzf_icon_fahuo" style="width:51px;"></i>闪电发货</span>
            <span><i class="bzf_icon_qitian"></i>七天退换</span>
            <span><i class="bzf_icon_money" style="width:42px;height:42px;"></i>退款保障</span>
        </div>
        <div class="row bzf_footer_tail">

            <!-- 取得友链的数据 -->
            {{bzf_assign_option_value optionKey="friend_link_json_data" decodeJson="true"}}
            <p><a target="_blank" href="http://www.bangzhufu.com">棒主妇商城</a> | <a target="_blank"
                                                                                 href="http://www.bzfshop.net">棒主妇开源</a>{{foreach $friend_link_json_data as $friendLink}}{{if !empty($friendLink['title'])}} |
                <a target="_blank" href="{{$friendLink['url']}}">{{$friendLink['title']}}</a>{{/if}}{{/foreach}}</p>

            <p>棒主妇开源 版权所有 ©Copyright 2010-{{$smarty.now|date_format:"%Y"}} All Rights Reserved</p>

            <p>ICP备案：<a target="_blank" href="http://www.miit.gov.cn/">{{bzf_get_option_value optionKey="icp"}}</a></p>

            <p><!-- 驱动 Cron 任务-->
                <img style="width:1px;height:1px;" src="{{bzf_make_url controller='/Cron/Run' static=false}}"/>
                {{bzf_get_option_value optionKey="statistics_code"}} <!-- 统计代码 -->
            </p>
        </div>
    </div>
    <!-- /底部，各种资格认证 -->

</body>
</html>