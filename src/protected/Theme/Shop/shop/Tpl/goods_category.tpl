{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bzf_set_nav_status.push(function ($) {
            $("#bzf_header_nav_menu li:has(a[href='{{bzf_make_url controller='/'}}'])").addClass("active");
        });
    </script>
    <!-- 主体内容 row -->
    <div class="row" style="padding-bottom: 10px;">

    <!----------------------  网页主体内容  ---------------------->

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

    <!-- 左侧栏 -->
    <div class="span2" style="width:192px;">

        {{if isset($goodsCategoryTreeArray)}}
            <!-- 商品分类 -->
            <div id="bzf_goods_category_tree_table_panel" class="row bzf_basic_content_block">
                <table class="table table-bordered table-hover" style="margin-bottom: 0px;">
                    <tbody>
                    {{foreach $goodsCategoryTreeArray as $goodsCategoryItem}}
                        <tr class="bzf_parent" data-tt-id="bzf_goods_category_{{$goodsCategoryItem['meta_id']}}">
                            <td>
                                <a href="{{bzf_make_url controller='/Goods/Category' category_id=$goodsCategoryItem['meta_id']}}">{{$goodsCategoryItem['meta_name']}}</a>
                            </td>
                        </tr>
                        {{if isset($goodsCategoryItem['child_list'])}}
                            {{foreach $goodsCategoryItem['child_list'] as $childCategory}}
                                <tr data-tt-id="bzf_goods_category_{{$childCategory['meta_id']}}"
                                    data-tt-parent-id="bzf_goods_category_{{$childCategory['parent_meta_id']}}">
                                    <td>
                                        <a href="{{bzf_make_url controller='/Goods/Category' category_id=$childCategory['meta_id']}}">{{$childCategory['meta_name']}}</a>
                                    </td>
                                </tr>
                            {{/foreach}}
                        {{/if}}
                    {{/foreach}}
                    </tbody>
                </table>
            </div>
            <!-- /商品分类 -->
        {{/if}}

        <!-- 商品浏览历史，由 JavaScript 生成 -->
        <div id="bzf_goods_search_history" class="bzf_goods_view_history">
            <div class="header">
                最近浏览
            </div>
        </div>
        <!-- /商品浏览历史，由 JavaScript 生成 -->

    </div>
    <!-- /左侧栏 -->


    <!-- 右侧栏 -->
    <div class="span10" style="margin-left: 6px; width:804px;">

        <!-- 上面的过滤面板 -->
        <div class="row bzf_basic_content_block">
            <form method="POST" style="margin-bottom: 0px;">

                <!-- 属性过滤面板 -->
                {{if isset($goodsFilterArray)}}
                    <table class="table" id="bzf_goods_search_filter_panel" style="margin-bottom: 0px;">
                        <tbody>
                        <tr>
                            <td colspan="3" style="background-color: #F7F7F7;font-size:14px;padding: 5px 5px;">
                                <span style="color:#FF6600;">{{$category['meta_name']}}</span>&nbsp;--&nbsp;商品筛选
                                <!-- 隐藏的设置 -->
                                <input type="hidden" name="filter" value="{{$filter|default}}"/>
                                <input type="hidden" name="brand_id" value="{{$brand_id|default}}"/>
                                <!-- /隐藏的设置 -->
                            </td>
                        </tr>
                        {{foreach $goodsFilterArray as $filterLabel => $filterItem}}
                            <!-- 商品属性过滤 -->
                            <tr>
                                <td width="9%" class="labelkey">{{$filterLabel}}</td>
                                <td>
                                    <div class="bzf_choose_div" data-filterKey="{{$filterItem['filterKey']}}">
                                        {{foreach $filterItem['filterValueArray'] as $valueItem}}
                                            <button class="btn btn-mini" type="button"
                                                    data-filterValue="{{$valueItem['value']}}">
                                                {{$valueItem['text']}}<i class="icon-remove"></i>
                                            </button>
                                        {{/foreach}}
                                    </div>
                                    <div class="bzf_confirm_div">
                                        <button class="btn btn-small btn-success" type="button"
                                                onclick="bZF.goods_filter.submitForm();">确认
                                        </button>
                                        <button class="btn btn-small" type="button"
                                                onclick="bZF.goods_filter.filterMultiChooseClose(this.parentNode.parentNode.parentNode);">
                                            取消
                                        </button>
                                    </div>
                                </td>
                                <td width="6%">
                                    <button class="btn btn-mini bzf_multi_choose" type="button"
                                            onclick="bZF.goods_filter.filterMultiChooseOpen(this.parentNode.parentNode);">
                                        多选
                                    </button>
                                </td>
                            </tr>
                            <!-- /商品属性过滤 -->
                        {{/foreach}}
                        </tbody>
                    </table>
                {{/if}}
                <!-- /属性过滤面板 -->

                <!-- 结果排序，价格区间搜索 -->
                <table class="table" style="margin-bottom: 0px;">
                    <tbody>
                    <tr>
                        <td class="labelkey" width="8%">&nbsp;</td>
                        <td style="text-align: left;">
                            <div class="row bzf_goods_search_order_filter_bar">
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
                                            <input type="text" name="shop_price_min"
                                                   value=""
                                                   pattern="^\d+(\.\d+)?$" data-validation-pattern-message="价格非法"/>
                                        </div>
                                        <span class="bzf_text">--</span>

                                        <div class="input-prepend">
                                            <span class="add-on">￥</span>
                                            <input type="text" name="shop_price_max"
                                                   value=""
                                                   pattern="^\d+(\.\d+)?$" data-validation-pattern-message="价格非法"/>
                                        </div>

                                        <button type="submit" class="btn btn-mini btn-success">筛选商品</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <!-- /结果排序，价格区间搜索 -->

            </form>
        </div>
        <!-- /上面的过滤面板 -->

        <!-- 搜索商品的结果 -->
        <div class="row" style="margin-top: 10px;">

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

    </div>
    <!-- /右侧栏 -->

    <!--------------------------------------------  /网页主体内容  -------------------------------------------------->

    </div>
    <!-- /主题内容 row -->

{{/block}}
