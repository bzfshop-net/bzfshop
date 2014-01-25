{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#system_top_navbar li:has(a[href='{{bzf_make_url controller='/Account/Index'}}'])").addClass("active");
        });
    </script>
    <div class="row bz_basic_content_block bz_box_shadow" style="padding:10px 10px 10px 10px;">

        <!-- 页面上方导航条 -->
        <div class="row">
            <ul id="account_tabbar" class="nav nav-tabs">
                <li><a href="{{bzf_make_url controller='/Account/User/Search'}}">用户</a></li>
                <li><a href="{{bzf_make_url controller='/Account/User/Money'}}">用户资金</a></li>
                <li><a href="{{bzf_make_url controller='/Account/Supplier/ListUser'}}">供货商</a></li>
                <li><a href="{{bzf_make_url controller='/Account/Admin/ListUser'}}">管理员</a></li>
                <li><a href="{{bzf_make_url controller='/Account/Role/ListRole'}}">管理员角色</a></li>
                <li><a href="{{bzf_make_url controller='/Account/Admin/ListLog'}}">管理员日志</a></li>
            </ul>
        </div>
        <!-- /页面上方导航条 -->

        <!-- 账户管理主体内容 -->
        {{block name=account_main_body}}{{/block}}
        <!-- /账户管理主体内容 -->

    </div>
{{/block}}