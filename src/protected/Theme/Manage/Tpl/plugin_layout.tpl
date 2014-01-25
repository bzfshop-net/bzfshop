{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#system_top_navbar li:has(a[href='{{bzf_make_url controller='/Plugin/Index'}}'])").addClass("active");
        });
    </script>
    <div class="row bz_basic_content_block bz_box_shadow" style="padding:10px 10px 10px 10px;">

        <!-- 页面上方导航条 -->
        <div class="row">
            <ul id="plugin_tabbar" class="nav nav-tabs">
                <li><a href="{{bzf_make_url controller='/Plugin/Plugin/ListPlugin'}}">插件管理</a></li>
                <li><a href="{{bzf_make_url controller='/Plugin/Theme/ListTheme'}}">主题管理</a></li>
            </ul>
        </div>
        <!-- /页面上方导航条 -->

        <!-- 插件管理主体内容 -->
        {{block name=plugin_main_body}}{{/block}}
        <!-- /插件管理主体内容 -->

    </div>
{{/block}}