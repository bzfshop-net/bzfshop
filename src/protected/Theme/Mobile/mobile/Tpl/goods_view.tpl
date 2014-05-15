{{extends file='page_layout.tpl'}}
{{block name=main_page_content}}

    <!-- 商品标题 -->
    <h4>{{$goodsInfo['goods_name']}}</h4>
    <!-- /商品标题 -->

    <!-- 商品头图和价格 -->
    <div class="ui-grid-a">

        <!-- 头图 -->
        <div class="ui-block-a">

            {{if isset($goodsGalleryArray)}}
                {{assign var='goodsMainImage' value=$goodsGalleryArray[0]['img_url'] }}
                {{assign var='goodsThumbImage' value=$goodsGalleryArray[0]['thumb_url'] }}
            {{/if}}

            <img class="lazyload goods_thumbnail_image bzf_zoom" style="border: 1px solid silver;"
                 src="{{bzf_get_asset_url asset='img/lazyload_placeholder_460_344.png'}}"
                 data-original="{{$goodsThumbImage|default}}"
                 data-image-zoom="{{$goodsMainImage|default}}"/>
            <noscript>
                <img class="goods_thumbnail_image" style="border: 1px solid silver;"
                     src="{{$goodsThumbImage|default}}"/>
            </noscript>

        </div>
        <!-- 头图 -->

        <!-- 商品价格信息 -->
        <div class="ui-block-b" style="padding: 10px 10px;">
            <div class="ui-grid-a">
                <div class="ui-block-a" style="font-weight: bold;">现价</div>
                <div class="ui-block-b" style="color:red;font-weight: bold;">
                    ￥{{$goodsInfo['shop_price']|bzf_money_display}}</div>
                <div class="ui-block-a" style="font-weight: bold;">原价</div>
                <div class="ui-block-b" style="text-decoration: line-through;color:blue;">
                    ￥{{$goodsInfo['market_price']|bzf_money_display}}</div>
                <div class="ui-block-a" style="font-weight: bold;">邮费</div>
                <div class="ui-block-b" style="color:red;font-weight: bold;">
                    ￥{{$goodsInfo['shipping_fee']|bzf_money_display}}</div>
                {{if $goodsInfo['shipping_free_number'] > 0}}
                    <div class="ui-block-a" style="font-weight: bold;">提示</div>
                    <div class="ui-block-b">
                        {{$goodsInfo['shipping_free_number']}}件免邮
                    </div>
                {{/if}}
            </div>
        </div>
        <!-- 商品价格信息 -->

        <div class="ui-block-a">
            <a data-role="button" data-rel="back" data-mini="true"
               data-theme="b" data-icon="arrow-l" data-transition="flip"
               href="{{bzf_make_url controller='/'}}">返回前页</a>
        </div>

        <div class="ui-block-b">
            <a data-role="button" data-mini="true" data-theme="b"
               href="tel:{{bzf_get_option_value optionKey='kefu_telephone'}}">客服电话</a>
        </div>

    </div>
    <!-- 隐藏变量 -->
    <input type="hidden" id="bzf_goods_view_goods_id_input" value="{{$goodsInfo['goods_id']}}"/>
    <!-- /隐藏变量 -->

    <!-- 商品的规格选择 -->
    <label for="goods_choose_speclist">商品选择</label>
    {{if isset($goodsSpec) }}
        <select id="goods_choose_speclist" name="goods_choose_speclist" data-native-menu="false">
            {{foreach $goodsSpec as $specKey => $specItem }}
                {{if $specItem['goods_number'] > 0}} <!-- 有库存才能选择 -->
                    <option value="{{$specKey}}" goods_number="{{$specItem['goods_number']}}">
                        {{$specKey}}
                        {{if $specItem['goods_spec_add_price'] > 0}}(加{{$specItem['goods_spec_add_price']|bzf_money_display}}元){{/if}}
                        [库存{{$specItem['goods_number']}}]
                    </option>
                {{/if}}
            {{/foreach}}
        </select>
    {{else}}
        <select id="goods_choose_speclist" name="goods_choose_speclist" data-native-menu="false">
            <option value="" goods_number="{{$goodsInfo['goods_number']}}">
                默认规格 [库存{{$goodsInfo['goods_number']}}]
            </option>
        </select>
    {{/if}}
    <label for="goods_view_buy_count">购买数量*</label>
    <input type="number" id="goods_view_buy_count" value="1"
           name="goods_view_buy_count"
           pattern="[0-9]+" min="1" required="required"/>
    <!-- /商品的规格选择 -->

    <!-- 加入购物车 -->
    <div class="ui-grid-a">
        <div class="ui-block-a">
            <input id="bzf_goods_view_add_goods_to_cart" type="button" data-theme="e" value="加入购物车"/>
        </div>
        <div class="ui-block-b">
            <a data-role="button" data-theme="f" data-transition="slide"
               href="{{bzf_make_url controller='/Cart/Show'}}">查看购物车</a>
        </div>
    </div>
    <!-- /加入购物车 -->

    <!-- 商品的各种介绍 -->
    <div data-role="collapsible-set" data-collapsed-icon="arrow-r" data-expanded-icon="arrow-d"
         data-theme="c" data-content-theme="d">

        <div data-role="collapsible">
            <h3>商品简介</h3>
            {{$goodsInfo['goods_brief'] nofilter}}
        </div>
        <div data-role="collapsible">
            <h3>商品提示</h3>
            {{$goodsInfo['goods_notice'] nofilter}}
        </div>
        <div data-role="collapsible">
            <h3>商品详情</h3>
            <button data-theme="e" data-mini="true" onclick="bZF.load_all_lazy_image();">
                点击显示全部图片
            </button>
            {{$goodsInfo['goods_desc']|bzf_html_image_lazyload nofilter}}
        </div>
        <div data-role="collapsible">
            <h3>售后服务</h3>
            {{if !empty($goodsInfo['goods_after_service'])}}
                {{$goodsInfo['goods_after_service'] nofilter}}
                <br/>
            {{/if}}
            {{bzf_get_option_value optionKey="goods_after_service"}}
        </div>
        <div data-role="collapsible">
            <h3>商家信息</h3>

            <p>该商品由 {{bzf_get_option_value optionKey='merchant_name'}} 提供</p>
            <a data-role="button" href="tel:{{bzf_get_option_value optionKey='kefu_telephone'}}" data-theme="f">商家电话</a>

        </div>
    </div>
    <!-- /商品的各种介绍 -->

{{/block}}