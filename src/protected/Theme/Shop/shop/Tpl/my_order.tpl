{{extends file='my_layout.tpl'}}
{{block name=main_body_my}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bzf_set_nav_status.push(function ($) {
            $("#my_nav_tabbar li:has(a[href='{{bzf_make_url controller='/My/Order'}}'])").addClass("active");
        });
    </script>
    <!-- 页面主体内容 -->
    <div id="my_order_list_panel" class="row">

        <h4>{{$myOrderTitle|default}}</h4>

        <!-- 我的订单列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th width="20%">订单流水号</th>
                <th width="20%">下单时间</th>
                <th width="20%">付款时间</th>
                <th>订单金额</th>
                <th>订单状态</th>
                <th width="10%">操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($orderInfoArray)}}
                {{foreach $orderInfoArray as $orderInfoItem}}
                    <tr>
                        <td>{{$orderInfoItem['order_sn']}}</td>
                        <td>{{$orderInfoItem['add_time']|bzf_localtime}}</td>
                        <td>{{$orderInfoItem['confirm_time']|bzf_localtime}}</td>
                        <td class="price">{{($orderInfoItem['goods_amount'] + $orderInfoItem['shipping_fee'] - $orderInfoItem['discount'] - $orderInfoItem['extra_discount'])|bzf_money_display}}</td>
                        <td>{{$orderInfoItem['pay_status_desc']}},{{$orderInfoItem['order_status_desc']}}</td>
                        <td>
                            <a class="btn btn-small"
                               href="{{bzf_make_url controller="/My/Order/Detail" order_id=$orderInfoItem['order_id'] }}">订单详情</a>
                            {{if 0 == {{$orderInfoItem['order_status']}}}}
                            <a class="btn btn-small btn-success"
                               href="{{bzf_make_url controller="/My/Order/Cart" order_id=$orderInfoItem['order_id'] }}">现在付款</a>
                            <a class="btn btn-small btn-danger"
                               href="{{bzf_make_url controller="/My/Order/Cancel" order_id=$orderInfoItem['order_id'] }}">取消订单</a>
                        </td>
                        {{/if}}
                    </tr>
                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /我的订单列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->


{{/block}}