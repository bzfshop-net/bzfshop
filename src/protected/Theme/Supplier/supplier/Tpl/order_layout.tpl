{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#system_top_navbar li:has(a[href='{{bzf_make_url controller='/Order/Goods/Search'}}'])").addClass("active");
        });
    </script>
    <div class="row bz_basic_content_block bz_box_shadow" style="padding:10px 10px 10px 10px;">

        <!-- 页面上方导航条 -->
        <div id="order_tabbar" class="row">
            <ul class="nav nav-tabs">
                <li><a href="{{bzf_make_url controller='/Order/Goods/Search'}}">售出商品</a></li>
                <li><a href="{{bzf_make_url controller='/Order/Refund/Search'}}">商品退款</a></li>
                <li><a href="{{bzf_make_url controller='/Order/Excel'}}">批量下载订单</a></li>
                <li><a href="{{bzf_make_url controller='/Order/Settle/ListSettle'}}">结算历史</a></li>
            </ul>
        </div>
        <!-- /页面上方导航条 -->

        <!-- 订单管理主体内容 -->
        {{block name=order_main_body}}{{/block}}
        <!-- /订单管理主体内容 -->

    </div>
{{/block}}