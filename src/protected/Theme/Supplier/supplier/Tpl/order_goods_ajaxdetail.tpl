<!-- 订单详情对话框 -->
<!-- div id="order_detail_dialog" class="modal hide fade" -->
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
        &times;
    </button>
    <h3>订单详情</h3>
</div>
<div class="modal-body">

    {{if !empty($errorMessage)}}
        <!-- 错误警告 -->
        <div id="goods_statistics_error" class="row" style="text-align: center;font-weight: bold;">
            <label class="label label-important">错误：</label>{{$errorMessage}}
        </div>
        <!-- /错误警告 -->
    {{/if}}

    {{if isset($orderGoods) && isset($orderInfo)}}

        <!-- 订单详情 -->
        <div class="row">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th width="20%">&nbsp;</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="labelkey">订单号</td>
                    <td id="order_detail_order_id" class="labelvalue">{{$orderGoods['order_id']}}
                        -{{$orderGoods['rec_id']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">来源系统</td>
                    <td class="labelvalue">
                        <label class="label label-success">{{$orderInfo['system_id']|bzf_system_name}}</label>
                    </td>
                </tr>
                <tr>
                    <td class="labelkey">商品名称</td>
                    <td id="order_detail_goods_name" class="labelvalue">({{$orderGoods['goods_id']}}
                        ){{$orderGoods['goods_name']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">商品货号</td>
                    <td class="labelvalue">{{$orderGoods['goods_sn']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">仓库货架</td>
                    <td class="labelvalue">{{$orderGoods['warehouse']}}&nbsp;|&nbsp;{{$orderGoods['shelf']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">商品选项</td>
                    <td id="order_detail_goods_number" class="labelvalue">{{$orderGoods['goods_attr']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">购买数量</td>
                    <td id="order_detail_goods_number" class="labelvalue">{{$orderGoods['goods_number']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">供货成本</td>
                    <td class="labelvalue">{{($orderGoods['goods_number'] * $orderGoods['suppliers_price'])|bzf_money_display}}</td>
                </tr>
                <tr>
                    <td class="labelkey">订单状态</td>
                    {{if 1 != $orderGoods['order_goods_status']}}
                        <td id="order_detail_order_status"
                            class="labelvalue price">{{$orderGoods['order_goods_status_desc']}}</td>
                    {{else}}
                        <td id="order_detail_order_status"
                            class="labelvalue">{{$orderGoods['order_goods_status_desc']}}</td>
                    {{/if}}
                </tr>
                <tr>
                    <td class="labelkey">退款说明</td>
                    <td class="labelvalue" style="color:red;">{{$orderGoods['refund_note']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">下单时间</td>
                    <td class="labelvalue">
                        <span id="order_detail_add_time">{{$orderInfo['add_time']|bzf_localtime}}</span>|
                        <span id="order_detail_pay_time">{{$orderInfo['pay_time']|bzf_localtime}}</span>
                    </td>
                </tr>

                <!-- 分割符 -->
                <tr>
                    <td colspan="2" class="well well-small">&nbsp;</td>
                </tr>
                <!-- /分割符 -->

                <tr>
                    <td class="labelkey">收件人</td>
                    <td id="order_detail_consignee" class="labelvalue">{{$orderInfo['consignee']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">手机号</td>
                    <td id="order_detail_mobile" class="labelvalue">
                        {{$orderInfo['mobile']}}&nbsp;&nbsp;
                        <a href="{{bzf_mobile_query_url mobile=$orderInfo['mobile']}}" class="btn btn-small"
                           target="_blank">
                            查询
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="labelkey">电话</td>
                    <td class="labelvalue">{{$orderInfo['tel']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">收件地址</td>
                    <td id="order_detail_address" class="labelvalue">{{$orderInfo['address']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">邮编</td>
                    <td id="order_detail_address" class="labelvalue">{{$orderInfo['zipcode']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">用户留言</td>
                    <td id="order_detail_postscript" class="labelvalue">{{$orderInfo['postscript']}}</td>
                </tr>

                <tr>
                    <td class="labelkey">客服备注</td>
                    <td id="order_detail_memo" class="labelvalue" style="color:red;">{{$orderGoods['memo']}}</td>
                </tr>

                <tr>
                    <td rowspan="2" class="labelkey">快递信息</td>
                    <input type="hidden" id="order_detail_rec_id" value="{{$orderGoods['rec_id']}}"/>
                    <td class="labelvalue">
                        <select class="span2 select2-simple" id="order_detail_shipping_select" data-placeholder="请选择快递"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Express/ListExpress"}}"
                                data-option-value-key="meta_id" data-option-text-key="meta_name"
                                data-initValue="{{$orderGoods['shipping_id']}}">
                            <option value=""></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="labelvalue">
                        快递单号：<input class="span2" id="order_detail_shipping_no" value="{{$orderGoods['shipping_no']}}"/>&nbsp;&nbsp;
                        <button type="button" class="btn btn-small"
                                onclick="bZF.Order_ListOrder_ajaxUpdate();">确定
                        </button>
                        {{if $orderGoods['shipping_id'] > 0}}
                            <a target="_blank" class="btn btn-small"
                               href="{{bzf_express_query_url expressName=$orderGoods['shipping_name'] expressNo=$orderGoods['shipping_no']}}">查询快递</a>
                        {{/if}}
                        <br/>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
        <!-- /订单详情 -->

    {{/if}}

</div>
<div class="modal-footer">
    <a href="#" class="btn btn-success" data-dismiss="modal">关闭</a>
</div>

<!-- /div --><!-- 订单详情对话框 -->
