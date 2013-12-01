{{extends file='community_layout.tpl'}}
{{block name=community_main_body}}
    <script>
        window.bz_set_nav_status.push(function ($) {
            $("#community_tabbar li:has(a[href='{{bzf_make_url controller='/Community/Article'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 0, text: '技术文章', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div id="bzf_community_article_rss_panel" class="row" style="padding: 0px 30px;">
        文章加载中 ...
    </div>
    <!-- /页面主体内容 -->

{{/block}}