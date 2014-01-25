<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- 让 IE 使用最新模式 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <title>棒主妇管理后台</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="棒主妇商城管理后台">
    <meta name="author" content="棒主妇开源">

    <!-- 指定360浏览器使用极速模式 -->
    <meta name="renderer" content="webkit"/>
    <!-- /指定360浏览器使用极速模式 -->

    <!-- 引入 CSS 文件 -->
    {{include file="layout_block_link_css.tpl"}}
    <!-- /引入 CSS 文件 -->

    <!-- 这里是页面专用的 css 代码 -->
    {{block name=page_css_block}}{{/block}}
    <!-- 这里是页面专用的 css 代码 -->

</head>

<body>
<!-- 用 JS 设置页面的导航菜单 -->
<script type="text/javascript">
    window.bz_set_nav_status = []; // 用于设置导航栏状态的数组，里面是很多设置 function()
    window.bz_set_breadcrumb_status = []; // 用于设置 breadcrumb 的数组，里面是很多设置 {index:1, text:'商品编辑', link:'http://...'}
</script>

{{if $DEBUG > 0}}
    <!-- 调试提醒 -->
    <div class="navbar navbar-inverse navbar-static-top">
        <div class="navbar-inner">
            <div class="container" style="text-align:center;">
                <h5>注意：现在是 DEBUG ({{$DEBUG}}) 模式</h5>
            </div>
        </div>
    </div>
    <!-- /调试提醒 -->
{{/if}}

<!-- 顶部导航菜单 -->
<div id="system_top_navbar" class="navbar navbar-static-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" target="_blank" href="http://www.bzfshop.net">棒主妇开源</a>
            <ul class="nav">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">[{{$authAdminUser['user_name'] nocache}}]
                        <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="{{bzf_make_url controller='/My/Profile'}}">我的资料</a></li>
                        <li><a href="{{bzf_make_url controller='/User/Logout'}}">退出登陆</a></li>
                    </ul>
                </li>
                <li><a href="{{bzf_make_url controller='/Goods/Index'}}">商品管理</a></li>
                <li><a href="{{bzf_make_url controller='/Order/Index'}}">订单管理</a></li>
                <li><a href="{{bzf_make_url controller='/Article/Index'}}">文章管理</a></li>
                <li><a href="{{bzf_make_url controller='/Account/Index'}}">账号管理</a></li>
                <li><a href="{{bzf_make_url controller='/Misc/Index'}}">杂项管理</a></li>
                <li><a href="{{bzf_make_url controller='/Stat/Index'}}">数据统计</a></li>
                <li><a href="{{bzf_make_url controller='/Plugin/Index'}}">插件主题</a></li>
                <li><a href="{{bzf_make_url controller='/Community/Announce'}}">开源社区</a></li>
            </ul>
        </div>
    </div>
</div>
<!-- /顶部导航菜单 -->

<!-- main_body -->
<div id="main_body" class="container" style="margin-top: 10px;">

    <!-- breadcrumb 导航-->
    <div id="main_body_breadcrumb" class="row">
    </div>
    <!-- /breadcrumb 导航-->

    <!-- ====================================== 这里是页面的主体内容 ============================================ -->

    {{block name=main_body}}{{/block}}

    <!-- ====================================== /这里是页面的主体内容 ============================================ -->

</div>
<!-- /main_body -->

<!-- 让 main_body 和下面的 footer 中间隔离出一段距离 -->
<div id="main_body_tail" class="container">
    <!-- 调用 Cron 执行，用于驱动系统的 Cron 去执行一些周期性的任务 -->
    <img style="width:1px;height:1px;" src="{{bzf_make_url controller='/Cron/Run' static=false}}"/>
</div>
<!-- /让 main_body 和下面的 footer 中间隔离出一段距离 -->

<!-- 引入 JS 文件 -->
{{include file="layout_block_link_js.tpl"}}
<!-- /引入 JS 文件 -->

<!-- 这里是页面专用的 JS 代码 -->
{{block name=page_js_block}}{{/block}}
<!-- 这里是页面专用的 JS 代码 -->

{{if 0 == $DEBUG}}
    <!-- 尾部 footer -->
    <div class="navbar navbar-fixed-bottom">
        <div class="navbar-inner">
            <div class="container">
                <div class="row" style="text-align:center;">
                    <span>版权所有：bzfshop 2010-{{$smarty.now|date_format:"%Y"}}</span>
                </div>
            </div>
        </div>
    </div>
    <!-- /尾部 footer -->
{{/if}}

</body>
</html>