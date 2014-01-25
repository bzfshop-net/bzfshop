{{extends file='stat_layout.tpl'}}
{{block name=stat_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#stat_tabbar li:has(a[href='{{bzf_make_url controller='/Stat/Kaohe/Index'}}'])").addClass("active");
        });
    </script>
    <!-- 业绩考核主体内容 -->
    <div class="tabbable tabs-left">

        <!-- 业绩考核左侧标签导航 -->
        <ul id="stat_kaohe_tab_left" class="nav nav-tabs">
            <li class=""><a href="{{bzf_make_url controller='/Stat/Kaohe/Kefu' }}">客服业绩</a></li>
        </ul>
        <!-- /业绩考核左侧标签导航 -->

        <!-- 业绩考核各个子页面 -->
        {{block name=stat_kaohe_main_body}}{{/block}}
        <!-- /业绩考核各个子页面 -->

    </div>
    <!-- /业绩考核主体内容 -->

{{/block}}