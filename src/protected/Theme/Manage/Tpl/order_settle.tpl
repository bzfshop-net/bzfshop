{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
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

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {

            /**
             * order_settle.tpl
             *
             * 订单结算页面，根据用户选择的结算时间段取得这个时间段里面有销售的供货商
             */
            bZF.order_settle_supplier_select = function () {
                var pay_time_start = $('#order_settle_pay_time_start').val();
                if (!pay_time_start) {
                    bZF.showMessage('付款开始时间不能为空');
                    return;
                }

                var pay_time_end = $('#order_settle_pay_time_end').val();
                if (!pay_time_end) {
                    bZF.showMessage('付款结束时间不能为空');
                    return;
                }

                var callUrl = bZF.makeUrl('/Ajax/Supplier/ListOrderGoodsSupplierIdName?pay_time_start=' +
                        encodeURI(pay_time_start) + '&pay_time_end=' + encodeURI(pay_time_end) + '&supplier_for_settle=true');

                // ajax  调用
                bZF.ajaxCallGet(callUrl, function (data) {
                    if (!data) {
                        bZF.showMessage('没有需要结算的供货商');
                        return;
                    }

                    supplierArray = data;
                    // 设置 360tuan_cateogry_1 的数据
                    var optionHtml = '<option value=""></option>';
                    $.each(supplierArray, function (index, elem) {
                        optionHtml += '<option value="' + elem.suppliers_id + '">' + elem.suppliers_name + '</option>';
                    });
                    $('#order_settle_supplier_select').html(optionHtml);
                    //重新设置一次初始值
                    $('#order_settle_supplier_select').select2('val', null);
                    bZF.showMessage('取供货商列表成功');
                });
            };

            /**
             *  order_settle.tpl
             *
             * 订单结算页面，用于计算结算金额
             */
            bZF.order_settle_calculate = function () {

                var orderSettleItemSize = $('#order_settle_item_size').val();
                if (!orderSettleItemSize || orderSettleItemSize <= 0) {
                    return; // 没有需要结算的订单
                }

                var totalItemSelectCount = 0;
                var totalGoodsPrice = 0;
                var totalShippingFee = 0;
                var totalRefund = 0;

                // 遍历所有选择的订单
                for (var orderSettleItemIndex = 0; orderSettleItemIndex < orderSettleItemSize; orderSettleItemIndex++) {
                    if (!$('#order_settle_item_' + orderSettleItemIndex + '_checkbox').attr('checked')) {
                        continue; // 没有选 checkbox，不需要计算
                    }

                    // 计算
                    totalItemSelectCount++;
                    totalGoodsPrice += parseFloat($('#order_settle_item_' + orderSettleItemIndex + '_goods_price').text());
                    totalShippingFee += parseFloat($('#order_settle_item_' + orderSettleItemIndex + '_shipping_fee').text());
                    totalRefund += parseFloat($('#order_settle_item_' + orderSettleItemIndex + '_refund').text());
                }

                // 显示计算的结果
                $('#order_settle_order_goods_count').text(totalItemSelectCount);
                $('#order_settle_total_goods_price').text(totalGoodsPrice.toFixed(2));
                $('#order_settle_total_shipping_fee').text(totalShippingFee.toFixed(2));
                $('#order_settle_total_refund').text(totalRefund.toFixed(2));
                $('#order_settle_total_settle_money').text((totalGoodsPrice + totalShippingFee - totalRefund).toFixed(2));
            };
            //页面启动加载的时候执行一次
            bZF.order_settle_calculate();

        })(window, jQuery));
    </script>
{{/block}}