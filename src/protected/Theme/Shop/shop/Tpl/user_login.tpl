{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bzf_set_nav_status.push(function ($) {
            $("#bzf_header_nav_menu li:has(a[href='{{bzf_make_url controller='/'}}'])").addClass("active");
        });
    </script>
    <!-- 主体内容 row -->
    <div class="row" style="background-color: white;padding-bottom: 10px;">

        <!-- 左侧 用户登录 -->
        <div class="span6" style="border-right: solid 1px silver">
            <div class="row bzf_user_login_panel">
                <div class="bzf_header_panel">
                    <span>用户登陆</span>
                </div>

                <!-- 登陆提交表单  -->
                <form class="form-horizontal" method="post" action="{{bzf_make_url controller='/User/Login'}}">

                    <!-- 登陆信息 -->
                    <div class="row">

                        <div class="control-group">
                            <label class="control-label">用户名/邮箱*</label>

                            <div class="controls">
                                <input class="span3" type="text" name="user_name" value=""
                                       data-validation-required="data-validation-required"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">密&nbsp;&nbsp;&nbsp;码*</label>

                            <div class="controls">
                                <input class="span3" type="password" name="password" minlength="6"
                                       data-validation-required="data-validation-required"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">验证码</label>

                            <div class="controls">
                                <input id="captcha_input_login" class="span1" type="text" name="captcha"
                                       data-validation-required="data-validation-required"/>
                                <span id="captcha_image_login">点击输入获得验证码</span>

                                <p>
                                    &nbsp;
                                </p>
                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls">
                                <button type="submit" class="bzf_button_big">
                                    点击登陆
                                </button>
                            </div>
                        </div>

                    </div>
                    <!-- /登陆信息 -->

                </form>
                <!-- /登陆提交表单 -->

                <!-- 联合登陆 -->
                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <td colspan="2" class="well well-small" style="color:blue;font-weight: bold;">使用第三方账号登陆</td>
                    </tr>
                    <tr>
                        <td width="50%">
                            <!-- 查询QQ登陆插件的版本 -->
                            {{bzf_query_plugin_feature_var varName='qqAuthVersion' uniqueId='90B3F08E-971B-4D04-A1B3-BF4FA341BD96' command='pluginGetVersion' }}
                            {{if !empty($qqAuthVersion)}}
                                <a href="{{bzf_make_url controller='/Thirdpart/QQAuth/Login'}}">
                                    <img src="{{bzf_get_asset_url asset='img/login_qq.png'}}"/>
                                </a>
                            {{/if}}
                        </td>
                        <td width="50%">
                            <!-- 查询360联合登陆插件的版本 -->
                            {{bzf_query_plugin_feature_var varName='dev360AuthVersion' uniqueId='BCA22CBB-8107-4F50-8C07-63EFCAB494CE' command='pluginGetVersion' }}
                            {{if !empty($dev360AuthVersion)}}
                                <a href="{{bzf_make_url controller='/Thirdpart/Dev360Auth/Login'}}">
                                    <img src="{{bzf_get_asset_url asset='img/login_360auth.gif'}}"/>
                                </a>
                            {{/if}}
                        </td>
                    </tr>
                    </tbody>
                </table>
                <!-- /联合登陆 -->

            </div>
        </div>
        <!-- /左侧 用户登录 -->

        <!-- 右侧 用户注册 -->
        <div class="span6">
            <div class="row bzf_user_login_panel">
                <div class="bzf_header_panel">
                    <span>用户注册</span>
                </div>


                <!-- 注册提交表单  -->
                <form class="form-horizontal" method="post" action="{{bzf_make_url controller='/User/Register'}}">

                    <!-- 注册信息 -->
                    <div class="row">

                        <div class="control-group">
                            <label class="control-label">用户名*</label>

                            <div class="controls">
                                <input class="span3" type="text" name="user_name" value=""
                                       minlength="2" data-validation-required="data-validation-required"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">密&nbsp;&nbsp;&nbsp;码*</label>

                            <div class="controls">
                                <input class="span3" type="password" name="password" minlength="6"
                                       data-validation-required="data-validation-required"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">确认密码*</label>

                            <div class="controls">
                                <input class="span3" type="password" name="password_again"
                                       data-validation-passwordagain="data-validation-passwordagain"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">电子邮箱</label>

                            <div class="controls">
                                <input class="span3" type="text" name="email" value=""
                                       data-validation-email="data-validation-email"/>

                                <p>
                                    请输入您的真实邮箱，以便接收购买信息
                                </p>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">手机号码</label>

                            <div class="controls">
                                <input class="span3" type="text" name="mobile_phone" value=""
                                       pattern="1([0-9]{10})" data-validation-pattern-message="号码格式不正确"/>

                                <p>
                                    请输入您的手机号码，以便接快递信息（我们承诺不会泄露您的资料，也不会给您发垃圾短信）
                                </p>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">验证码</label>

                            <div class="controls">
                                <input id="captcha_input_register" class="span1" type="text" name="captcha"
                                       data-validation-required="data-validation-required"/>
                                <span id="captcha_image_register">点击输入获得验证码</span>

                                <p>&nbsp;</p>
                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls">
                                <button type="submit" class="bzf_button_big">
                                    点击注册
                                </button>
                            </div>
                        </div>

                    </div>
                    <!-- /注册信息 -->

                </form>
                <!-- /注册提交表单 -->

            </div>
        </div>
        <!-- /右侧 用户注册 -->

    </div>
    <!-- /主体内容 row -->

{{/block}}

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {
            /**
             * user_login.tpl 页面，用户登陆注册
             *
             * 验证码图片显示，当输入框第一次获得焦点的时候取得验证码
             * */
            $("#captcha_input_login").one('focus', function () {
                bZF.loadCaptchaImage("#captcha_image_login");
            });

            $("#captcha_input_register").one('focus', function () {
                bZF.loadCaptchaImage("#captcha_image_register");
            });

        })(window, jQuery));
    </script>
{{/block}}