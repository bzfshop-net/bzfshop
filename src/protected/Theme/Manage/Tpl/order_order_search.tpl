{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#order_tabbar li:has(a[href='{{bzf_make_url controller='/Order/Order/Search'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 1, text: '订单列表', link: window.location.href});
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
                        <span class="input-label">订单流水号</span>
                        <input class="span2" type="text" name="order_sn" value="{{$order_sn|default}}"/>
                        <span class="input-label" rel="tooltip" data-placement="top"
                              data-title="第三方系统支付号，比如支付宝、财付通的支付号">第三方支付号</span>
                        <input class="span2" type="text" name="pay_no" value="{{$pay_no|default}}"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">收货人</span>
                        <input class="span2" type="text" name="consignee" value="{{$consignee|default}}"/>
                        <span class="input-label">手机号</span>
                        <input class="span2" type="text" pattern="[0-9]+"
                               data-validation-pattern-message="手机号码格式不正确"
                               name="mobile" value="{{$mobile|default}}"/>
                        <span class="input-label">收货地址</span>
                        <input class="span2" type="text" name="address" value="{{$address|default}}"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">订单附言</span>
                        <input class="span5" type="text" name="postscript" value="{{$postscript|default}}"/>
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
                        <span class="input-label" rel="tooltip" data-placement="top"
                              data-title="哪位客服为顾客服务的">服务客服</span>
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

                        <span class="input-label">来源渠道(主)</span>
                        <select class="span2 select2-simple" name="utm_source" data-placeholder="全部"
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

                        <span class="input-label">来源渠道(次)</span>
                        <select class="span2 select2-simple" name="utm_medium" data-placeholder="全部"
                                data-initValue="{{$utm_medium|default}}"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Order/ListOrderReferUtmMedium"}}"
                                data-option-value-key="word" data-option-text-key="name">
                            <option value=""></option>
                        </select>
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
                <th>订单ID</th>
                <th width="16%">订单流水号</th>
                <th width="15%">下单时间</th>
                <th width="15%">付款时间</th>
                <th>购买用户</th>
                <th>订单金额</th>
                <th width="6%">订单状态</th>
                <th width="5%">操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($orderInfoArray)}}
                {{foreach $orderInfoArray as $orderInfoItem}}
                    {{if 2 == $orderInfoItem['order_status'] || 3 == $orderInfoItem['order_status']}}
                        <tr class="warning"> <!-- 已取消或者无效的订单 -->
                            {{elseif  0 == $orderInfoItem['pay_status']}}
                        <tr class="info"> <!-- 未付款订单 -->
                            {{else}}
                        <tr> <!-- 其它订单 -->
                    {{/if}}
                    <td>{{$orderInfoItem['order_id']}}</td>
                    <td>{{$orderInfoItem['order_sn']}}</td>
                    <td>{{$orderInfoItem['add_time']|bzf_localtime}}</td>
                    <td>{{$orderInfoItem['confirm_time']|bzf_localtime}}</td>
                    <td>
                        <a href="#"
                           onclick="bZF.Account_User_ajaxDetail({{$orderInfoItem['user_id']}});return false;">{{$orderInfoItem['user_name']|default}}</a>
                        <br/>{{$orderInfoItem['email']|default}}
                        {{if $orderInfoItem['kefu_user_id'] > 0}}
                            <br/>
                            <span style="color:blue;">
                                客服[{{$orderInfoItem['kefu_user_name']}}] 服务打分[{{$orderInfoItem['kefu_user_rate']}}]
                            </span>
                        {{/if}}
                    </td>
                    <td class="price">{{($orderInfoItem['goods_amount'] + $orderInfoItem['shipping_fee'] - $orderInfoItem['discount'])|bzf_money_display}}</td>
                    <td>
                        <label class="label label-success">{{$orderInfoItem['system_id']|bzf_system_name}}</label><br/>
                        {{$orderInfoItem['order_status_desc']}}，{{$orderInfoItem['pay_status_desc']}}
                    </td>
                    <td>
                        <a class="btn btn-small"
                           href="{{bzf_make_url controller="/Order/Order/Detail" order_id=$orderInfoItem['order_id'] }}">详情</a>
                    </td>
                    </tr>
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

    <!-- 用户详情对话框 -->
    <div id="user_detail_dialog" class="modal hide fade">
    </div>
    <!-- 用户详情对话框 -->

{{/block}}