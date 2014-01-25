{{extends file='account_layout.tpl'}}
{{block name=account_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#account_tabbar li:has(a[href='{{bzf_make_url controller='/Account/Admin/ListUser'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '管理员列表', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>管理员列表</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">管理员账号</span>
                        <input class="span2" type="text" name="user_name" value="{{$user_name|default}}"/>
                        <span class="input-label">管理员姓名</span>
                        <input class="span2" type="text" name="user_real_name" value="{{$user_real_name|default}}"/>
                        <span class="input-label">管理员角色</span>
                        <select id="account_admin_privilege_role_select"
                                class="span2 select2-simple" name="role_id" style="float:left;"
                                data-placeholder="选择角色"
                                data-initValue="{{$role_id|default}}"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Role/ListRole"}}"
                                data-option-value-key="meta_id" data-option-text-key="meta_name">
                            <option value=""></option>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">管理员描述</span>
                        <input class="span4" type="text" name="user_desc" value="{{$user_desc|default}}"/>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-success">查询</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <a href="{{bzf_make_url controller='/Account/Admin/Create'}}" class="btn btn-info">添加管理员</a>
                    </div>
                </div>
            </form>
        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 管理员列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>管理员ID</th>
                <th>管理员账号</th>
                <th>管理员姓名</th>
                <th>角色</th>
                <th>在线客服</th>
                <th width="50%">管理员描述</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($adminUserArray)}}
                {{foreach $adminUserArray as $adminUserItem}}
                    <!-- 一个管理员 -->
                    {{if $adminUserItem['disable']}}
                        <tr class="error">
                            {{else}}
                        <tr>
                    {{/if}}
                    <td>{{$adminUserItem['user_id']}}</td>
                    <td>{{$adminUserItem['user_name']}}</td>
                    <td>{{$adminUserItem['user_real_name']}}</td>
                    <td>{{$adminUserItem['role_name']}}</td>
                    <td>
                        {{if $adminUserItem['is_kefu'] > 0}}
                            <i class="icon-ok"></i>
                        {{/if}}
                    </td>
                    <td>{{$adminUserItem['user_desc']}}</td>
                    <td>
                        <a href="{{bzf_make_url controller='/Account/Admin/Edit' user_id=$adminUserItem['user_id']}}"
                           class="btn btn-small">编辑</a>
                        <a href="{{bzf_make_url controller='/Account/Admin/Privilege' user_id=$adminUserItem['user_id']}}"
                           class="btn btn-small">权限</a>
                    </td>
                    </tr>
                    <!-- /一个管理员 -->
                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /管理员列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}