{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script>
        window.bz_set_nav_status.push(function ($) {
            $("#order_tabbar li:has(a[href='{{bzf_make_url controller='/Order/Settle'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 1, text: '订单结算', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        <h4>订单商品结算（限制最多 {{$max_query_record_count|default}} 条记录）</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline"
                  method="GET" style="margin: 0px 0px 0px 0px;">

                <div class="control-group" style="padding-top:3px; padding-bottom:3px;">
                    <div class="controls">
                        <span class="input-label">付款时间</span>

                        <div class="input-append date datetimepicker">
                            <input id="order_settle_pay_time_start" class="span2"
                                   type="text" name="pay_time_start" value="{{$pay_time_start|default}}"
                                   data-validation-required-message="付款时间必须填写"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                        <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                        <div class="input-append date datetimepicker">
                            <input id="order_settle_pay_time_end" class="span2"
                                   type="text" name="pay_time_end" value="{{$pay_time_end|default}}"
                                   data-validation-required-message="付款时间必须填写"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                        <button type="button" class="btn btn-info" onclick="bZF.order_settle_supplier_select();"
                                style="float:left;margin-left: 5px;margin-right: 5px;">筛选供货商--&gt;</button>
                        <select id="order_settle_supplier_select"
                                class="span3 select2-simple" name="suppliers_id"
                                data-validation-required-message="供货商不能为空"
                                data-placeholder="选择供货商"
                                data-initValue="{{$suppliers_id|default}}"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Supplier/ListOrderGoodsSupplierIdName" pay_time_start={{$pay_time_start|default}}   pay_time_end={{$pay_time_end|default}} supplier_for_settle=true }}"
                                data-option-value-key="suppliers_id" data-option-text-key="suppliers_name">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <div class="control-group" style="padding-top:3px; padding-bottom:3px;">
                    <div class="controls">
                        <button type="submit" class="btn btn-success">列出订单商品</button>
                    </div>
                </div>

            </form>

        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 订单结算提交表单 -->
        <form class="form-horizontal form-horizontal-inline"
              method="POST" style="margin: 0px 0px 0px 0px;">

            <!-- 订单列表 -->
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>&nbsp;</th>
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
                    <th>状态</th>
                    <th width="10%">备注</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>

                {{if isset($orderGoodsArray)}}

                    {{assign var='orderGoodsItemIndex' value=0}}

                    {{foreach $orderGoodsArray as $orderGoodsItem }}

                        <!-- 一个订单 -->
                        {{if 0 == $orderGoodsItem['order_goods_status']}}
                            <tr id="order_settle_item_{{$orderGoodsItemIndex}}" class="info"> <!-- 未付款订单 -->
                                {{elseif 1 == $orderGoodsItem['order_goods_status']}}
                            <tr id="order_settle_item_{{$orderGoodsItemIndex}}"> <!-- 付款订单 -->
                                {{else}}
                            <tr id="order_settle_item_{{$orderGoodsItemIndex}}" class="error"> <!-- 退款订单 -->
                        {{/if}}
                        <td>
                            <input id="order_settle_item_{{$orderGoodsItemIndex}}_checkbox"
                                   onchange="bZF.order_settle_calculate();"
                                   type="checkbox" name="orderGoodsIdArray[]"
                                   value="{{$orderGoodsItem['rec_id']}}"
                                   checked="checked"/>
                        </td>
                        <td>
                            <a href="#" ref="popover" data-trigger="hover" data-placement="right"
                               data-content="下单：{{$orderGoodsItem['create_time']|bzf_localtime}}&nbsp;&nbsp;&nbsp;&nbsp;支付：{{$orderGoodsItem['pay_time']|bzf_localtime}}">
                                {{$orderGoodsItem['rec_id']}}
                            </a>
                        </td>
                        <td>({{$orderGoodsItem['goods_id']}}){{$orderGoodsItem['goods_name']}}<span
                                    style="color:blue;">({{$orderGoodsItem['seller_note']}})</span></td>
                        <td>{{$orderGoodsItem['goods_attr']|default}}</td>
                        <td>{{$orderGoodsItem['goods_number']}}</td>
                        <td>{{$orderGoodsItem['suppliers_price']|bzf_money_display}}</td>
                        <td id="order_settle_item_{{$orderGoodsItemIndex}}_goods_price"
                            class="price">{{($orderGoodsItem['goods_number'] * $orderGoodsItem['suppliers_price'])|bzf_money_display}}</td>
                        <td id="order_settle_item_{{$orderGoodsItemIndex}}_shipping_fee"
                            class="price">{{$orderGoodsItem['suppliers_shipping_fee']|bzf_money_display}}</td>
                        <td>
                            <a id="order_settle_item_{{$orderGoodsItemIndex}}_refund"
                               style="color:blue;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                               data-content="{{$orderGoodsItem['suppliers_refund_note']}}">
                                {{$orderGoodsItem['suppliers_refund']|bzf_money_display}}
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

                        {{assign var='orderGoodsItemIndex' value={{$orderGoodsItemIndex}}+1}}
                    {{/foreach}}
                    <input id="order_settle_item_size" type="hidden" value="{{$orderGoodsItemIndex}}"/>
                    <!-- 最大的索引值 -->
                {{/if}}

                </tbody>
            </table>
            <!-- /订单列表 -->

            <!-- 订单结算总结 -->
            <div class="row well well-small">
                <p style="margin-left:110px;">
                    结算子订单数量：<span id="order_settle_order_goods_count" style="color:red;font-weight: bold;">0</span>，
                    商品货款 <span id="order_settle_total_goods_price" style="color:red;font-weight: bold;">0</span>
                    + 快递费用 <span id="order_settle_total_shipping_fee" style="color:red;font-weight: bold;">0</span>
                    - 退款金额 <span id="order_settle_total_refund" style="color:blue;font-weight: bold;">0</span>
                    = 结算金额 <span id="order_settle_total_settle_money" style="color:red;font-weight: bold;">0</span>
                </p>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">结算备注</span>
                        <textarea class="span5" rows="5" cols="20" name="memo"
                                  data-validation-required-message="结算备注不能为空"
                                  maxlength="250">{{$memo|default}}</textarea>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="hidden" name="payTimeStart" value="{{$payTimeStart|default}}"/>
                        <input type="hidden" name="payTimeEnd" value="{{$payTimeEnd|default}}"/>
                        <input type="hidden" name="suppliers_id" value="{{$suppliers_id|default}}"/>
                        <button type="submit" class="btn btn-danger">提交结算</button>
                    </div>
                </div>

            </div>
            <!-- /订单结算总结 -->

        </form>
        <!-- 订单结算提交表单 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}