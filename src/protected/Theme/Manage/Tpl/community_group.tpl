{{extends file='community_layout.tpl'}}
{{block name=community_main_body}}
    <script>
        window.bz_set_nav_status.push(function ($) {
            $("#community_tabbar li:has(a[href='{{bzf_make_url controller='/Community/Group'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 0, text: '讨论组', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">


        <div class="row" style="text-align: right;">
            (我们采用的是 Google Group，如果看不到显示，说明你的网络被盾了需要
            <a target="_blank" href="https://code.google.com/p/openwrt-smarthosts-autoddvpn/">翻墙</a>
            才能看)
        </div>

        <iframe id="forum_embed"
                src="javascript:void(0)"
                scrolling="no"
                frameborder="0"
                width="1180"
                height="1200">
        </iframe>
        <script type="text/javascript">
            document.getElementById('forum_embed').src =
                    'https://groups.google.com/forum/embed/?place=forum/bzfshop-group'
                            + '&showsearch=false&showpopout=true&showtabs=false'
                            + '&parenturl=' + encodeURIComponent(window.location.href);
        </script>

    </div>
    <!-- /页面主体内容 -->

{{/block}}