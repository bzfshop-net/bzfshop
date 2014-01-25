{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#system_top_navbar li:has(a[href='{{bzf_make_url controller='/Article/Index'}}'])").addClass("active");
        });
    </script>
    <div class="row bz_basic_content_block bz_box_shadow" style="padding:10px 10px 10px 10px;">

        <!-- 页面上方导航条 -->
        <div class="row">
            <ul id="article_tabbar" class="nav nav-tabs">
                <li>
                    <a href="{{bzf_make_url controller='/Article/Article/Search'}}">文章列表</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Article/Category'}}">文章分类</a>
                </li>
            </ul>
        </div>
        <!-- /页面上方导航条 -->

        <!-- 文章管理主体内容 -->
        {{block name=article_main_body}}{{/block}}
        <!-- /文章管理主体内容 -->

    </div>
{{/block}}