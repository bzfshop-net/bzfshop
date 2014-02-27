{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Search'}}'])").addClass("active");
        });
    </script>
    <!-- 商品编辑页面主体内容 -->
    <div class="tabbable tabs-left">

        <!-- 编辑页面左侧标签导航 -->
        <ul id="goods_edit_tab_left" class="nav nav-tabs">
            <li class=""><a href="{{bzf_make_url controller='/Goods/Edit/Edit' goods_id=$goods_id }}">商品信息</a></li>
            <li class=""><a href="{{bzf_make_url controller='/Goods/Edit/Gallery' goods_id=$goods_id }}">商品相册</a></li>
            <li class=""><a href="{{bzf_make_url controller='/Goods/Edit/Spec' goods_id=$goods_id }}">商品规格</a></li>
            <li class=""><a href="{{bzf_make_url controller='/Goods/Edit/Type' goods_id=$goods_id }}">类型属性</a></li>
            <li class=""><a href="{{bzf_make_url controller='/Goods/Edit/LinkGoods' goods_id=$goods_id }}">关联商品</a></li>
            <li class=""><a href="{{bzf_make_url controller='/Goods/Edit/Promote' goods_id=$goods_id }}">推广渠道</a></li>
            <li class=""><a href="{{bzf_make_url controller='/Goods/Edit/Log' goods_id=$goods_id }}">编辑日志</a></li>
            <li class=""><a href="{{bzf_make_url controller='/Goods/Edit/Cron' goods_id=$goods_id }}">定时任务</a></li>
        </ul>
        <!-- /编辑页面左侧标签导航 -->

        <!-- 商品不同编辑项目的页面 -->
        {{block name=goods_edit_main_body}}{{/block}}
        <!-- /商品不同编辑项目的页面 -->

    </div>
    <!-- /商品编辑页面主体内容 -->

{{/block}}