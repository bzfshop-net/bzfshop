{{extends file='community_layout.tpl'}}
{{block name=community_main_body}}
    <script>
        window.bz_set_nav_status.push(function ($) {
            $("#community_tabbar li:has(a[href='{{bzf_make_url controller='/Community/Calendar'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 0, text: '开发日历', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        <div class="row" style="text-align: right;">
            (我们采用的是 Google Calendar，如果看不到日历显示，说明你的网络被盾了需要
            <a target="_blank" href="https://code.google.com/p/openwrt-smarthosts-autoddvpn/">翻墙</a>
            才能看)
        </div>

        <!-- 棒主妇开源日历 -->
        <iframe src="https://www.google.com/calendar/embed?showPrint=0&amp;showCalendars=0&amp;height=800&amp;wkst=2&amp;bgcolor=%23FFFFFF&amp;src=sitetuan.com_4fpkj9k336r4n2pli60mt23pc0%40group.calendar.google.com&amp;color=%232F6309&amp;ctz=Asia%2FShanghai"
                style=" border-width:0 " width="1180" height="800" frameborder="0" scrolling="no"></iframe>
        <!-- /棒主妇开源日历 -->

        <div class="row" style="height: 30px;"></div>

    </div>
    <!-- /页面主体内容 -->

{{/block}}