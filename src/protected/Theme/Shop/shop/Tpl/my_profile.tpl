{{extends file='my_layout.tpl'}}
{{block name=main_body_my}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bzf_set_nav_status.push(function ($) {
            $("#my_nav_tabbar li:has(a[href='{{bzf_make_url controller='/My/Profile'}}'])").addClass("active");
        });
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        <h4>我的资料</h4>

        <!-- 我的资料表单  -->
        <form class="form-horizontal" method="post">

            <div class="control-group">
                <label class="control-label">用户名</label>

                <div class="controls">
                    <input class="span3" type="text" disabled="disabled" value="{{$user_name|default}}"/>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label">电子邮箱</label>

                <div class="controls">
                    <input class="span3" type="text" name="email" value="{{$email|default}}"
                           data-validation-email="data-validation-email"/>

                    <p>
                        请输入您的真实邮箱，以便接收购买信息
                    </p>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label">手机号码</label>

                <div class="controls">
                    <input class="span3" type="text" name="mobile_phone" value="{{$mobile_phone|default}}"
                           pattern="1([0-9]{10})" data-validation-pattern-message="号码格式不正确"/>

                    <p>
                        请输入您的手机号码，以便接快递信息（我们承诺不会泄露您的资料，也不会给您发垃圾短信）
                    </p>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label">原始密码</label>

                <div class="controls">
                    <input class="span3" type="password" name="oldpassword" minlength="6"/>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label">新密码</label>

                <div class="controls">
                    <input class="span3" type="password" name="password" minlength="6"/>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label">确认新密码</label>

                <div class="controls">
                    <input class="span3" type="password" name="password_again"
                           data-validation-passwordagain="data-validation-passwordagain"/>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label">&nbsp; </label>

                <div class="controls">
                    <button type="submit" class="btn btn-success">
                        提交修改
                    </button>
                </div>
            </div>

        </form>
        <!-- /我的资料表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}