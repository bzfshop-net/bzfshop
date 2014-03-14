{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#order_tabbar li:has(a[href='{{bzf_make_url controller='/Order/Goods/Search'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 1, text: '售出商品', link: window.location.href});
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
                    <span class="input-label">订单流水号</span>
                    <input class="span2" type="text" name="order_sn" value="{{$order_sn|default}}"/>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">订单状态</span>
                    <select class="span2 select2-simple" name="order_goods_status" data-placeholder="全部"
                            data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Order/ListOrderGoodsStatus"}}"
                            data-option-value-key="id" data-option-text-key="desc"
                            data-initValue="{{$order_goods_status|default}}">
                        <option value=""></option>
                    </select>

                    <span class="input-label">发货状态</span>
                    <select class="span2 select2-simple" name="shippingStatus" data-placeholder="全部"
                            data-initValue="{{$shippingStatus|default}}">
                        <option value=""></option>
                        <option value="1">未发货</option>
                        <option value="2">已发货</option>
                    </select>

                        <span class="input-label" rel="tooltip" data-placement="top"
                              data-title="第三方系统支付号，比如支付宝、财付通的支付号">第三方支付号</span>
                    <input class="span2" type="text" name="pay_no" value="{{$pay_no|default}}"/>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">商品ID</span>
                    <input class="span2" type="text" pattern="[0-9]*" data-validation-pattern-message="商品ID应该是全数字"
                           name="goods_id" value="{{$goods_id|default}}"/>
                    <span class="input-label">商品名称</span>
                    <input class="span5" type="text" name="goods_name" value="{{$goods_name|default}}"/>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">用户名</span>
                    <input class="span2" type="text" name="user_name" value="{{$user_name|default}}"/>
                    <span class="input-label">邮箱</span>
                    <input class="span2" type="text" name="email" value="{{$email|default}}"/>
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
                    <span class="input-label">客服备注</span>
                    <input class="span5" type="text" name="memo" value="{{$memo|default}}"/>
                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="商品是谁编辑的">商品编辑</span>
                    <select class="span2 select2-simple" name="goods_admin_user_id" data-placeholder="编辑列表"
                            data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/AdminUser/ListUserIdName"}}"
                            data-option-value-key="user_id" data-option-text-key="user_name"
                            data-initValue="{{$goods_admin_user_id|default}}">
                        <option value=""></option>
                    </select>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">下单时间</span>

                    <div class="input-append date datetimepicker">
                        <input class="span2" type="text" name="create_time_start"
                               value="{{$create_time_start|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                    </div>
                    <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                    <div class="input-append date datetimepicker">
                        <input class="span2" type="text" name="create_time_end"
                               value="{{$create_time_end|default}}"/>
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
            <th width="6%">订单号</th>
            <th width="20%">商品名称</th>
            <th>购买用户</th>
            <th width="7%">商品选项</th>
            <th width="5%">购买数量</th>
            <th width="5%">金额</th>
            <th width="5%">优惠</th>
            <th width="5%">快递费</th>
            <th width="5%">退款</th>
            <th width="5%">状态</th>
            <th width="10%">物流快递</th>
            <th width="5%">操作</th>
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
                <td>{{$orderGoodsItem['order_id']}}-{{$orderGoodsItem['rec_id']}}</td>
                <td>
                    <a rel="clickover" data-placement="top" href="#"
                       data-content="{{bzf_goods_view_toolbar goods_id=$orderGoodsItem['goods_id']}}">
                        ({{$orderGoodsItem['goods_id']}}){{$orderGoodsItem['goods_name']}}
                    </a><br/>
                    <span style="color:blue;">
                        编辑[{{$orderGoodsItem['goods_admin_user_name']}}]
                    </span>
                </td>
                <td>
                    <a href="#"
                       onclick="bZF.Account_User_ajaxDetail({{$orderGoodsItem['user_id']}});return false;">{{$orderGoodsItem['user_name']|default}}</a>
                    <br/>{{$orderGoodsItem['email']|default}}
                    {{if $orderGoodsItem['kefu_user_id'] > 0}}
                        <br/>
                        <span style="color:blue;">
                                客服[{{$orderGoodsItem['kefu_user_name']}}] 服务打分[{{$orderGoodsItem['kefu_user_rate']}}]
                            </span>
                    {{/if}}
                </td>
                <td>{{$orderGoodsItem['goods_attr']|default}}</td>
                <td>{{$orderGoodsItem['goods_number']}}</td>
                <td>
                    <a style="color:red;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                       data-content="{{$orderGoodsItem['goods_price_note']}}">
                        {{$orderGoodsItem['goods_price']|bzf_money_display}}
                    </a>
                </td>
                <td>
                    <a style="color:blue;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                       data-content="{{$orderGoodsItem['discount_note']}}">
                        {{$orderGoodsItem['discount']|bzf_money_display}}
                    </a>
                </td>
                <td>
                    <a style="color:red;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                       data-content="{{$orderGoodsItem['shipping_fee_note']}}">
                        {{$orderGoodsItem['shipping_fee']|bzf_money_display}}
                    </a>
                </td>
                <td>
                    <a style="color:blue;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                       data-content="我们给顾客退款：{{$orderGoodsItem['refund_note']}}">
                        {{$orderGoodsItem['refund']|bzf_money_display}}
                    </a><br/>
                    <a style="color:green;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                       data-content="供货商给我们退款：{{$orderGoodsItem['suppliers_refund_note']}}">
                        {{$orderGoodsItem['suppliers_refund']|bzf_money_display}}
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
                    <a class="btn btn-small"
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

    <!-- 结算记录设置 modal -->
    <div id="order_settle_listsettle_modal_update" class="modal hide fade" tabindex="-1" role="dialog"
         aria-hidden="true">
    </div>
    <!-- /结算记录设置 modal -->

    <!-- 用户详情对话框 -->
    <div id="user_detail_dialog" class="modal hide fade">
    </div>
    <!-- 用户详情对话框 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}