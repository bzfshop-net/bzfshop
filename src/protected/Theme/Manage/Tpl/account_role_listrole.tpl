{{extends file='account_layout.tpl'}}
{{block name=account_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#account_tabbar li:has(a[href='{{bzf_make_url controller='/Account/Role/ListRole'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '角色列表', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>角色列表</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <a href="{{bzf_make_url controller='/Account/Role/Create'}}" class="btn btn-info">添加角色</a>
        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 角色列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>角色ID</th>
                <th>角色名</th>
                <th width="50%">角色描述</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($roleArray)}}
                {{foreach $roleArray as $roleItem}}
                    <!-- 一个角色 -->
                    <tr>
                        <td>{{$roleItem['meta_id']}}</td>
                        <td>{{$roleItem['meta_name']}}</td>
                        <td>{{$roleItem['meta_desc']}}</td>
                        <td>
                            <a href="{{bzf_make_url controller='/Account/Role/Edit' meta_id=$roleItem['meta_id']}}"
                               class="btn btn-small">编辑</a>
                            <a href="{{bzf_make_url controller='/Account/Role/Privilege' meta_id=$roleItem['meta_id']}}"
                               class="btn btn-small">权限</a>
                            <a href="{{bzf_make_url controller='/Account/Admin/ListUser' role_id=$roleItem['meta_id']}}"
                               class="btn btn-small">用户</a>
                        </td>
                    </tr>
                    <!-- /一个角色 -->
                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /角色列表 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}