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

    {{if isset($goods_view_adv_slider)}}
        <!-- 广告图片轮播 -->
        <div class="row bzf_goods_view_head_slide_image_panel">

            <div class="quake-slider">
                <div class="quake-slider-images">
                    {{foreach $goods_view_adv_slider as $sliderItem}}
                        <a target="{{$sliderItem['target']}}" href="{{$sliderItem['url']}}">
                            <img src="{{$sliderItem['image']}}"/>
                        </a>
                    {{/foreach}}
                </div>
            </div>

        </div>
        <!-- /广告图片轮播 -->
    {{/if}}

    {{if isset($goodsCategoryLevelArray)}}
        <!-- 商品所处的分类层级 -->
        <div class="row">
            <ul class="breadcrumb">
                {{foreach $goodsCategoryLevelArray as $goodsCategoryItem}}
                    <li>
                        <a target="_blank"
                           href="{{bzf_make_url controller='/Goods/Category' category_id=$goodsCategoryItem['meta_id']}}">
                            {{$goodsCategoryItem['meta_name']}}
                        </a>
                        <span class="divider">&gt;</span>
                    </li>
                {{/foreach}}
                <li>
                    <a title="{{$goodsInfo['goods_name']}}"
                       href="{{bzf_make_url controller='/Goods/View' goods_id=$goodsInfo['goods_id']}}">
                        当前商品
                    </a>
                    <span class="divider">&nbsp;</span>
                </li>
            </ul>
        </div>
        <!-- /商品所处的分类层级 -->
    {{/if}}

    <!-- 商品详情头部，包括头图和价格区域 -->
    <div class="row">

    <!-- 商品头图 -->
    <div class="span6">

        <!-- 商品大图 -->
        <div class="bzf_goods_view_big_image">
            <a href="{{bzf_get_asset_url asset='img/placeholder_800x800_gray.gif'}}"
               id="bzf_goods_view_zoom_image" class="cloud-zoom"
               rel="position:'right', adjustX: 10, zoomWidth: 400, zoomHeight: 400">
                <img src="{{bzf_get_asset_url asset='img/blank.gif'}}"/>
            </a>
        </div>
        <!-- /商品大图 -->

        <!-- 商品缩略图列表 -->
        <div id="bzf_goods_view_thumb_image_slider" class="jcarousel-skin-goods-image-thumbnail">
            <ul>
                {{if !isset($goodsGalleryArray)}}
                    <!-- 一个缩略图 -->
                    <li>
                        <a href="{{bzf_get_asset_url asset='img/placeholder_800x800_gray.gif'}}"
                           class="cloud-zoom-gallery"
                           rel="useZoom:'bzf_goods_view_zoom_image',smallImage:'{{bzf_get_asset_url asset='img/placeholder_460x460_gray.gif'}}',title:'空图片'">
                            <img class="lazyload" src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                 data-original="{{bzf_get_asset_url asset='img/placeholder_250x250.gif'}}"/>
                        </a>
                    </li>
                    <!-- /一个缩略图 -->
                {{else}}
                    {{foreach $goodsGalleryArray as $goodsGalleryItem}}
                        <!-- 一个缩略图 -->
                        <li>
                            <a href="{{$goodsGalleryItem['img_original']}}"
                               data-img_id="{{$goodsGalleryItem['img_id']}}"
                               class="cloud-zoom-gallery"
                               rel="useZoom:'bzf_goods_view_zoom_image',smallImage:'{{$goodsGalleryItem["img_url"]}}',title:'{{$goodsGalleryItem["img_desc"]}}'">
                                <img class="lazyload" src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                     data-original="{{$goodsGalleryItem['thumb_url']}}"/>
                            </a>
                        </li>
                        <!-- /一个缩略图 -->
                    {{/foreach}}
                {{/if}}
            </ul>
        </div>
        <!-- 商品缩略图列表 -->

    </div>
    <!-- /商品头图 -->

    <!-- 商品价格和描述 -->
    <div class="span6" style="padding-bottom: 20px;">

        <!-- 隐藏变量 -->
        <input type="hidden" id="bzf_goods_view_goods_id_input" value="{{$goodsInfo['goods_id']}}"/>
        <!-- 商品的库存 -->
        <input type="hidden" id="bzf_goods_view_goods_number" value="{{$goodsInfo['goods_number']}}"/>

        <!-- 商品大标题 -->
        <div id="bzf_goods_title_caption" class="bzf_goods_title_caption">
            <h2><span class="bzf_prefix">&nbsp;</span>{{$goodsInfo['goods_name']}}</h2>
        </div>
        <!-- /商品大标题 -->

        <div style="padding:5px 0px;">
            <span>原价：</span>
            <span style="text-decoration: line-through;">￥{{$goodsInfo['market_price']|bzf_money_display}}</span>
        </div>
        <div style="padding:5px 0px 5px 0px;">
            <span>现价：</span>
                <span id="bzf_goods_view_shop_price_input_span"
                      style="color:#E4006E;font-size:20px;font-weight: bold;">￥{{$goodsInfo['shop_price']|bzf_money_display}}</span>
            {{if (isset($goodsInfo['shop_price_notice']) && !empty($goodsInfo['shop_price_notice']))}}
                <label class="label label-warning">{{$goodsInfo['shop_price_notice']}}</label>
            {{/if}}
            <input type="hidden" id="bzf_goods_view_shop_price_input"
                   value="{{$goodsInfo['shop_price']|bzf_money_display}}"/>
        </div>

        <!-- 不同的价格，用于不同的显示 -->
        <input id="bzf_goods_special_price_360tequan_price" type="hidden"
               value="{{$goodsPromote['360tequan_price']|bzf_money_display}}"/>
        <input id="bzf_goods_special_price_360tequan_price_notice" type="hidden"
               value="360特权价"/>
        <input id="bzf_goods_special_price_360tequan_price_goods_title_prefix" type="hidden"
               value="【360特权团购，立减{{($goodsInfo['shop_price'] - $goodsPromote['360tequan_price'])|bzf_money_display}}元】"/>
        <!-- 不同的价格，用于不同的显示 -->

        <!-- 用户特权价格 -->
        <div id="bzf_goods_special_price" style="padding:5px 0px;display: none;">
            <label class="label label-success">360特权价</label>
            <span style="color:#E4006E;font-size:20px;font-weight: bold;">￥15</span>
        </div>
        <!-- 用户特权价格 -->

        <div style="padding:5px 0px;">
            <span>邮费：</span>
                    <span style="color:#E4006E;font-weight: bold;">
                        {{if $goodsInfo['shipping_fee'] > 0}}
                            ￥{{$goodsInfo['shipping_fee']|bzf_money_display}}
                        {{else}}
                            商家包邮
                        {{/if}}
                    </span>
        </div>

        {{if $goodsInfo['shipping_free_number'] > 0}}
            <div style="padding:5px 0px;">
                <label class="label label-success">邮费优惠</label>
                本商品任选
                <span style="font-weight: bold;color: #E4006E;padding: 0px 5px;">{{$goodsInfo['shipping_free_number']}}</span>件以上免邮费
            </div>
        {{/if}}

        {{if !empty($goodsSpecNameArray) }}
            <!-- 商品加价 -->
            <input type="hidden" id="bzf_goods_view_goods_spec_add_price" value="0"/>
            <!-- JS 记录商品 Spec 属性 -->
            <script type="text/javascript">
                var goods_view_goods_spec_json = '{{$goodsSpecJson nofilter}}';
            </script>
            <!-- 商品的选择 -->
            <table class="table table-bordered bzf_goods_view_select">

                {{assign var='goodsSpecValue1ArrayStr' value=implode('',$goodsSpecValue1Array)}}
                {{if !empty($goodsSpecValue1ArrayStr)}}
                    <!-- 一级选择 -->
                    <tr id="goods_view_spec_value1">
                        <td width="15%">{{$goodsSpecNameArray[0]|default}}</td>
                        <td>
                            <!-- 不同的选项 -->
                            {{foreach array_unique($goodsSpecValue1Array) as $specValue }}
                                {{if empty($specValue)}}
                                    <!-- empty value -->
                                {{else}}
                                    <span onclick="bZF.goods_view_choose_spec(1,this);">
                                        <input type="radio" name="goods_view_spec_value1" value="{{$specValue}}"/>
                                        <label>{{$specValue}}</label>
                                        <i class="bzf_goods_view_spec_select_icon"></i>
                                    </span>
                                {{/if}}
                            {{/foreach}}
                        </td>
                    </tr>
                    <!-- 一级选择 -->
                {{/if}}

                {{assign var='goodsSpecValue2ArrayStr' value=implode('',$goodsSpecValue2Array)}}
                {{if !empty($goodsSpecValue2ArrayStr)}}
                    <!-- 二级选择 -->
                    <tr id="goods_view_spec_value2">
                        <td width="15%">{{$goodsSpecNameArray[1]|default}}</td>
                        <td>
                            <!-- 不同的选项 -->
                            {{foreach array_unique($goodsSpecValue2Array) as $specValue }}
                                {{if empty($specValue)}}
                                    <!-- empty value -->
                                {{else}}
                                    <span class="inactive" onclick="bZF.goods_view_choose_spec(2,this);">
                                        <input type="radio" name="goods_view_spec_value2" value="{{$specValue}}"/>
                                        <label>{{$specValue}}</label>
                                        <i class="bzf_goods_view_spec_select_icon"></i>
                                    </span>
                                {{/if}}
                            {{/foreach}}
                        </td>
                    </tr>
                    <!-- 二级选择 -->
                {{/if}}


                {{assign var='goodsSpecValue3ArrayStr' value=implode('',$goodsSpecValue3Array)}}
                {{if !empty($goodsSpecValue3ArrayStr)}}
                    <!-- 三级选择 -->
                    <tr id="goods_view_spec_value3">
                        <td width="15%">{{$goodsSpecNameArray[2]|default}}</td>
                        <td>
                            <!-- 不同的选项 -->
                            {{foreach array_unique($goodsSpecValue3Array) as $specValue }}
                                {{if empty($specValue)}}
                                    <!-- empty value -->
                                {{else}}
                                    <span class="inactive" onclick="bZF.goods_view_choose_spec(3,this);">
                                        <input type="radio" name="goods_view_spec_value3" value="{{$specValue}}"/>
                                        <label>{{$specValue}}</label>
                                        <i class="bzf_goods_view_spec_select_icon"></i>
                                    </span>
                                {{/if}}
                            {{/foreach}}
                        </td>
                    </tr>
                    <!-- 三级选择 -->
                {{/if}}

            </table>
            <!-- /商品的选择 -->
        {{/if}} <!-- !empty($goodsSpecValue1Array)-->

        <!-- 购买数量 -->
        <div style="padding:5px 0px 5px 0px;">
            <span>购买数量：</span><span><input id="goods_view_buy_count" type="text" value="1"/></span>
            <span id="goods_view_goods_number_display">
                    (库存 {{$goodsInfo['goods_number']}} 件)
            </span>
                <span style="margin-left: 50px;">
                    <a class="btn btn-info btn-small" target="_blank"
                       href="{{bzf_make_url controller='/Goods/Category' category_id=$goodsInfo['cat_id']}}">
                        查看同类商品
                    </a>
                </span>
        </div>

        <!-- 加入购物车 -->
        {{if $goodsInfo['is_on_sale'] > 0 }}
        <button class="bzf_button_big" onclick="bZF.goods_view_goods_buy();"> <!-- 可以购买 -->
            {{else}}
            <button class="bzf_button_big disabled"> <!-- 不可以购买 -->
                {{/if}}
                <span class="bzf_icon_cart_white"
                      style="line-height: 38px;vertical-align: middle;margin-right: 5px;"></span>
                加入购物车
            </button>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <!-- 去购物车结算 -->
            <a class="bzf_button_big"
               href="{{bzf_make_url controller='/Cart/Show'}}">
                去购物车结算->
            </a>

    </div>
    <!-- /商品价格和描述 -->

    </div>
    <!-- /商品详情头部，包括头图和价格区域 -->


    <!-- 相关商品推荐区域 -->
    <div class="row bzf_goods_view_relate_goods_recommand">

        <!-- 标签导航头 -->
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#bzf_goods_view_link_goods_pane" data-toggle="tab">
                    <span rel="tooltip" data-placement="top"
                          data-title="商城推荐商品">关联推荐</span>
                </a>
            </li>
            <li>
                <a href="#bzf_goods_view_same_supplier_goods_pane" data-toggle="tab">
                    <span rel="tooltip" data-placement="top"
                          data-title="商品搭配购买，立即享受免邮">搭配免邮</span>
                </a>
            </li>
        </ul>
        <!-- /标签导航头 -->

        <div class="tab-content">

            <!-- 关联商品推荐 -->
            <div id="bzf_goods_view_link_goods_pane" class="tab-pane active">
                {{if isset($linkGoodsArray)}}
                    <div class="jcarousel-skin-goods-recommand">
                        <ul>
                            {{foreach $linkGoodsArray as $linkGoodsItem}}
                                <!-- 一个关联商品 -->
                                <li>
                                    <div class="bzf_image_wrap">
                                        <a target="_blank" title="{{$linkGoodsItem['goods_name']}}"
                                           href="{{bzf_make_url controller='/Goods/View' goods_id=$linkGoodsItem['goods_id']}}">
                                            <img class="lazyload" src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                                 data-original="{{bzf_goods_thumb_image goods_id=$linkGoodsItem['goods_id']}}"/>
                                        </a>
                                    </div>
                                    <div class="bzf_goods_view_goods_recommand_price_pane">
                                        <div class="market_price">
                                            原价：{{$linkGoodsItem['market_price']|bzf_money_display}}元
                                        </div>
                                        <div class="shop_price">
                                            现价：<span>{{$linkGoodsItem['shop_price']|bzf_money_display}}元</span>
                                        </div>
                                    </div>
                                </li>
                                <!-- /一个关联商品 -->
                            {{/foreach}}
                        </ul>
                    </div>
                {{/if}}
            </div>
            <!-- /关联商品推荐 -->


            <!-- 相同供货商商品 -->
            <div id="bzf_goods_view_same_supplier_goods_pane" class="tab-pane">
                {{if isset($supplierGoodsArray)}}
                    <div class="row" style="margin-bottom: 10px;padding-right: 15px;">
                        <a class="btn btn-success pull-right" target="_blank"
                           href="{{bzf_make_url controller='/Goods/Search' static=false suppliers_id=$goodsInfo['suppliers_id']}}">查看更多搭配免邮商品</a>
                    </div>
                    <div class="jcarousel-skin-goods-recommand">
                        <ul>
                            {{foreach $supplierGoodsArray as $supplierGoodsItem}}
                                <!-- 一个关联商品 -->
                                <li>
                                    <div class="bzf_image_wrap">
                                        <a target="_blank" title="{{$supplierGoodsItem['goods_name']}}"
                                           href="{{bzf_make_url controller='/Goods/View' goods_id=$supplierGoodsItem['goods_id']}}">
                                            <img class="lazyload" src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                                 data-original="{{bzf_goods_thumb_image goods_id=$supplierGoodsItem['goods_id']}}"/>
                                        </a>
                                    </div>
                                    <div class="bzf_goods_view_goods_recommand_price_pane">
                                        <div class="market_price">
                                            原价：{{$supplierGoodsItem['market_price']|bzf_money_display}}元
                                        </div>
                                        <div class="shop_price">
                                            现价：<span>{{$supplierGoodsItem['shop_price']|bzf_money_display}}元</span>
                                        </div>
                                    </div>
                                </li>
                                <!-- /一个关联商品 -->
                            {{/foreach}}
                        </ul>
                    </div>
                {{/if}}
            </div>
        </div>
        <!-- /相同供货商商品 -->

    </div>
    <!-- /相关商品推荐区域 -->


    <!-- 商品下方区域 -->
    <div class="row" style="margin-top: 20px;padding: 0px 0px 0px 10px;">

        <!-- 左侧边栏 -->
        <div class="span3" style="width:216px;">

            <!-- 供货商信息 -->
            <div id="bzf_goods_view_supplier_pane" class="bzf_supplier_pane">

                <!-- 悬浮的时候显示的头部 -->
                <div class="bzf_sticky_header">
                    在线客服&nbsp;<a target="_blank"
                                 href="http://wpa.qq.com/msgrd?v=3&uin={{bzf_get_option_value optionKey="kefu_qq"}}&site=qq&menu=yes">
                        <img border="0"
                             src="http://wpa.qq.com/pa?p=2:{{bzf_get_option_value optionKey="kefu_qq"}}:41"
                             alt="QQ在线客服"
                             title="QQ在线客服"/>
                    </a>
                </div>
                <!-- /悬浮的时候显示的头部 -->

                <!-- 商家信息的主面板 -->
                <div class="bzf_hide bzf_supplier_main_pane">

                    <div class="header">
                        商家信息
                    </div>

                    <div class="supplier_pane_content">

                        <div class="supplier_banner">
                            <span style="font-size: 14px;font-weight: bold;">SHOP</span> bangzhufu.com
                        </div>
                        <div class="supplier_promise"></div>
                        <div class="divider"></div>
                        <div class="supplier_info">
                            <p><b>店铺动态评分</b></p>

                            <p>描述相符：<span>4.9</span>分</p>

                            <p>发货速度：<span>4.8</span>分</p>
                        </div>

                        <div class="divider"></div>
                        <div class="supplier_info">
                            <p>商 户 名 ：{{bzf_get_option_value optionKey="merchant_name"}}</p>

                            <p>所 在 地 ：{{bzf_get_option_value optionKey="merchant_address"}}</p>

                            <p>商务合作：QQ <a target="_blank"
                                          href="http://wpa.qq.com/msgrd?v=3&uin={{bzf_get_option_value optionKey="business_qq"}}&site=qq&menu=yes">{{bzf_get_option_value optionKey="business_qq"}}</a>
                            </p>

                            <p>客服电话：{{bzf_get_option_value optionKey="kefu_telephone"}}</p>

                        </div>
                        <div class="divider"></div>
                        <div class="supplier_info" style="text-align: center;">
                            <p>客服 QQ：{{bzf_get_option_value optionKey="kefu_qq"}}</p>

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
                <!-- /商家信息的主面板 -->

            </div>
            <!-- /供货商信息 -->

            <!-- 商品浏览历史，由 JavaScript 生成 -->
            <div id="bzf_goods_view_history" class="bzf_goods_view_history">
                <div class="header">
                    最近浏览
                </div>
                <!-- 一个商品 -->
                <!-- div class="goods_view_item">
                    <a href="#">
                        <img src="#"/></a>

                    <p class="price">￥100.34</p>
                </div -->
                <!-- /一个商品 -->
            </div>
            <!-- /商品浏览历史，由 JavaScript 生成 -->

        </div>
        <!-- 左侧边栏 -->

        <!-- 右侧商品详情区域 -->
        <div class="span9 bzf_goods_view_goods_content" style="margin-left: 6px; width: 760px;">

            <!-- 标签导航头 -->
            <div id="bzf_goods_view_goods_detail_tabbar">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#bzf_goods_view_goods_desc_pane" data-toggle="tab">商品详情</a></li>
                    <li><a href="#bzf_goods_view_goods_notice_pane" data-toggle="tab">商品提示</a></li>
                    <li><a href="#bzf_goods_view_goods_attr_pane" data-toggle="tab">商品参数</a></li>
                    <li><a href="#bzf_goods_view_goods_comments_pane" data-toggle="tab">用户评价</a></li>
                    <li><a href="#bzf_goods_view_goods_shouhou_pane" data-toggle="tab">售后服务</a></li>
                </ul>
            </div>
            <!-- /标签导航头 -->

            <!-- 标签对应的内容 -->
            <div class="tab-content">

                <!-- 商品详情内容 -->
                <div id="bzf_goods_view_goods_desc_pane" class="tab-pane active">
                    {{bzf_get_option_value optionKey="goods_view_detail_notice"}}
                    {{$goodsInfo['goods_desc']|bzf_html_image_lazyload nofilter}}
                </div>
                <!-- /商品详情内容 -->

                <!-- 商品提示 -->
                <div id="bzf_goods_view_goods_notice_pane" class="tab-pane">
                    {{$goodsInfo['goods_notice'] nofilter}}
                </div>
                <!-- /商品提示 -->

                <!-- 商品规格参数 -->
                <div id="bzf_goods_view_goods_attr_pane" class="tab-pane">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th width="20%">&nbsp;</th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{if isset($goodsAttrTreeTable)}}
                            {{foreach $goodsAttrTreeTable as $goodsAttrValue}}
                                {{if 'goods_type_attr_group' == $goodsAttrValue['meta_type']}}
                                    <!-- 属性分组 -->
                                    <tr>
                                        <td class="attrlabel" style="font-weight: bold;"
                                            colspan="2">{{$goodsAttrValue['meta_name']}}</td>
                                    </tr>
                                {{else}}
                                    <!-- 属性值 -->
                                    <tr>
                                        <td class="labelkey attrlabel">{{$goodsAttrValue['meta_name']}}</td>
                                        {{if 'textarea' == $goodsAttrValue['meta_ename']}}
                                            <td class="labelvalue">{{$goodsAttrValue['attr_item_value']|nl2br nofilter}}</td>
                                        {{else}}
                                            <td class="labelvalue">{{$goodsAttrValue['attr_item_value']}}</td>
                                        {{/if}}
                                    </tr>
                                {{/if}}
                            {{/foreach}}
                        {{/if}}
                        </tbody>
                    </table>
                </div>
                <!-- /商品规格参数 -->

                <!-- 顾客评价 -->
                <div id="bzf_goods_view_goods_comments_pane" class="tab-pane">
                    <!-- 第一页的评价在一开始就生成在页面里面，后面的评价使用 ajax 载入，这样做的目的是希望做 SEO 优化有更多的内容 -->
                    {{bzf_goods_comment goods_id=$goods_id}}
                </div>
                <!-- /顾客评价 -->

                <!-- 售后服务 -->
                <div id="bzf_goods_view_goods_shouhou_pane" class="tab-pane">
                    {{if !empty($goodsInfo['goods_after_service'])}}
                        {{$goodsInfo['goods_after_service'] nofilter}}
                        <br/>
                    {{/if}}
                    {{bzf_get_option_value optionKey="goods_after_service"}}
                </div>
                <!-- /售后服务 -->

            </div>
            <!-- /标签对应的内容 -->

        </div>
        <!-- 右侧商品详情区域 -->

    </div>
    <!-- /商品下方区域 -->

    </div>
    <!-- /主体内容 row -->
    <div class="row" style="height: 60px;background-color: white;"></div>
{{/block}}
