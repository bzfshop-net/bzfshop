<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- 让 IE 使用最新模式 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <title>用户登录</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="棒主妇商城管理后台">
    <meta name="author" content="棒主妇开源">

    <!-- 指定360浏览器使用极速模式 -->
    <meta name="renderer" content="webkit"/>
    <!-- /指定360浏览器使用极速模式 -->

    <!-- 引入 CSS 文件 -->
    {{include file="layout_block_link_css.tpl"}}
    <!-- /引入 CSS 文件 -->

</head>

<body>

{{if $DEBUG > 0}}
    <!-- 调试提醒 -->
    <div id="system_top_navbar" class="navbar navbar-inverse navbar-static-top">
        <div class="navbar-inner">
            <div class="container" style="text-align:center;">
                <h5>注意：现在是 DEBUG ({{$DEBUG}}) 模式</h5>
            </div>
        </div>
    </div>
    <!-- /调试提醒 -->
{{/if}}

<!-- main_body -->
<div id="main_body" class="container">

    <!-- ====================================== 这里是页面的主体内容 ============================================ -->

    <form class="form-signin" method="post">
        <h2 class="form-signin-heading">棒主妇-管理后台</h2>

        <div class="control-group">
            <label class="control-label">用户名*</label>

            <div class="controls">
                <input placeholder="这里输入用户名" class="input-block-level" type="text" name="user_name"
                       data-validation-required="data-validation-required"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">密&nbsp;&nbsp;&nbsp;码*</label>

            <div class="controls">
                <input placeholder="这里输入密码" class="input-block-level" type="password" name="password" minlength="6"
                       data-validation-required="data-validation-required"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">验证码*</label>

            <div class="controls">
                <input id="captcha_input" class="span1" type="text" name="captcha"
                       data-validation-required="data-validation-required"/>
                <span id="captcha_image">点击输入获得验证码</span>

                <p>
                    &nbsp;
                </p>
            </div>
        </div>

        <button type="submit" class="btn btn-large btn-primary" type="submit">点击登陆</button>
    </form>

    <!-- ====================================== /这里是页面的主体内容 ============================================ -->

</div>
<!-- /main_body -->

<!-- 360浏览器提示 -->
<!--[if lte IE 9]>

<div class="container" style="text-align: center;">
    <h4>如果你使用的是 360浏览器，请切换到“极速模式”达到最佳浏览效果</h4>
    <img src="{{bzf_get_asset_url asset='img/360browser_help.jpg'}}"/>
</div>
<![endif]-->
<!-- /360浏览器提示 -->

<!-- 让 main_body 和下面的 footer 中间隔离出一段距离 -->
<div id="main_body_tail" class="container">
    <!-- 调用 Cron 执行，用于驱动系统的 Cron 去执行一些周期性的任务 -->
    <img style="width:1px;height:1px;" src="{{bzf_make_url controller='/Cron/Run'}}"/>
</div>
<!-- /让 main_body 和下面的 footer 中间隔离出一段距离 -->

<!-- 引入 JS 文件 -->
{{include file="layout_block_link_js.tpl"}}
<!-- /引入 JS 文件 -->

<!-- IE 7 以下浏览器弹出警告框 -->

<!--[if lte IE 7]>
<script type="text/javascript">
    window.browser_is_lte_ie7_fwtewgjgowjgw = true;
</script>
<![endif]-->

<script type="text/javascript">
    /**
     * 这里的代码等 document.ready 才执行
     */
    jQuery((function (window, $) {
        /**
         * user_login.tpl   user_register.tpl
         *
         * 验证码图片显示，当输入框第一次获得焦点的时候取得验证码
         * */
        $("#captcha_input").one('focus', function () {
            bZF.loadCaptchaImage("#captcha_image");
        });
    })(window, jQuery));
</script>

<script type="text/javascript">

    (function () {
        if (!window.browser_is_lte_ie7_fwtewgjgowjgw) {
            return;
        }
        jQuery.pnotify({
            title: '警告',
            text: '您的浏览器太老了，访问本网站可能会有显示错乱问题，建议换用最新的 Firefox、Chrome、360浏览器"急速模式"、IE8',
            type: 'error',
            hide: false
        }).show();
    })();
</script>


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