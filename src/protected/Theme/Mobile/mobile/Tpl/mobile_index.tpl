{{extends file='page_layout.tpl'}}
{{block name=main_page_content}}

    {{if isset($categoryArray) }}
        <ul class="listview_goods" data-role="listview" data-inset="false">

            {{foreach $categoryArray as $categoryItem }}

                {{if isset($categoryGoodsArray[$categoryItem['meta_id']]) }}
                    <li data-role="list-divider" class="listview_head">{{$categoryItem['meta_name']}}</li>
                    {{foreach $categoryGoodsArray[$categoryItem['meta_id']] as $goodsItem }}
                        <li>
                            <img class="lazyload ui-li-thumb bzf_zoom"
                                 src="{{bzf_get_asset_url asset='img/lazyload_placeholder_298_223.png'}}"
                                 data-original="{{$goodsThumbImageArray[$goodsItem['goods_id']]|default:''}}"
                                 data-image-zoom="{{$goodsImageArray[$goodsItem['goods_id']]|default:''}}"/>
                            <noscript>
                                <img class="ui-li-thumb"
                                     src="{{$goodsThumbImageArray[$goodsItem['goods_id']]|default:''}}"/>
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
                    {{/foreach}}

                {{/if}}

            {{/foreach}}

        </ul>
    {{else}}
        <h1>没有商品</h1>
    {{/if}}

{{/block}}