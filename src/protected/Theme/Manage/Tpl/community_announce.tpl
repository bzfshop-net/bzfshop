{{extends file='community_layout.tpl'}}
{{block name=community_main_body}}
    <script>
        window.bz_set_nav_status.push(function ($) {
            $("#community_tabbar li:has(a[href='{{bzf_make_url controller='/Community/Announce'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 0, text: '最新动态', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        <div class="span8">
            <iframe style="border-width:0px;"
                    src="http://www.bzfshop.net/article/224.html?utm_source=bzfshop"
                    width="780" height="600" frameborder="0" scrolling="auto"></iframe>
        </div>

        <div class="span3" style="padding-left: 20px;">

            <div class="accordion" id="accordion0">
                <div class="accordion-group">
                    <div class="accordion-heading">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion0"
                           href="#collapse0">
                            棒主妇开源商城--v{{$version}}
                        </a>
                    </div>
                    <div id="collapse0" class="accordion-body collapse in">
                        <div class="accordion-inner">
                            程序发布时间：{{$release_date}}<br/><br/>
                            程序更新请查看左边的最新动态
                        </div>
                    </div>
                </div>
                <div class="accordion-group">
                    <div class="accordion-heading">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion0"
                           href="#collapse1">
                            棒主妇开源商城--技术资料
                        </a>
                    </div>
                    <div id="collapse1" class="accordion-body collapse">
                        <div class="accordion-inner">
                            <a target="_blank"
                               href="http://www.bzfshop.net/article/category/bzfshop?utm_source=bzfshop">点击查看</a>棒主妇开源商城
                            相关的技术文章
                        </div>
                    </div>
                </div>
                <div class="accordion-group">
                    <div class="accordion-heading">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion0"
                           href="#collapse2">
                            棒主妇开源商城--正式文档
                        </a>
                    </div>
                    <div id="collapse2" class="accordion-body collapse">
                        <div class="accordion-inner">

                            <div class="dropdown clearfix">
                                <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu"
                                    style="display: block; position: static; margin-bottom: 5px; *width: 180px;">
                                    <li><a tabindex="-1" target="_blank"
                                           href="http://doc.bzfshop.net?utm_source=bzfshop">使用手册</a>
                                    </li>
                                    <li class="divider"></li>
                                    <li><a tabindex="-1" target="_blank"
                                           href="http://doc.bzfshop.net?utm_source=bzfshop">开发手册</a>
                                    </li>
                                    <li><a tabindex="-1" target="_blank"
                                           href="http://doc.bzfshop.net?utm_source=bzfshop">设计手册</a>
                                    </li>
                                    <li><a tabindex="-1" target="_blank"
                                           href="http://doc.bzfshop.net?utm_source=bzfshop">部署手册</a>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="accordion-group">
                    <div class="accordion-heading">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion0"
                           href="#collapse3">
                            棒主妇开源商城--技术支持
                        </a>
                    </div>
                    <div id="collapse3" class="accordion-body collapse">
                        <div class="accordion-inner">
                            <p><a target="_blank" href="https://groups.google.com/d/forum/bzfshop-group">Google
                                    Group 讨论组</a> (需要<a target="_blank"
                                                        href="https://code.google.com/p/openwrt-smarthosts-autoddvpn/">翻墙</a>)
                            </p>

                            <p>QQ群 134820563</p>

                            <p>邮件发送 <a href="mailto:bzfshop-support@bzfshop.net">bzfshop-support@bzfshop.net</a></p>

                            <p>目前主要使用 Google Group，其它方式为辅（其它辅助方式可能无人理会你的消息）</p>

                            <p>QQ群用的很少，我们是 Linux 工作环境，上 QQ 很不方便，用过 Linux 都懂的</p>

                            <p>无重大事情不要发邮件，否则会进入黑名单永不解封，切记、切记</p>

                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row" style="height: 30px;"></div>

    </div>
    <!-- /页面主体内容 -->

{{/block}}