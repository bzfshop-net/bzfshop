{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script>
        window.bzf_set_nav_status.push(function ($) {
            $("#bzf_header_nav_menu li:has(a[href='{{bzf_make_url controller='/'}}'])").addClass("active");
        });
    </script>
    <!-- 主体内容 row -->
    <div class="row" style="padding-bottom: 10px;">

        <!--------------------------------------------  网页主体内容  --------------------------------------------------------->

        {{if isset($goods_search_adv_slider)}}
            <!-- 广告图片轮播 -->
            <div class="row bzf_goods_view_head_slide_image_panel" style="margin-bottom: 10px;">

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

        <div id="goods_category_filter_panel" class="row bzf_basic_content_block" style="margin-bottom: 10px;">

            <table class="table" style="margin-bottom: 0px;">

                <!-- 商品分类列表 -->
                {{if isset($categoryLevelList)}}
                    <tr class="bzf_hide">
                        <td class="labelkey">商品分类</td>
                        <td>
                            {{foreach $categoryLevelList as $categoryLevelItem}}
                                <div class="bzf_goods_category_panel"
                                     category_active_id="{{$categoryLevelItem['category_active_id']|default}}">
                                    {{foreach $categoryLevelItem['category_list'] as $category}}
                                        <a category_id="{{$category['meta_id']}}"
                                           href="{{bzf_make_url controller='/Goods/Category' category_id=$category['meta_id'] }}">{{$category['meta_name']}}</a>
                                    {{/foreach}}
                                </div>
                            {{/foreach}}
                        </td>
                    </tr>
                {{/if}}
                <!-- /商品分类列表 -->

                <!-- 搜索过滤条件 bar -->
                <tr>
                    <td class="labelkey" width="8%">&nbsp;</td>
                    <td style="text-align: left;">
                        <div class="row bzf_goods_search_order_filter_bar">
                            <form method="POST" style="margin-bottom: 0px;">
                                <div class="control-group">
                                    <div class="controls">

                                        <!-- 隐藏的排序设置 -->
                                        <input type="hidden" name="category_id" value="{{$category_id|default}}"/>
                                        <input type="hidden" name="orderBy" value="{{$orderBy|default}}"/>
                                        <input type="hidden" name="orderDir" value="{{$orderDir|default}}"/>
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
                                            <input type="text" name="shop_price_min" value="{{$shop_price_min|default}}"
                                                   pattern="^\d+(\.\d+)?$" data-validation-pattern-message="价格非法"/>
                                        </div>
                                        <span class="bzf_text">--</span>

                                        <div class="input-prepend">
                                            <span class="add-on">￥</span>
                                            <input type="text" name="shop_price_max" value="{{$shop_price_max|default}}"
                                                   pattern="^\d+(\.\d+)?$" data-validation-pattern-message="价格非法"/>
                                        </div>

                                        <button type="submit" class="btn btn-mini btn-success">筛选商品</button>
                                    </div>

                                </div>

                            </form>
                        </div>
                    </td>
                </tr>
                <!-- 搜索过滤条件 bar -->

            </table>

        </div>

        <!-- 搜索商品的结果 -->
        <div class="row">

            {{if !isset($goodsArray)}}
                <div class="row" style="text-align: center;">
                    <h3>没有匹配的商品</h3>
                </div>
            {{else}}
                {{foreach $goodsArray as $goodsItem}}

                    <!-- 一个商品 -->
                    <div class="span4 bzf_basic_content_block bzf_goods_search_goods_item">

                        <div class="row bzf_goods_image">
                            <a target="_blank" title="{{$goodsItem['goods_name']}}"
                               href="{{bzf_make_url controller='/Goods/View' goods_id=$goodsItem['goods_id']}}">
                                <img class="lazyload" src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                     data-original="{{bzf_goods_thumb_image goods_id=$goodsItem['goods_id']}}"/>
                            </a>

                            <div class="row bzf_goods_name">{{$goodsItem['goods_name']}}</div>
                        </div>

                        <div class="row bzf_goods_price">
                            <span class="price"><i>¥</i><em>{{$goodsItem['shop_price']|bzf_money_int_part}}</em>.{{$goodsItem['shop_price']|bzf_money_float_part}}</span>

                            {{if 0 != $goodsItem['market_price']}}
                                {{assign var='divider' value=$goodsItem['market_price']}}
                            {{else}}
                                {{assign var='divider' value=1}}
                            {{/if}}

                            <span class="dock">
                        <span class="discount"><em>{{10*round($goodsItem['shop_price']/$divider,2)}}</em>折</span><br/>
                        <span class="orig-price">¥{{$goodsItem['market_price']|bzf_money_display}}</span>
                        </span>
                            <span class="sold-num"><em>{{$goodsItem['total_buy_number']}}</em>人已买</span>
                        </div>

                    </div>
                    <!-- /一个商品 -->

                {{/foreach}}
            {{/if}}

        </div>
        <!-- /搜索商品的结果 -->

        <!-- 分页 -->
        <div class="row pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:60 }}
        </div>
        <!-- 分页 -->

        <!--------------------------------------------  /网页主体内容  -------------------------------------------------->

    </div>
    <!-- /主题内容 row -->

{{/block}}