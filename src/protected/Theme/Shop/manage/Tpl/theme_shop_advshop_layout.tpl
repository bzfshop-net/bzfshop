{{extends file='theme_shop_layout.tpl'}}
{{block name=theme_shop_main_body}}
    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#theme_shop_tabbar li:has(a[href='{{bzf_make_url controller='/Theme/Shop/AdvShopSlider'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 1, text: '首页广告', link: window.location.href});
    </script>
    <!-- 首页广告设置 -->
    <div class="tabbable tabs-left">

        <!-- 页面左侧标签导航 -->
        <ul id="theme_shop_advindex_left_tabbar" class="nav nav-tabs">
            <li class=""><a href="{{bzf_make_url controller='/Theme/Shop/AdvShopSlider'}}">滑动图片</a></li>
            <li class=""><a href="{{bzf_make_url controller='/Theme/Shop/AdvShopBlock'}}">区块广告</a></li>
        </ul>
        <!-- /页面左侧标签导航 -->

        <!-- 不同的设置页面 -->
        {{block name=theme_shop_advindex_body}}{{/block}}
        <!-- /不同的设置页面 -->

    </div>
    <!-- 首页广告设置 -->

{{/block}}
