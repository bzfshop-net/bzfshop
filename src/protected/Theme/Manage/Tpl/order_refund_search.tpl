{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#order_tabbar li:has(a[href='{{bzf_make_url controller='/Order/Refund/Search'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 1, text: '商品退款', link: window.location.href});
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
                    <select class="span2 select2-simple" name="order_goods_status"
                            data-placeholder="选择状态"
                            data-initValue="{{$order_goods_status|default}}">
                        <option value=""></option>
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

                        <span class="input-label" rel="tooltip" data-placement="top"
                              data-title="第三方系统支付号，比如支付宝、财付通的支付号">第三方支付号</span>
                    <input class="span2" type="text" name="pay_no" value="{{$pay_no|default}}"/>
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
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">客服备注</span>
                    <input class="span5" type="text" name="memo" value="{{$memo|default}}"/>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">退款时间</span>

                    <div class="input-append date datetimepicker">
                        <input class="span2" type="text" name="refund_time_start"
                               value="{{$refund_time_start|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                    </div>
                    <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                    <div class="input-append date datetimepicker">
                        <input class="span2" type="text" name="refund_time_end"
                               value="{{$refund_time_end|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                    </div>

                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <span class="input-label">完成时间</span>

                    <div class="input-append date datetimepicker">
                        <input class="span2" type="text" name="refund_finish_time_start"
                               value="{{$refund_finish_time_start|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                    </div>
                    <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                    <div class="input-append date datetimepicker">
                        <input class="span2" type="text" name="refund_finish_time_end"
                               value="{{$refund_finish_time_end|default}}"/>
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
                {{if 4 == $orderGoodsItem['order_goods_status']}}
                    <tr> <!-- 完成退款订单 -->
                        {{elseif 2 == $orderGoodsItem['order_goods_status']}}
                    <tr class="info"> <!-- 申请退款 -->
                        {{else}}
                    <tr class="error"> <!-- 退款中 -->
                {{/if}}
                <td>{{$orderGoodsItem['order_id']}}-{{$orderGoodsItem['rec_id']}}</td>
                <td>
                    <a rel="clickover" data-placement="top" href="#"
                       data-content="{{bzf_goods_view_toolbar goods_id=$orderGoodsItem['goods_id']}}">
                        ({{$orderGoodsItem['goods_id']}}){{$orderGoodsItem['goods_name']}}
                    </a>
                </td>
                <td>
                    <a href="#"
                       onclick="bZF.Account_User_ajaxDetail({{$orderGoodsItem['user_id']}});return false;">{{$orderGoodsItem['user_name']|default}}</a>
                    <br/>{{$orderGoodsItem['email']|default}}
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

                    <!-- 申请退款 的订单，可以设置退款中 -->
                    {{if 2 == $orderGoodsItem['order_goods_status'] }}
                        <a class="btn btn-small btn-info"
                           href="{{bzf_make_url controller='/Order/Refund/SetRefund' rec_id=$orderGoodsItem['rec_id']}}"
                           onclick="return confirm('请确认你已经收到顾客的退货了，然后才在这里把订单设置为 [退款中] 状态以通知财务人员去退款');"
                                >确认</a>
                    {{/if}}
                    <!-- /申请退款 的订单，可以设置退款中 -->

                    <!-- 退款中的订单，可以确认退款或者拒绝退款 -->
                    {{if 3 == $orderGoodsItem['order_goods_status'] }}
                        <button type="button" class="btn btn-small btn-danger"
                                onclick="jQuery('#order_refund_confirm_modal input[name=\'rec_id\']').val({{$orderGoodsItem['rec_id']}});jQuery('#order_refund_confirm_modal').modal();">
                            确认
                        </button>
                        <button type="button" class="btn btn-small"
                                onclick="jQuery('#order_refund_refuse_modal input[name=\'rec_id\']').val({{$orderGoodsItem['rec_id']}});jQuery('#order_refund_refuse_modal').modal();">
                            拒绝
                        </button>
                    {{/if}}
                    <!-- /退款中的订单，可以确认退款或者拒绝退款 -->

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

    <!-- 确认退款 Modal-->
    <div id="order_refund_confirm_modal" class="modal hide fade" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4>财务人员--确认已经退款</h4>
        </div>
        <form class="form-horizontal form-horizontal-inline" method="POST"
              action="Confirm">
            <input type="hidden" name="rec_id" value="0"/>

            <div class="modal-body">
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">备注</span>
                        <textarea name="refund_finish_note" class="span4" rows="5" cols="20"
                                  data-validation-required-message="备注不能为空"
                                ></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">确定</button>
                <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
            </div>
        </form>
    </div>
    <!-- /确认退款 Modal -->

    <!-- 拒绝退款 Modal-->
    <div id="order_refund_refuse_modal" class="modal hide fade" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4>财务人员--拒绝退款</h4>
        </div>
        <form class="form-horizontal form-horizontal-inline" method="POST"
              action="Refuse">
            <input type="hidden" name="rec_id" value="0"/>

            <div class="modal-body">
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">备注</span>
                        <textarea name="refund_finish_note" class="span4" rows="5" cols="20"
                                  data-validation-required-message="备注不能为空"
                                ></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">确定</button>
                <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
            </div>
        </form>
    </div>
    <!-- /拒绝退款 Modal -->

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