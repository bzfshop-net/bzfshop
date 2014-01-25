{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#order_tabbar li:has(a[href='{{bzf_make_url controller='/Order/Settle/ListSettle'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 0, text: '结算历史', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        <h4>订单商品结算历史</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline"
                  method="GET" style="margin: 0px 0px 0px 0px;">

                <div class="control-group" style="padding-top:3px; padding-bottom:3px;">
                    <div class="controls">
                        <span class="input-label">结算时间</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2"
                                   type="text" name="settle_time_start" value="{{$settle_time_start|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                        <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2"
                                   type="text" name="settle_time_end" value="{{$settle_time_end|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>

                        <span class="input-label">付款状态</span>
                        <select class="span2 select2-simple" name="is_pay"
                                data-placeholder="选择付款状态"
                                data-initValue="{{$is_pay|default}}">
                            <option value=""></option>
                            <option value="1">没有付款</option>
                            <option value="2">已经付款</option>
                        </select>
                    </div>
                </div>

                <div class="control-group" style="padding-top:3px; padding-bottom:3px;">
                    <div class="controls">
                        <button type="submit" class="btn btn-success">查询</button>
                    </div>
                </div>

            </form>

        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 结算记录列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>结算ID</th>
                <th width="8%">订单开始</th>
                <th width="8%">订单结束</th>
                <th width="8%">结算时间</th>
                <th width="15%">供货商</th>
                <th>商品金额</th>
                <th>快递金额</th>
                <th>退款金额</th>
                <th>结算金额</th>
                <th>结算人</th>
                <th width="15%">备注</th>
                <th width="5%">操作</th>
            </tr>
            </thead>
            <tbody>

            {{if isset($orderSettleArray)}}
                {{foreach $orderSettleArray as $orderSettleItem }}

                    <!-- 一个结算记录 -->
                    <tr>
                        <td>{{$orderSettleItem['settle_id']}}</td>
                        <td>{{$orderSettleItem['settle_start_time']|bzf_localtime}}</td>
                        <td>{{$orderSettleItem['settle_end_time']|bzf_localtime}}</td>
                        <td>{{$orderSettleItem['create_time']|bzf_localtime}}</td>
                        <td>({{$orderSettleItem['suppliers_id']}}){{$orderSettleItem['suppliers_name']}}</td>
                        <td class="price">{{$orderSettleItem['suppliers_goods_price']|bzf_money_display}}</td>
                        <td class="price">{{$orderSettleItem['suppliers_shipping_fee']|bzf_money_display}}</td>
                        <td class="discount">{{$orderSettleItem['suppliers_refund']|bzf_money_display}}</td>
                        <td class="price">{{($orderSettleItem['suppliers_goods_price'] + $orderSettleItem['suppliers_shipping_fee'] - $orderSettleItem['suppliers_refund'])|bzf_money_display}}</td>
                        <td>
                            ({{$orderSettleItem['user_id']}}){{$orderSettleItem['user_name']}}
                            {{if $orderSettleItem['pay_time'] > 0}}
                                <br/>
                                <span style="color:blue;">[已付款]</span>
                            {{/if}}
                        </td>
                        <td>{{$orderSettleItem['memo']|nl2br nofilter}}</td>
                        <td>
                            <button type="button" class="btn btn-small"
                                    onclick="bZF.Order_Settle_ajaxDetail({{$orderSettleItem['settle_id']}});">
                                详情
                            </button>
                            <a class="btn btn-small"
                               href="{{bzf_make_url controller='/Order/Settle/ListOrderGoods' settle_id=$orderSettleItem['settle_id']}}">明细</a>
                        </td>
                    </tr>
                    <!-- /一个结算记录 -->

                {{/foreach}}
            {{/if}}

            </tbody>
        </table>
        <!-- /结算记录列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
        </div>
        <!-- 分页 -->

        <!-- 结算记录设置 modal -->
        <div id="order_settle_listsettle_modal_detail" class="modal hide fade" tabindex="-1" role="dialog"
             aria-hidden="true">
        </div>
        <!-- /结算记录设置 modal -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {
            /******* 订单结算页面，结算详情 ***********/
            bZF.Order_Settle_ajaxDetail = function (settle_id) {
                var ajaxCallUrl = bZF.makeUrl('/Order/Settle/ajaxDetail');
                $('#order_settle_listsettle_modal_detail').load(ajaxCallUrl + '?settle_id=' + settle_id, function () {
                    $('#order_settle_listsettle_modal_detail').modal({dynamic: true});
                });
            };

        })(window, jQuery));
    </script>
{{/block}}