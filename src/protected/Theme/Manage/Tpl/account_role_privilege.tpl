{{extends file='account_layout.tpl'}}
{{block name=account_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script>
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