{{extends file='account_layout.tpl'}}
{{block name=account_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#account_tabbar li:has(a[href='{{bzf_make_url controller='/Account/Supplier/ListUser'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '供货商编辑', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>供货商详情</h4>

        <!-- 更新供货商信息的表单  -->
        <form class="form-horizontal" method="POST" action="Edit?suppliers_id={{$suppliers_id|default}}">

            <!-- 供货商详细信息 -->
            <div class="row">

                <div class="control-group">
                    <label class="control-label">供货商账号</label>

                    <div class="controls">
                        <input class="span3" type="text" name="suppliers_account" value="{{$suppliers_account|default}}"
                               minlength="4" data-validation-required="data-validation-required"/>
                        <span class="comments">(用于登陆)</span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">供货商名称</label>

                    <div class="controls">
                        <input class="span3" type="text" name="suppliers_name" value="{{$suppliers_name|default}}"
                               minlength="4" data-validation-required="data-validation-required"/>
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
                    <label class="control-label">电话</label>

                    <div class="controls">
                        <input class="span3" type="text" name="phone" value="{{$phone|default}}"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">地址</label>

                    <div class="controls">
                        <input class="span5" type="text" name="address"
                               value="{{$address|default}}"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">供货商描述</label>

                    <div class="controls">
                        <textarea class="span5" rows="3" cols="40"
                                  name="suppliers_desc">{{$suppliers_desc|default}}</textarea>
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
            <!-- /供货商详细信息 -->

        </form>
        <!-- /更新供货商信息的表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}