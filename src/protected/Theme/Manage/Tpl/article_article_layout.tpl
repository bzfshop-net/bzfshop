{{extends file='article_layout.tpl'}}
{{block name=article_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#article_tabbar li:has(a[href='{{bzf_make_url controller='/Article/Article/Search'}}'])").addClass("active");
        });
    </script>
    <!-- 商品编辑页面主体内容 -->
    <div class="tabbable tabs-left">

        <!-- 编辑页面左侧标签导航 -->
        <ul id="article_edit_tab_left" class="nav nav-tabs">
            <li>
                <a href="{{bzf_make_url controller='/Article/Article/Edit' article_id=$article_id }}">文章内容</a>
            </li>
        </ul>
        <!-- /编辑页面左侧标签导航 -->

        <!-- 文章不同编辑项目的页面 -->
        {{block name=article_edit_main_body}}{{/block}}
        <!-- /文章不同编辑项目的页面 -->

    </div>
    <!-- /商品编辑页面主体内容 -->

{{/block}}