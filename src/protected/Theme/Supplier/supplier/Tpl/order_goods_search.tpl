{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#order_tabbar li:has(a[href='{{bzf_make_url controller='/Order/Goods/Search'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 0, text: '售出商品', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

    <!-- 这里是条件筛选区 -->
    <div class="row well well-small">
        <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">订单ID</span>
                    <input class="span2" type="text" pattern="[0-9]*" data-validation-pattern-message="订单ID应该是全数字"
                           name="order_id" value="{{$order_id|default}}"/>
                    <span class="input-label">订单子ID</span>
                    <input class="span2" type="text" pattern="[0-9]*" data-validation-pattern-message="订单子ID应该是全数字"
                           name="rec_id" value="{{$rec_id|default}}"/>
                    <span class="input-label">商品ID</span>
                    <input class="span2" type="text" pattern="[0-9]*" data-validation-pattern-message="商品ID应该是全数字"
                           name="goods_id" value="{{$goods_id|default}}"/>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">收货人</span>
                    <input class="span2" type="text" name="consignee" value="{{$consignee|default}}"/>
                    <span class="input-label">手机号</span>
                    <input class="span2" type="text" pattern="1([0-9]{10})"
                           data-validation-pattern-message="手机号码格式不正确"
                           name="mobile" value="{{$mobile|default}}"/>
                    <span class="input-label">收货地址</span>
                    <input class="span2" type="text" name="address" value="{{$address|default}}"/>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">商品货号</span>
                    <input class="span2" type="text" name="goods_sn" value="{{$goods_sn|default}}"/>

                    <span class="input-label">订单状态</span>
                    <select class="span2 select2-simple" name="order_goods_status" data-placeholder="全部"
                            data-initValue="{{$order_goods_status|default}}">
                        <option value=""></option>
                        <option value="1">已付款</option>
                        <option value="2">申请退款</option>
                        <option value="3">退款中</option>
                        <option value="4">退款完成</option>
                    </select>

                    <span class="input-label">发货状态</span>
                    <select class="span2 select2-simple" name="shippingStatus" data-placeholder="全部"
                            data-initValue="{{$shippingStatus|default}}">
                        <option value=""></option>
                        <option value="1">未发货</option>
                        <option value="2">已发货</option>
                    </select>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">用户留言</span>
                    <input class="span6" type="text" name="postscript" value="{{$postscript|default}}"/>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">客服备注</span>
                    <input class="span6" type="text" name="memo" value="{{$memo|default}}"/>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">下单时间</span>

                    <div class="input-append date datetimepicker">
                        <input class="span2" type="text" name="add_time_start" value="{{$add_time_start|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                    </div>
                    <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                    <div class="input-append date datetimepicker">
                        <input class="span2" type="text" name="add_time_end" value="{{$add_time_end|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">付款时间</span>

                    <div class="input-append date datetimepicker">
                        <input class="span2" type="text" name="pay_time_start" value="{{$pay_time_start|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                    </div>
                    <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                    <div class="input-append date datetimepicker">
                        <input class="span2" type="text" name="pay_time_end" value="{{$pay_time_end|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn btn-success">查询</button>
                </div>
            </div>

        </form>
    </div>
    <!-- /这里是条件筛选区 -->

    <!-- 订单列表 -->
    <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th width="7%">订单号</th>
            <th width="20%">商品名称</th>
            <th width="7%">付款时间</th>
            <th width="7%">选项</th>
            <th>数量</th>
            <th>商品金额</th>
            <th>快递费</th>
            <th>退款</th>
            <th>额外退款</th>
            <th width="7%">订单状态</th>
            <th width="10%">物流快递</th>
            <th width="5%">操作</th>
        </tr>
        </thead>
        <tbody>

        {{if isset($orderGoodsArray)}}
            {{foreach $orderGoodsArray as $orderGoodsItem }}

                <!-- 一个订单 -->
                {{if 1 == $orderGoodsItem['order_goods_status'] }}
                    <tr> <!-- 付款订单 -->
                        {{elseif 2 == $orderGoodsItem['order_goods_status'] }}
                    <tr class="info"> <!-- 申请退款 订单 -->
                        {{else}}
                    <tr class="error"> <!-- 退款中，退款完成 订单 -->
                {{/if}}
                <td>{{$orderGoodsItem['order_id']}}-{{$orderGoodsItem['rec_id']}}</td>
                <td>
                    <a rel="clickover" data-placement="top" href="#"
                       data-content="{{bzf_goods_view_toolbar goods_id=$orderGoodsItem['goods_id'] system_tag_list=$orderGoodsItem['system_id']}}">
                        ({{$orderGoodsItem['goods_id']}}){{$orderGoodsItem['goods_name']}}
                    </a>
                </td>
                <td>{{$orderGoodsItem['pay_time']|bzf_localtime}}</td>
                <td>{{$orderGoodsItem['goods_attr']}}</td>
                <td>{{$orderGoodsItem['goods_number']}}</td>
                <td class="price">{{($orderGoodsItem['goods_number']*$orderGoodsItem['suppliers_price'])|bzf_money_display}}</td>
                <td>
                    <a style="color:red;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                       data-content="{{$orderGoodsItem['suppliers_shipping_fee_note']}}">
                        {{$orderGoodsItem['suppliers_shipping_fee']|bzf_money_display}}
                    </a>
                </td>
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
                <td>
                    <label class="label label-success">{{$orderGoodsItem['system_id']|bzf_system_name}}</label><br/>
                    {{$orderGoodsItem['order_goods_status_desc']}}
                    {{if $orderGoodsItem['settle_id'] > 0}}
                        <br/>
                        <a href="#"
                           onclick="bZF.Order_Settle_ajaxDetail({{$orderGoodsItem['settle_id']}});return false;">[已结算]</a>
                    {{/if}}
                </td>
                <td>
                    {{if $orderGoodsItem['shipping_id'] > 0}}
                        {{$orderGoodsItem['shipping_name']}}
                        <br/>
                        {{$orderGoodsItem['shipping_no']}}
                        <br/>
                        <a target="_blank" class="btn btn-small"
                           href="{{bzf_express_query_url expressName=$orderGoodsItem['shipping_name'] expressNo=$orderGoodsItem['shipping_no']}}">查询快递</a>
                    {{else}}
                        未发货
                    {{/if}}
                </td>
                <td>
                    <button type="button" class="btn btn-small"
                            onclick="bZF.Order_ListOrder_Detail({{$orderGoodsItem['rec_id']}})">详情
                    </button>
                </td>
                </tr><!-- /一个订单 -->

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

    <!-- 订单详情对话框 -->
    <div id="order_detail_dialog" class="modal hide fade">
    </div>
    <!-- 订单详情对话框 -->

    <!-- 结算记录设置 modal -->
    <div id="order_settle_listsettle_modal_detail" class="modal hide fade" tabindex="-1" role="dialog"
         aria-hidden="true">
    </div>
    <!-- /结算记录设置 modal -->

{{/block}}
