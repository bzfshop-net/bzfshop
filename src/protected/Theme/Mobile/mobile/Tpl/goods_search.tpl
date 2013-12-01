{{extends file='page_layout.tpl'}}
{{block name=main_page_content}}
    <ul class="listview_goods" data-role="listview" data-inset="false">

        <li data-role="list-divider" class="listview_head">商品搜索</li>

        {{if isset($goodsArray)}}

            {{foreach from=$goodsArray item='goodsItem' }}
                <!-- 一个商品 -->
                <li>
                    <img class="lazyload ui-li-thumb bzf_zoom"
                         src="{{bzf_get_asset_url asset='img/lazyload_placeholder_298_223.png'}}"
                         data-original="{{$goodsThumbImageArray[$goodsItem['goods_id']]|default:''}}"
                         data-image-zoom="{{$goodsImageArray[$goodsItem['goods_id']]|default:''}}"/>
                    <noscript>
                        <img class="ui-li-thumb"
                             src="{{$goodsThumbImageArray[$goodsItem['goods_id']]|default:'' }}"/>
                    </noscript>

                    <a style="padding-left: 0em;"
                       href="{{bzf_make_url controller='/Goods/View' goods_id=$goodsItem['goods_id']}}"
                       data-transition="flip">
                        <h2>
                            <span class="current_price_label">现价:</span>
                            <span class="current_price">￥{{$goodsItem['shop_price']|bzf_money_display}}</span>
                        </h2>

                        <h2>
                            <span class="original_price_label">原价:</span>
                            <span class="original_price">￥{{$goodsItem['market_price']|bzf_money_display}}</span>
                        </h2>

                        <p>{{$goodsItem['goods_name']}}</p>
                    </a>
                </li>
                <!-- /一个商品 -->
            {{/foreach}}

        {{else}}
            <li style="text-align: center;">搜索没有商品</li>
        {{/if}}

    </ul>
    <br/>
    <!-- 分页 -->
    {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
    <!-- /分页 -->

{{/block}}