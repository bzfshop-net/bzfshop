{{extends file='stat_layout.tpl'}}
{{block name=stat_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#stat_tabbar li:has(a[href='{{bzf_make_url controller='/Stat/Order/Index'}}'])").addClass("active");
        });
    </script>
    <!-- 订单统计主体内容 -->
    <div class="tabbable tabs-left">

        <!-- 订单统计左侧标签导航 -->
        <ul id="stat_order_tab_left" class="nav nav-tabs">
            <li class=""><a href="{{bzf_make_url controller='/Stat/Order/Refer' }}">来源渠道</a></li>
        </ul>
        <!-- /订单统计左侧标签导航 -->

        <!-- 订单统计各个子页面 -->
        {{block name=stat_order_main_body}}{{/block}}
        <!-- /订单统计各个子页面 -->

    </div>
    <!-- /订单统计主体内容 -->

{{/block}}