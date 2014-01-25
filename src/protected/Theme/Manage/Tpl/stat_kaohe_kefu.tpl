{{extends file='stat_kaohe_layout.tpl'}}
{{block name=stat_kaohe_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#stat_kaohe_tab_left li:has(a[href='{{bzf_make_url controller='/Stat/Kaohe/Kefu' }}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '客服业绩', link: window.location.href});
    </script>
    <!-- 左侧每个标签的具体内容 -->
    <div class="tab-content">
        <div class="tab-pane active">

            <!-- 订单来源渠道统计 -->
            <div class="row">

                <!-- 这里是条件筛选区 -->
                <div class="row well well-small">
                    <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">

                        <div class="control-group">
                            <div class="controls">
                                <span class="input-label">下单时间</span>

                                <div class="input-append date datetimepicker">
                                    <input id="stat_order_refer_add_time_start"
                                           class="span2" type="text" name="add_time_start"
                                           value="{{$add_time_start|default}}"/>
                                    <span class="add-on">
                                        <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                                    </span>
                                </div>
                                <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                                <div class="input-append date datetimepicker">
                                    <input id="stat_order_refer_add_time_end"
                                           class="span2" type="text" name="add_time_end"
                                           value="{{$add_time_end|default}}"/>
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
                                    <input id="stat_order_refer_pay_time_start"
                                           class="span2" type="text" name="pay_time_start"
                                           value="{{$pay_time_start|default}}"/>
                                    <span class="add-on">
                                        <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                                    </span>
                                </div>
                                <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                                <div class="input-append date datetimepicker">
                                    <input id="stat_order_refer_pay_time_end"
                                           class="span2" type="text" name="pay_time_end"
                                           value="{{$pay_time_end|default}}"/>
                                    <span class="add-on">
                                        <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                                    </span>
                                </div>

                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls">
                                <span class="input-label">选择客服</span>
                                <select class="span2 select2-simple" name="kefu_user_id" data-placeholder="客服列表"
                                        data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/AdminUser/ListKefuIdName"}}"
                                        data-option-value-key="user_id" data-option-text-key="user_name"
                                        data-initValue="{{$kefu_user_id|default}}">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls">
                                <button type="submit" class="btn btn-success">业绩统计</button>
                            </div>
                        </div>

                    </form>
                </div>
                <!-- /这里是条件筛选区 -->

                <!-- 显示不同订单状态 -->
                {{if isset($orderGoodsStat)}}
                    {{foreach $orderGoodsStat as $orderGoodsStatItem}}
                        <!-- 一个统计结果的显示 -->
                        <table class="table table-bordered align-left">
                            <tbody>
                            <tr>
                                <td colspan="4" class="well well-small" style="font-weight: bold;text-align: center;">
                                    {{$orderGoodsStatItem['order_goods_status_desc']}}
                                </td>
                            </tr>
                            <tr>
                                <td width="25%">子订单数量：{{$orderGoodsStatItem['total_order_goods_count']}}</td>
                                <td width="25%">
                                    <a class="btn btn-small btn-info" target="_blank"
                                       href="{{bzf_make_url controller='/Order/Goods/Search' add_time_start=$add_time_start add_time_end=$add_time_end pay_time_start=$pay_time_start pay_time_end=$pay_time_end kefu_user_id=$kefu_user_id order_goods_status=$orderGoodsStatItem['order_goods_status']}}">子订单明细</a>
                                </td>
                                <td width="25%">商品货款：{{$orderGoodsStatItem['total_goods_price']|bzf_money_display}}</td>
                                <td width="25%">
                                    快递金额：{{$orderGoodsStatItem['total_shipping_fee']|bzf_money_display}}</td>
                            </tr>
                            <tr>
                                <td>商品优惠：{{$orderGoodsStatItem['total_discount']|bzf_money_display}}</td>
                                <td>
                                    额外优惠：{{$orderGoodsStatItem['total_extra_discount']|bzf_money_display}}</td>
                                <td>订单退款：{{$orderGoodsStatItem['total_refund']|bzf_money_display}}</td>
                                <td>额外退款：{{$orderGoodsStatItem['total_extra_refund']|bzf_money_display}}</td>
                            </tr>
                            </tbody>
                        </table>
                    {{/foreach}}
                {{/if}}
                <!-- /显示不同订单状态 -->


                <!-- 显示总计订单金额 -->
                {{if isset($orderGoodsStatTotal)}}
                    <table class="table table-bordered align-left">
                        <tbody>
                        <tr>
                            <td colspan="4" class="well well-small" style="font-weight: bold;text-align: center;">
                                总计结果
                            </td>
                        </tr>
                        <tr>
                            <td width="25%">有效业绩子订单：{{$orderGoodsStatTotal['total_order_goods_count']}}</td>
                            <td width="25%">&nbsp;</td>
                            <td width="25%">商品货款：{{$orderGoodsStatTotal['total_goods_price']|bzf_money_display}}</td>
                            <td width="25%">
                                快递金额：{{$orderGoodsStatTotal['total_shipping_fee']|bzf_money_display}}</td>
                        </tr>
                        <tr>
                            <td>商品优惠：{{$orderGoodsStatTotal['total_discount']|bzf_money_display}}</td>
                            <td>
                                额外优惠：{{$orderGoodsStatTotal['total_extra_discount']|bzf_money_display}}</td>
                            <td>订单退款：{{$orderGoodsStatTotal['total_refund']|bzf_money_display}}</td>
                            <td>额外退款：{{$orderGoodsStatTotal['total_extra_refund']|bzf_money_display}}</td>
                        </tr>
                        <tr>
                            <td colspan="4" style="font-weight: bold;text-align: center;">
                                有效业绩金额：{{$orderGoodsStatTotal['total_goods_price']|bzf_money_display}}
                                + {{$orderGoodsStatTotal['total_shipping_fee']|bzf_money_display}}
                                - {{$orderGoodsStatTotal['total_discount']|bzf_money_display}}
                                - {{$orderGoodsStatTotal['total_extra_discount']|bzf_money_display}}
                                - {{$orderGoodsStatTotal['total_refund']|bzf_money_display}}
                                - {{$orderGoodsStatTotal['total_extra_refund']|bzf_money_display}}
                                = {{($orderGoodsStatTotal['total_goods_price'] + $orderGoodsStatTotal['total_shipping_fee'] - $orderGoodsStatTotal['total_discount'] - $orderGoodsStatTotal['total_extra_discount'] - $orderGoodsStatTotal['total_refund'] - $orderGoodsStatTotal['total_extra_refund'])|bzf_money_display}}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                {{/if}}
                <!-- /显示总计订单金额 -->

            </div>
            <!-- /订单来源渠道统计 -->

        </div>
    </div>
    <!-- /左侧每个标签的具体内容 -->

{{/block}}
