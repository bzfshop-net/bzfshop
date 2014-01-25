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

        {{if isset($goods_search_adv_slider)}}
            <!-- 广告图片轮播 -->
            <div class="row bzf_goods_view_head_slide_image_panel">

                <div class="quake-slider">
                    <div class="quake-slider-images">
                        {{foreach $goods_search_adv_slider as $sliderItem}}
                            <a target="{{$sliderItem['target']}}" href="{{$sliderItem['url']}}">
                                <img src="{{$sliderItem['image']}}"/>
                            </a>
                        {{/foreach}}
                    </div>
                </div>

            </div>
            <!-- /广告图片轮播 -->
        {{/if}}

        <!-- 搜索过滤条件 bar -->
        <form method="GET">
            <div class="row well well-small bzf_goods_search_order_filter_bar" style="margin-top: 10px;">
                <div class="control-group">
                    <div class="controls">

                        <!-- 隐藏的排序设置 -->
                        <input type="hidden" name="category_id"/>
                        <input type="hidden" name="suppliers_id"/>
                        <input type="hidden" name="goods_name"/>
                        <input type="hidden" name="orderBy"/>
                        <input type="hidden" name="orderDir"/>
                        <!-- /隐藏的排序设置 -->

                        <div class="btn-toolbar">
                            <div class="btn-group">
                                <button id="bzf_goods_search_order_filter_bar_button_default"
                                        type="submit" class="btn btn-mini"
                                        data-orderBy="" data-orderDir="">
                                    默认<i class="icon-arrow-down"></i>
                                </button>
                                <button type="submit" class="btn btn-mini"
                                        data-orderBy="total_buy_number" data-orderDir="desc">
                                    销量<i class="icon-arrow-down"></i>
                                </button>
                                <button type="submit" class="btn btn-mini "
                                        data-orderBy="shop_price" data-orderDir="asc">
                                    价格<i class="icon-arrow-down"></i>
                                </button>
                                <button type="submit" class="btn btn-mini "
                                        data-orderBy="add_time" data-orderDir="desc">
                                    最新<i class="icon-arrow-down"></i>
                                </button>
                            </div>
                        </div>
                        <div class="input-prepend">
                            <span class="add-on">￥</span>
                            <input type="text" name="shop_price_min"
                                   pattern="^\d+(\.\d+)?$" data-validation-pattern-message="价格非法"/>
                        </div>
                        <span class="bzf_text">--</span>

                        <div class="input-prepend">
                            <span class="add-on">￥</span>
                            <input type="text" name="shop_price_max"
                                   pattern="^\d+(\.\d+)?$" data-validation-pattern-message="价格非法"/>
                        </div>

                        <button type="submit" class="btn btn-mini btn-success">筛选商品</button>
                    </div>

                </div>
            </div>
        </form>
        <!-- 搜索过滤条件 bar -->

        <!-- 搜索商品的结果 -->
        <div class="row">

            <div class="row" style="text-align: center;">
                <div class="span9">
                    {{if isset($url) }}
                        <h6>错误链接：{{$url}}</h6>
                    {{/if}}
                    <h3>没有匹配的商品，点击<a href="{{bzf_make_url controller='/'}}">返回首页</a></h3>
                </div>

                <div class="span3">
                    <!-- 客服信息 -->
                    <div class="bzf_supplier_pane" style="width:216px;">
                        <div class="header">
                            遇到问题？ - 咨询客服
                        </div>
                        <div class="supplier_pane_content">
                            <div class="bzf_show">
                                <div class="supplier_info" style="text-align: left;">
                                    <p>商 户 名 ：{{bzf_get_option_value optionKey="merchant_name"}}</p>

                                    <p>所 在 地 ：{{bzf_get_option_value optionKey="merchant_address"}}</p>

                                    <p>客服电话：{{bzf_get_option_value optionKey="kefu_telephone"}}</p>

                                    <p>客服 QQ：{{bzf_get_option_value optionKey="kefu_qq"}}</p>
                                </div>
                                <div class="divider"></div>
                                <div class="supplier_info" style="text-align: center;">
                                    <p>周一至周五 10:00-19:00</p>
                                    <a target="_blank"
                                       href="http://wpa.qq.com/msgrd?v=3&uin={{bzf_get_option_value optionKey="kefu_qq"}}&site=qq&menu=yes">
                                        <img border="0"
                                             src="http://wpa.qq.com/pa?p=2:{{bzf_get_option_value optionKey="kefu_qq"}}:41"
                                             alt="QQ在线客服"
                                             title="QQ在线客服"/>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /客服信息 -->
                </div>

            </div>

        </div>
        <!-- /搜索商品的结果 -->

        <!--------------------------------------------  /网页主体内容  -------------------------------------------------->

    </div>
    <!-- /主题内容 row -->

{{/block}}