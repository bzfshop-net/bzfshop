{{extends file='layout.tpl'}}
{{block name=main_body}}

<!-- 用 JS 设置页面的导航菜单 -->
<script type="text/javascript">
    window.bzf_set_nav_status.push(function ($) {
        $("#bzf_header_nav_menu li:has(a[href='{{bzf_make_url controller='/'}}'])").addClass("active");
    });
</script>
<!-- 主题内容 row -->
<div class="row" style="background-color: white;padding-bottom: 10px;">

<!-- 首页，总是显示商品分类 -->
<script type="text/javascript">
    var bZF = {};
    window.bZF = bZF;
    bZF.always_show_bzf_header_dropdown_menu = true;
</script>
<!-- /首页，总是显示商品分类 -->

<!-- 头部广告图片切换栏 -->
<div class="row">

    <!-- 左侧商品分类 -->
    <div class="span3 bzf_shop_index_head_category_back_panel">

    </div>
    <!-- /左侧商品分类 -->

    <!-- 左侧商品分类 -->
    <div class="span9 bzf_shop_index_head_slide_image_panel">

        <div class="quake-slider">
            <div class="quake-slider-images">

                {{if !isset($shop_index_adv_slider)}}
                    <a target="_blank" href="javascript:">
                        <img src="{{bzf_get_asset_url asset='img/placeholder_780x350_black.gif'}}"/>
                    </a>
                    <a target="_blank" href="javascript:">
                        <img src="{{bzf_get_asset_url asset='img/placeholder_780x350_gray.gif'}}"/>
                    </a>
                {{else}}

                    {{foreach $shop_index_adv_slider as $sliderItem}}
                        <a target="{{$sliderItem['target']}}" href="{{$sliderItem['url']}}">
                            <img src="{{$sliderItem['image']}}"/>
                        </a>
                    {{/foreach}}

                {{/if}}

            </div>
        </div>

    </div>
    <!-- /左侧商品分类 -->

</div>
<!-- /头部广告图片切换栏 -->


<!-- 网站公告、店长商品推荐 -->
<div class="row" style="padding:10px 0px;">

    <!-- 网站公告 -->
    <div class="span3">

        <ul class="nav nav-tabs" style="margin-bottom: 0px;">
            <li class="active"><a href="#bzf_shop_index_site_notice" data-toggle="tab">网站公告</a></li>
        </ul>

        <div class="tab-content bzf_shop_index_site_notice_panel">
            <div class="tab-pane active" id="bzf_shop_index_site_notice">
                {{bzf_get_option_value optionKey='shop_index_notice'}}
            </div>
        </div>

    </div>
    <!-- /网站公告 -->

    <!-- 店长推荐商品 -->
    <div class="span9">

        <div class="row" style="font-size: 14px;font-weight: bold;padding-bottom: 10px;">
            欢迎光临&nbsp;&nbsp;<span style="color:red;">{{bzf_get_option_value optionKey="site_name"}}</span>&nbsp;&nbsp;
            <span class="label label-important">
                <a target="_blank"
                   style="color: white;text-decoration: none;"
                   href="{{bzf_make_url controller='/Goods/Search' orderBy='add_time' orderDir='desc' static=false}}">今日新品</a>
            </span>
        </div>

        {{if isset($recommandGoodsArray)}}
            <div class="jcarousel-skin-goods-recommand-compact">
                <ul>
                    {{foreach $recommandGoodsArray as $goodsItem}}

                        <!-- 一个店长推荐商品 -->
                        <li>
                            <div class="bzf_image_wrap">
                                <a target="_blank" title="{{$goodsItem['goods_name']}}"
                                   href="{{bzf_make_url controller='/Goods/View' goods_id=$goodsItem['goods_id']}}">
                                    <img class="lazyload" src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                         data-original="{{bzf_goods_thumb_image goods_id=$goodsItem['goods_id']}}"/>
                                </a>
                            </div>
                            <div class="bzf_goods_view_goods_recommand_price_pane">
                                <div class="market_price">
                                    原价：{{$goodsItem['market_price']|bzf_money_display}}元
                                </div>
                                <div class="shop_price">
                                    现价：<span>{{$goodsItem['shop_price']|bzf_money_display}}元</span>
                                </div>
                            </div>
                        </li>
                        <!-- /一个店长推荐商品  -->

                    {{/foreach}}
                </ul>
            </div>
        {{/if}}

    </div>
    <!-- 店长推荐商品 -->

</div>
<!-- /网站公告、店长商品推荐 -->


