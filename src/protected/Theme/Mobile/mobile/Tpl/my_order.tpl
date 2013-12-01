{{extends file='page_layout.tpl'}}
{{block name=main_page_content}}
    <table data-role="table" id="my_order_table" data-mode="columntoggle"
           class="ui-body-d ui-shadow table-stripe ui-responsive" data-column-btn-theme="b"
           data-column-btn-text="选择显示列" data-column-popup-theme="a">
        <thead>
        <tr class="ui-bar-f">
            <th data-priority="6">订单流水号</th>
            <th>商品</th>
            <th data-priority="2">选项</th>
            <th data-priority="3">数量</th>
            <th>金额</th>
            <th>邮费</th>
            <th data-priority="1" style="min-width: 50px;">状态</th>
            <th data-priority="4">下单时间</th>
            <th data-priority="5">付款时间</th>
        </tr>
        </thead>
        <tbody>

        {{if isset($orderGoodsArray)}}
            {{foreach $orderGoodsArray as $orderGoodsItem}}
                <tr>
                    <td>{{$orderGoodsItem['order_sn']}}</td>
                    <td>
                        <a href="{{bzf_make_url controller='/Goods/View' goods_id=$orderGoodsItem['goods_id']}}">
                            <img class="lazyload goods_thumbnail_image"
                                 src="{{bzf_get_asset_url asset='img/lazyload_placeholder_298_223.png'}}"
                                 data-original="{{bzf_goods_thumb_image goods_id=$orderGoodsItem['goods_id']}}"/>
                            <noscript>
                                <img src="{{bzf_goods_thumb_image goods_id=$orderGoodsItem['goods_id']}}"/>>
                            </noscript>
                        </a>
                    </td>
                    <td>{{$orderGoodsItem['goods_attr']}}</td>
                    <td>{{$orderGoodsItem['goods_number']}}</td>
                    <td style="color:red;">{{$orderGoodsItem['goods_price']|bzf_money_display}}</td>
                    <td style="color:red;">{{$orderGoodsItem['shipping_fee']|bzf_money_display}}</td>
                    <td>
                        {{if 0 == $orderGoodsItem['order_goods_status']}}
                            <a data-role="button" data-mini="true"
                               href="{{bzf_make_url controller='/Cart/Pay' order_id=$orderGoodsItem['order_id']}}"
                               data-theme="f">去付款</a>
                        {{else}}
                            {{$orderGoodsItem['order_goods_status_desc']}}
                        {{/if}}
                    </td>
                    <td>{{$orderGoodsItem['create_time']|bzf_localtime}}</td>
                    <td>{{$orderGoodsItem['pay_time']|bzf_localtime}}</td>
                </tr>
            {{/foreach}}
        {{/if}}

        </tbody>
    </table>
    <br/>
    <!-- 分页 -->
    {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
    <!-- /分页 -->

{{/block}}