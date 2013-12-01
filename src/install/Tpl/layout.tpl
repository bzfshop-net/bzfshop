<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- 让 IE 使用最新模式 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <title>棒主妇开源-安装</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="棒主妇开源安装程序">
    <meta name="author" content="棒主妇开源">

    <!-- 指定360浏览器使用极速模式 -->
    <meta name="renderer" content="webkit"/>
    <!-- /指定360浏览器使用极速模式 -->

    <link rel="stylesheet" type="text/css" href="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/css/bootstrap-1206.css"/>
    <link rel="stylesheet" type="text/css"
          href="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/plugin/pnotify/jquery.pnotify.default.css"/>

    <!--[if lte IE 6]>
    <link rel="stylesheet" type="text/css" href="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/css/bootstrap-1206.ie6.css"/>
    <![endif]-->
    <!--[if lte IE 7]>
    <link rel="stylesheet" type="text/css" href="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/css/ie.css"/>
    <![endif]-->

    <link rel="stylesheet" type="text/css" href="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/css/bootstrap-1206.fix.css"/>
    <link rel="stylesheet" type="text/css" href="{{$WEB_ROOT_BASE_RES}}css/install.css"/>

</head>

<body>

<!-- 顶部导航菜单 -->
<div id="system_top_navbar" class="navbar navbar-static-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" target="_blank" href="http://www.bzfshop.net">棒主妇开源</a>
            <ul class="nav">
                <li class="active"><a href="{{bzf_make_url controller='/Install/Step1'}}">程序安装</a></li>
                <li><a target="_blank" href="http://www.bzfshop.net/article/246.html">安装指南</a></li>
                <li><a target="_blank" href="http://www.bzfshop.net/article/248.html">技术支持</a></li>
            </ul>
        </div>
    </div>
</div>
<!-- /顶部导航菜单 -->

<!-- main_body -->
<div id="main_body" class="container" style="margin-top: 10px;">

    <!-- ====================================== 这里是页面的主体内容 ============================================ -->

    {{block name=main_body}}{{/block}}

    <!-- ====================================== /这里是页面的主体内容 ============================================ -->

</div>
<!-- /main_body -->

<!-- 让 main_body 和下面的 footer 中间隔离出一段距离 -->
<div id="main_body_tail" class="container">&nbsp;</div>
<!-- /让 main_body 和下面的 footer 中间隔离出一段距离 -->

<!-- 定义网站的起始路径，用于 JavaScript 的 Ajax 操作调用 -->
<script>
    var WEB_ROOT_HOST = '{{$WEB_ROOT_HOST}}';
    var WEB_ROOT_BASE = '{{$WEB_ROOT_BASE}}';
    var WEB_ROOT_BASE_RES = '{{$WEB_ROOT_BASE_RES}}';
</script>

<script type="text/javascript" src="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/js/json2.js"></script>
<script type="text/javascript" src="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/js/bootstrap.min.js"></script>
<script type="text/javascript" src="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/js/bootstrap.ie.js"></script>
<script type="text/javascript" src="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/js/validator.js"></script>
<script type="text/javascript" src="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/js/jquery.cookie.js"></script>
<script type="text/javascript"
        src="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/plugin/pnotify/jquery.pnotify.min.js"></script>
<script type="text/javascript" src="{{$WEB_ROOT_BASE_RES}}js/install.js"></script>

<!--[if lte IE 9]>
<script type="text/javascript" src="{{$WEB_ROOT_BASE_RES}}bootstrap-custom/js/iefix.js"></script>
<![endif]-->

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