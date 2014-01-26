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
            <ul id="theme_shop_tabbar" class="nav nav-tabs">
                <li>
                    <a href="{{bzf_make_url controller='/Theme/Shop/Basic'}}">基本信息</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Theme/Shop/AdvShopSlider'}}">首页广告</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Theme/Shop/GoodsSearchSlider'}}">搜索页广告</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Theme/Shop/GoodsViewSlider'}}">详情页广告</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Theme/Shop/HeadNav'}}">头部导航</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Theme/Shop/FootNav'}}">底部导航</a>
                </li>
            </ul>
        </div>
        <!-- /页面上方导航条 -->

        <!-- 商品管理主体内容 -->
        {{block name=theme_shop_main_body}}{{/block}}
        <!-- /商品管理主体内容 -->

    </div>
{{/block}}
