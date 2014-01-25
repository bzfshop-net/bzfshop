{{extends file='account_layout.tpl'}}
{{block name=account_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#account_tabbar li:has(a[href='{{bzf_make_url controller='/Account/Admin/ListUser'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '管理员编辑', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>管理员详情</h4>

        <!-- 更新管理员信息的表单  -->
        <form class="form-horizontal" method="POST" action="Edit?user_id={{$user_id|default}}">

            <!-- 管理员详细信息 -->
            <div class="row">

                <div class="control-group">
                    <label class="control-label">管理员账号</label>

                    <div class="controls">
                        <input class="span3" type="text" name="user_name" value="{{$user_name|default}}"
                               minlength="3" data-validation-required="data-validation-required"/>
                        <span class="comments">(用于登陆)</span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">是否禁用</label>

                    <div class="controls">
                        <select class="span1 select2-simple" name="disable"
                                data-initValue="{{$disable|default:'0'}}">
                            <option value="0">启用</option>
                            <option value="1">禁用</option>
                        </select>
                        <span class="comments">(禁用之后无法登陆)</span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">在线客服</label>

                    <div class="controls">
                        <select class="span1 select2-simple" name="is_kefu"
                                data-initValue="{{$is_kefu|default:'0'}}">
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                        <span class="comments">(用户可以对在线客服做服务评价)</span>
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
                    <label class="control-label">密&nbsp;&nbsp;&nbsp;码</label>

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