{{if !isset($shop_index_advblock_json_data)}}

    <!-- 一个缺省的分类展示区 -->
    <div class="row bzf_shop_index_adv_block bzf_shop_index_adv_block_theme_red">

        <!-- 头部标签切换 -->
        <ul class="nav nav-tabs">
            <li><a class="bzf_caption" href="#" onclick="return false;"><span>1F</span>女装系列</a>
            </li>
            <li class="active">
                <a href="#bzf_shop_index_adv_block_1_2" data-toggle="tab">上衣裙子</a>
            </li>
        </ul>
        <!-- /头部标签切换 -->

        <!-- 标签对应内容 -->
        <div class="tab-content">
            <!-- 一个广告 block 的图片区 -->
            <div id="bzf_shop_index_adv_block_1_2"
                 class="tab-pane active bzf_shop_index_adv_image_block">

                <!-- 左侧小图片 -->
                <div class="span3">
                    <a class="image_left" href="#"
                       data-target="_blank" data-url="#"
                       data-image="{{bzf_get_asset_url asset='img/placeholder_238x490.gif'}}">
                        <img src="{{bzf_get_asset_url asset='img/placeholder_238x490.gif'}}"/>
                    </a>
                </div>
                <!-- /左侧小图片 -->

                <!-- 中间大图 -->
                <div class="span6">
                    <a class="image_center" href="#"
                       data-target="_blank" data-url="#"
                       data-image="{{bzf_get_asset_url asset='img/placeholder_490x490.gif'}}">
                        <img src="{{bzf_get_asset_url asset='img/placeholder_490x490.gif'}}"/>
                    </a>
                </div>
                <!-- /中间大图 -->

                <!-- 右侧小图片 -->
                <div class="span3">
                    <a class="image_right" href="#"
                       data-target="_blank" data-url="#"
                       data-image="{{bzf_get_asset_url asset='img/placeholder_238x158.gif'}}">
                        <img src="{{bzf_get_asset_url asset='img/placeholder_238x158.gif'}}"/>
                    </a>
                    <a class="image_right" href="#"
                       data-target="_blank" data-url="#"
                       data-image="{{bzf_get_asset_url asset='img/placeholder_238x158.gif'}}">
                        <img src="{{bzf_get_asset_url asset='img/placeholder_238x158.gif'}}"/>
                    </a>
                    <a class="image_right" href="#"
                       data-target="_blank" data-url="#"
                       data-image="{{bzf_get_asset_url asset='img/placeholder_238x158.gif'}}">
                        <img src="{{bzf_get_asset_url asset='img/placeholder_238x158.gif'}}"/>
                    </a>
                </div>
                <!-- /右侧小图片 -->

            </div>
            <!-- /一个广告 block 的图片区 -->

        </div>
        <!-- /标签对应内容 -->

    </div>
    <!-- /一一个缺省的分类展示区 -->

{{else}}

    <!-- =============================== 生成 advBlock ============================ -->

{{assign var="advBlockObjectIndex" value=0}}
    {{foreach $shop_index_advblock_json_data as $advBlockObject}}

    <!-- 一个分类展示区 -->
<div class="row bzf_shop_index_adv_block {{$advBlockObject['theme_class']}}">

    <!-- 头部标签切换 -->
    <ul class="nav nav-tabs">
        <li><a class="bzf_caption" href="#"
               onclick="return false;"><span>{{$advBlockObjectIndex+1}}F</span>{{$advBlockObject['title']}}</a>
        </li>
        {{assign var="advBlockImageArray" value=$advBlockObject['advBlockImageArray'] }}
        {{assign var="advBlockImageArrayIndex" value=0}}
        {{foreach $advBlockImageArray as $advBlockImageItem}}
            {{if 0 == $advBlockImageArrayIndex}}
                <li class="active">
                    {{else}}
                <li>
            {{/if}}
            <a href="#{{$advBlockImageItem['id']}}"
               data-toggle="tab">{{$advBlockImageItem['title']}}</a>
            </li>
            {{assign var="advBlockImageArrayIndex" value=$advBlockImageArrayIndex+1}}
        {{/foreach}}
    </ul>
    <!-- /头部标签切换 -->

    <!-- 标签对应内容 -->
    <div class="tab-content">

        {{assign var="advBlockImageArray" value=$advBlockObject['advBlockImageArray'] }}
        {{assign var="advBlockImageArrayIndex" value=0}}
        {{foreach $advBlockImageArray as $advBlockImageItem}}

        <!-- 一个广告 block 的图片区 -->

        {{if 0 == $advBlockImageArrayIndex}}
        <div id="{{$advBlockImageItem['id']}}"
             class="tab-pane active bzf_shop_index_adv_image_block">
            {{else}}
            <div id="{{$advBlockImageItem['id']}}" class="tab-pane bzf_shop_index_adv_image_block">
                {{/if}}

                <!-- 左侧小图片 -->
                <div class="span3">
                    {{foreach $advBlockImageItem['image_left'] as $imageItem}}
                        <a class="image_left" target="{{$imageItem['target']}}" href="{{$imageItem['url']}}">
                            <img class="lazyload" src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                 data-original="{{$imageItem['image']}}"/>
                        </a>
                    {{/foreach}}
                </div>
                <!-- /左侧小图片 -->

                <!-- 中间大图 -->
                <div class="span6">
                    {{foreach $advBlockImageItem['image_center'] as $imageItem}}
                        <a class="image_center" target="{{$imageItem['target']}}" href="{{$imageItem['url']}}">
                            <img class="lazyload" src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                 data-original="{{$imageItem['image']}}"/>
                        </a>
                    {{/foreach}}
                </div>
                <!-- /中间大图 -->

                <!-- 右侧小图片 -->
                <div class="span3">
                    {{foreach $advBlockImageItem['image_right'] as $imageItem}}
                        <a class="image_right" target="{{$imageItem['target']}}" href="{{$imageItem['url']}}">
                            <img class="lazyload" src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                 data-original="{{$imageItem['image']}}"/>
                        </a>
                    {{/foreach}}
                </div>
                <!-- /右侧小图片 -->

            </div>
            <!-- /一个广告 block 的图片区 -->

            {{assign var="advBlockImageArrayIndex" value=$advBlockImageArrayIndex+1}}
            {{/foreach}}

        </div>
        <!-- /标签对应内容 -->

    </div>
    <!-- /一个分类展示区 -->

    {{assign var="advBlockObjectIndex" value=$advBlockObjectIndex+1 }}
    {{/foreach}}

    <!-- =============================== /生成 advBlock ============================ -->

    {{/if}}

</div>
<!-- /主题内容 row -->

{{/block}}
