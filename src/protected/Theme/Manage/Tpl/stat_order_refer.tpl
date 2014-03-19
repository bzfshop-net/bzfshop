{{extends file='stat_order_layout.tpl'}}
{{block name=stat_order_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#stat_order_tab_left li:has(a[href='{{bzf_make_url controller='/Stat/Order/Refer' }}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '来源渠道', link: window.location.href});
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

                            <span class="input-label">来源渠道(主)</span>
                            <select id="stat_order_refer_utm_source"
                                    class="span2 select2-simple" name="utm_source" data-placeholder="全部"
                                    data-initValue="{{$utm_source|default}}"
                                    data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Order/ListOrderReferUtmSource"}}"
                                    data-option-value-key="word" data-option-text-key="name">
                                <option value=""></option>
                            </select>
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

                            <span class="input-label">来源渠道(次)</span>
                            <select id="stat_order_refer_utm_medium"
                                    class="span2 select2-simple" name="utm_medium" data-placeholder="全部"
                                    data-initValue="{{$utm_medium|default}}"
                                    data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Order/ListOrderReferUtmMedium"}}"
                                    data-option-value-key="word" data-option-text-key="name">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">用户登陆方式</span>
                            <select id="stat_order_refer_login_type"
                                    class="span2 select2-simple" name="login_type" data-placeholder="全部"
                                    data-initValue="{{$login_type|default}}"
                                    data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Order/ListOrderReferLoginType"}}"
                                    data-option-value-key="word" data-option-text-key="name">
                                <option value=""></option>
                            </select>
                            <span class="input-label">来源系统</span>
                            <select class="span2 select2-simple" name="system_id" data-placeholder="全部"
                                    data-initValue="{{$system_id|default}}"
                                    data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Order/ListOrderSystemId"}}"
                                    data-option-value-key="word" data-option-text-key="name">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <div class="controls">
                            <button type="submit" class="btn btn-success">利润统计</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="stat_order_refer_download_button" type="button" class="btn btn-info">
                                订单下载
                            </button>
                        </div>
                    </div>

                </form>
            </div>
            <!-- /这里是条件筛选区 -->

            <!-- 计算说明 -->
            <table class="table table-bordered align-left">
                <tbody>
                <tr>
                    <td class="well well-small" style="font-weight: bold;text-align: center;">
                        计算说明
                    </td>
                </tr>
                <tr>
                    <td>
                        总支付金额 = 商品金额 + 快递金额 - 商品优惠 - 额外优惠 - 商品退款 - 额外退款 - 余额支付 - 红包支付
                    </td>
                </tr>
                <tr>
                    <td>
                        总进货成本 = 供货货款 + 供货快递 - 供货退款
                    </td>
                </tr>
                <tr>
                    <td>
                        总利润 = 总支付金额 - 总进货成本
                    </td>
                </tr>
                </tbody>
            </table>
            <!-- /计算说明 -->

            {{if isset($utmSourceMediumStatArray)}}

                {{foreach $utmSourceMediumStatArray as $utmSourceMediumStatItem }}

                    {{assign var='totalPayMoney' value= $utmSourceMediumStatItem['goods_price'] +  $utmSourceMediumStatItem['shipping_fee'] - $utmSourceMediumStatItem['discount'] - $utmSourceMediumStatItem['extra_discount'] - $utmSourceMediumStatItem['refund'] - $utmSourceMediumStatItem['extra_refund'] - $utmSourceMediumStatItem['surplus'] - $utmSourceMediumStatItem['bonus']}}

                    {{assign var='totalSupplierMoney' value=$utmSourceMediumStatItem['suppliers_price'] + $utmSourceMediumStatItem['suppliers_shipping_fee'] - $utmSourceMediumStatItem['suppliers_refund']}}

                    <!-- 一个统计结果的显示 -->
                    <table class="table table-bordered align-left">
                        <thead>
                        <th width="20%">&nbsp;</th>
                        <th width="20%">&nbsp;</th>
                        <th width="20%">&nbsp;</th>
                        <th width="20%">&nbsp;</th>
                        <th width="20%">&nbsp;</th>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="5" class="well well-small" style="font-weight: bold;text-align: center;">
                                {{$utmSourceMediumStatItem['utm_source']|bzf_dictionary_name}}
                                --{{$utmSourceMediumStatItem['utm_medium']|bzf_dictionary_name}}
                            </td>
                        </tr>
                        <tr>
                            <td>总利润：{{($totalPayMoney - $totalSupplierMoney)|bzf_money_display}}</td>
                            <td>总支付金额：{{$totalPayMoney|bzf_money_display}}</td>
                            <td>总进货成本：{{$totalSupplierMoney|bzf_money_display}}</td>
                            <td>总CPS结算金额：{{$utmSourceMediumStatItem['cps_amount']|bzf_money_display}}</td>
                            <td>总CPS费用：{{$utmSourceMediumStatItem['cps_fee']|bzf_money_display}}</td>
                        </tr>
                        <tr>
                            <td>商品金额：{{$utmSourceMediumStatItem['goods_price']|bzf_money_display}}</td>
                            <td>快递金额：{{$utmSourceMediumStatItem['shipping_fee']|bzf_money_display}}</td>
                            <td>商品优惠：{{$utmSourceMediumStatItem['discount']|bzf_money_display}}</td>
                            <td>额外优惠：{{$utmSourceMediumStatItem['extra_discount']|bzf_money_display}}</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>

                            <td>商品退款：{{$utmSourceMediumStatItem['refund']|bzf_money_display}}</td>
                            <td>额外退款：{{$utmSourceMediumStatItem['extra_refund']|bzf_money_display}}</td>
                            <td>供货货款：{{$utmSourceMediumStatItem['suppliers_price']|bzf_money_display}}</td>
                            <td>供货快递：{{$utmSourceMediumStatItem['suppliers_shipping_fee']|bzf_money_display}}</td>
                            <td>供货退款：{{$utmSourceMediumStatItem['suppliers_refund']|bzf_money_display}}</td>
                        </tr>
                        <tr>
                            <td>子订单数量：{{$utmSourceMediumStatItem['order_goods_count']}}</td>
                            <td>付款子订单：{{$utmSourceMediumStatItem['order_goods_pay_count']}}</td>
                            <td>退款子订单：{{$utmSourceMediumStatItem['order_goods_refund_count']}}</td>
                            <td>余额支付：{{$utmSourceMediumStatItem['surplus']|bzf_money_display}}</td>
                            <td>红包支付：{{$utmSourceMediumStatItem['bonus']|bzf_money_display}}</td>
                        </tr>
                        </tbody>
                    </table>
                    <!-- /一个统计结果的显示 -->
                {{/foreach}}
            {{/if}}

        </div>
        <!-- /订单来源渠道统计 -->

    </div>
    </div>
    <!-- /左侧每个标签的具体内容 -->

{{/block}}


{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {

            /************************** 订单下载 *****************************/
            $('#stat_order_refer_download_button').on('click', function () {

                var add_time_start = $('#stat_order_refer_add_time_start').val();
                var add_time_end = $('#stat_order_refer_add_time_end').val();
                var pay_time_start = $('#stat_order_refer_pay_time_start').val();
                var pay_time_end = $('#stat_order_refer_pay_time_end').val();
                var utm_source = $('#stat_order_refer_utm_source').find('option:selected').val();
                var utm_medium = $('#stat_order_refer_utm_medium').find('option:selected').val();
                var login_type = $('#stat_order_refer_login_type').find('option:selected').val();

                // 参数检查
                if (!add_time_start && !add_time_end && !pay_time_start && !pay_time_end) {
                    bZF.showMessage('必须提供最少一个查询时间');
                    return;
                }

                // 构造调用链接
                var callUrl = bZF.makeUrl('/Stat/Order/Refer/Download'
                        + '?add_time_start=' + add_time_start
                        + '&add_time_end=' + add_time_end
                        + '&pay_time_start=' + pay_time_start
                        + '&pay_time_end=' + pay_time_end
                        + '&utm_source=' + utm_source
                        + '&utm_medium=' + utm_medium
                        + '&login_type=' + login_type);

                window.open(encodeURI(callUrl));
            });

        })(window, jQuery));
    </script>
{{/block}}
