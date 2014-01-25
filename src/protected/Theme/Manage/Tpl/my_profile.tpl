{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#system_top_navbar li:has(a[href='{{bzf_make_url controller='/My/Profile'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 0, text: '我的资料', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row bz_basic_content_block bz_box_shadow" style="padding:10px 10px 10px 10px;">
        <h4>我的资料</h4>

        <!-- 更新管理员信息的表单  -->
        <form class="form-horizontal" method="POST">

            <!-- 管理员详细信息 -->
            <div class="row">

                <div class="control-group">
                    <label class="control-label">管理员账号</label>

                    <div class="controls">
                        <input class="span3" type="text" value="{{$user_name|default}}" disabled="disabled"/>
                        <span class="comments">(用于登陆)</span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">管理员名称</label>

                    <div class="controls">
                        <input class="span3" type="text" name="user_real_name" value="{{$user_real_name|default}}"
                               minlength="2" data-validation-required="data-validation-required"/>
                        <span class="comments">(用于列表显示)</span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">旧密码</label>

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
                    <label class="control-label">确认密码</label>

                    <div class="controls">
                        <input class="span3" type="password" name="password_again"
                               data-validation-passwordagain="data-validation-passwordagain"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">管理员描述</label>

                    <div class="controls">
                        <textarea class="span5" rows="3" cols="40"
                                  name="user_desc">{{$user_desc|default}}</textarea>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">&nbsp; </label>

                    <div class="controls">
                        <button type="submit" class="btn btn-success">
                            更新
                        </button>
                    </div>
                </div>

            </div>
            <!-- /管理员详细信息 -->

        </form>
        <!-- /更新管理员信息的表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}