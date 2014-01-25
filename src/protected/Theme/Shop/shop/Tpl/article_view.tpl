{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bzf_set_nav_status.push(function ($) {
            $("#bzf_header_nav_menu li:has(a[href='{{bzf_make_url controller='/'}}'])").addClass("active");
        });
    </script>
    <!-- 主体内容 row -->
    <div class="row" style="background-color: white;padding-bottom: 10px;">

        <!--------------------------------------------  网页主体内容  --------------------------------------------------------->

        <div style="padding:10px;">
            {{$articleInfo['content']|bzf_html_image_lazyload nofilter}}
        </div>

        <!--------------------------------------------  /网页主体内容  -------------------------------------------------->

    </div>
    <!-- /主题内容 row -->

{{/block}}