{{extends file='account_layout.tpl'}}
{{block name=account_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#account_tabbar li:has(a[href='{{bzf_make_url controller='/Account/Role/ListRole'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '角色权限', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>角色权限：{{$meta_name|default}}</h4>

        <input type="hidden" id="account_admin_privilege_action_list" value="{{$meta_data|default}}"/>

        <!-- 更新角色权限的表单  -->
        <form class="form-horizontal" method="POST" action="Privilege?meta_id={{$meta_id|default}}">

            <!-- 角色权限 -->
            <table class="table table-bordered">
                <thead>
                <th width="20%">&nbsp;</th>
                <th width="20%">&nbsp;</th>
                <th width="20%">&nbsp;</th>
                <th width="20%">&nbsp;</th>
                <th width="20%">&nbsp;</th>
                </thead>
                <tbody>

                {{foreach $privilegeArray as $privilegeGroup}}
                    <!-- 一个权限组的显示 -->
                    <tr>
                        <td colspan="5" class="well well-small" style="font-weight: bold;">
                        <span rel="tooltip" data-placement="top"
                              data-title="{{$privilegeGroup['meta_desc']|default}}">{{$privilegeGroup['meta_name']|default}}</span>
                        </td>
                    </tr>
                    {{assign var='privilegeItemArrayCount' value=count($privilegeGroup['item_array'])}}
                    {{assign var='privilegeItemArrayCountRound5' value=ceil($privilegeItemArrayCount/5)*5}}
                    {{for $privilegeItemIndex=0 to $privilegeItemArrayCountRound5-1}}

                        {{if $privilegeItemIndex%5 == 0}}
                            <tr>
                        {{/if}}

                        {{if $privilegeItemIndex < $privilegeItemArrayCount}}
                            <td>
                                <div class="admin-privilege">
                                    <input type="checkbox" name="action_code[]"
                                           value="{{$privilegeGroup['item_array'][$privilegeItemIndex]['meta_key']|default}}"/>
                                    <span rel="tooltip" data-placement="top"
                                          data-title="{{$privilegeGroup['item_array'][$privilegeItemIndex]['meta_desc']|default}}">
                                        {{$privilegeGroup['item_array'][$privilegeItemIndex]['meta_name']|default}}
                                    </span>
                                </div>
                            </td>
                        {{else}}
                            <td>&nbsp;</td>
                        {{/if}}

                        {{if ($privilegeItemIndex+1)%5 == 0}}
                            </tr>
                        {{/if}}

                    {{/for}}

                    <!-- /一个权限组的显示 -->
                {{/foreach}}

                <tr>
                    <td colspan="5">
                        <button type="submit" class="btn btn-success">
                            提交更新
                        </button>
                    </td>
                </tr>

                </tbody>
            </table>
            <!-- /角色权限 -->

        </form>
        <!-- /更新角色权限的表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {

            /**
             * account_admin_privilege.tpl 管理员权限页面，根据用户已有的权限设置对应的勾选项
             * */
            (function ($) {

                var actionListStr = $('#account_admin_privilege_action_list').val();
                if (!actionListStr) {
                    // 没有权限设置，返回
                    return;
                }

                // 一头一尾加上 ',' 好做字符串的比较
                actionListStr = ',' + actionListStr + ',';

                // 对每个 checkbox 检查，然后设置值
                $('div.admin-privilege input').each(function (index, elem) {
                    if (actionListStr.indexOf(',' + $(elem).val() + ',') == -1) {
                        // 没有设置这个权限
                        return;
                    }

                    //有这个权限，让勾选勾上
                    $(elem).attr('checked', 'checked');
                });

            })(jQuery);

            /**
             * account_admin_privilege.tpl
             *
             * 管理员权限页面，如果勾选了权限，我们让字体加粗
             *
             * */
            $('div.admin-privilege').each(function (index, elem) {

                var actionFunc = function (divElem) {
                    var checkBoxAttrChecked = $('input', divElem).attr('checked');
                    if (checkBoxAttrChecked) {
                        $('span', divElem).css('font-weight', 'bold');
                        $('span', divElem).css('color', 'blue');
                    } else {
                        $('span', divElem).css('font-weight', 'normal');
                        $('span', divElem).css('color', 'black');
                    }
                };

                // 第一次执行，检查选中状态
                actionFunc(elem);

                // 用户选择之后改变状态
                $('input', elem).on('click', function () {
                    actionFunc(this.parentNode);
                });
            });

            /******************** account_admin_privilege.tpl 设置用户权限页面，查看角色权限 *********************/
            $('#account_admin_privilege_view_role_privilege_button').on('click', function () {
                var roleId = parseInt($('#account_admin_privilege_role_select').find('option:selected').val());
                roleId = isNaN(roleId) ? 0 : roleId;

                if (roleId <= 0) {
                    bZF.showMessage('请先选择正确的角色');
                    return;
                }

                var callUrl = bZF.makeUrl('/Account/Role/Privilege?meta_id=' + roleId);
                window.open(encodeURI(callUrl));
            });

        })(window, jQuery));
    </script>
{{/block}}