{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#order_tabbar li:has(a[href='{{bzf_make_url controller='/Order/Settle/ListSettle'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 2, text: '结算明细', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        <h4>订单商品结算明细</h4>

        <!-- 订单列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>子订单号</th>
                <th width="20%">商品名称</th>
                <th>选项</th>
                <th>数量</th>
                <th>
                    <span rel="tooltip" data-placement="top"
                          data-title="商品的供货单价">单价</span>
                </th>
                <th>
                    <span rel="tooltip" data-placement="top"
                          data-title="商品的供货总价">总价</span>
                </th>
                <th>
                    <span rel="tooltip" data-placement="top"
                          data-title="商品的供货快递">快递</span>
                </th>
                <th>
                    <span rel="tooltip" data-placement="top"
                          data-title="供货商给我们的退款">退款</span>
                </th>
                <th>
                    <span rel="tooltip" data-placement="top"
                          data-title="结算后又出现的退款">额外退款</span>
                </th>
                <th>状态</th>
                <th width="10%">备注</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>

            {{if isset($orderGoodsArray)}}

                {{foreach $orderGoodsArray as $orderGoodsItem }}

                    <!-- 一个订单 -->
                    {{if 0 == $orderGoodsItem['order_goods_status']}}
                        <tr class="info"> <!-- 未付款订单 -->
                            {{elseif 1 == $orderGoodsItem['order_goods_status']}}
                        <tr> <!-- 付款订单 -->
                            {{else}}
                        <tr class="error"> <!-- 退款订单 -->
                    {{/if}}
                    <td>
                        <a href="#" ref="popover" data-trigger="hover" data-placement="right"
                           data-content="下单：{{$orderGoodsItem['create_time']|bzf_localtime}}&nbsp;&nbsp;&nbsp;&nbsp;支付：{{$orderGoodsItem['pay_time']|bzf_localtime}}">
                            {{$orderGoodsItem['rec_id']}}
                        </a>
                    </td>
                    <td>({{$orderGoodsItem['goods_id']}}){{$orderGoodsItem['goods_name']}}</td>
                    <td>{{$orderGoodsItem['goods_attr']|default}}</td>
                    <td>{{$orderGoodsItem['goods_number']}}</td>
                    <td>{{$orderGoodsItem['suppliers_price']|bzf_money_display}}</td>
                    <td class="price">{{($orderGoodsItem['goods_number'] * $orderGoodsItem['suppliers_price'])|bzf_money_display}}</td>
                    <td class="price">{{$orderGoodsItem['suppliers_shipping_fee']|bzf_money_display}}</td>
                    <td>
                        <a style="color:blue;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                           data-content="{{$orderGoodsItem['suppliers_refund_note']}}">
                            {{$orderGoodsItem['suppliers_refund']|bzf_money_display}}
                        </a>
                    </td>
                    <td>
                        <a style="color:blue;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                           data-content="{{$orderGoodsItem['extra_refund_note']}}">
                            {{$orderGoodsItem['extra_refund']|bzf_money_display}}
                        </a>
                    </td>
                    <td>{{$orderGoodsItem['order_goods_status_desc']}}</td>
                    <td>{{$orderGoodsItem['memo']}}</td>
                    <td>
                        <a class="btn btn-small" target="_blank"
                           href="{{bzf_make_url controller='/Order/Goods/Detail' rec_id=$orderGoodsItem['rec_id']}}">详情</a>
                    </td>
                    </tr>
                    <!-- /一个订单 -->
                {{/foreach}}
            {{/if}}

            </tbody>
        </table>
        <!-- /订单列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}