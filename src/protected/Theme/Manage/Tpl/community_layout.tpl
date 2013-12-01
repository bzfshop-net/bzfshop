{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script>
        window.bz_set_nav_status.push(function ($) {
            $("#system_top_navbar li:has(a[href='{{bzf_make_url controller='/Community/Announce'}}'])").addClass("active");
        });
    </script>
    <div class="row bz_basic_content_block bz_box_shadow" style="padding:10px 10px 10px 10px;">

        <!-- 页面上方导航条 -->
        <div class="row">
            <ul id="community_tabbar" class="nav nav-tabs">
                <li>
                    <a href="{{bzf_make_url controller='/Community/Announce'}}">最新动态</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Community/Article'}}">技术文章</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Community/Group'}}">讨论组</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Community/Calendar'}}">开发日历</a>
                </li>
            </ul>
        </div>
        <!-- /页面上方导航条 -->

        <!-- 开源社区主体内容 -->
        {{block name=community_main_body}}{{/block}}
        <!-- /开源社区主体内容 -->

    </div>
{{/block}}