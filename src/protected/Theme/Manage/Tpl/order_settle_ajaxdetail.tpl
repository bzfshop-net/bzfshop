<!-- 结算记录设置 modal -->
<!-- div id="order_settle_listsettle_modal_update" class="modal hide fade" tabindex="-1" role="dialog"
     aria-hidden="true" -->

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4>结算记录设置</h4>
</div>

<!-- 更新提交表单 -->
<form class="form-horizontal form-horizontal-inline" method="POST"
      action="Update?settle_id={{$settle_id|default}}">
    <div class="modal-body">

        {{if !empty($errorMessage)}}
            <!-- 错误警告 -->
            <div class="row" style="text-align: center;font-weight: bold;">
                <label class="label label-important">错误：</label>{{$errorMessage}}
            </div>
            <!-- /错误警告 -->
        {{/if}}

        {{if isset($orderSettle) }}

            <!-- 结算记录详情 -->
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
                        <td class="labelkey">结算ID</td>
                        <td class="labelvalue">{{$orderSettle['settle_id']}}</td>
                    </tr>
                    <tr>
                        <td class="labelkey">订单时间</td>
                        <td class="labelvalue">{{$orderSettle['settle_start_time']|bzf_localtime}}
                            | {{$orderSettle['settle_end_time']|bzf_localtime}}</td>
                    </tr>
                    <tr>
                        <td class="labelkey">结算时间</td>
                        <td class="labelvalue">{{$orderSettle['create_time']|bzf_localtime}}</td>
                    </tr>
                    <tr>
                        <td class="labelkey">结算人</td>
                        <td class="labelvalue">{{$orderSettle['user_name']}}</td>
                    </tr>
                    <tr>
                        <td class="labelkey">商品金额</td>
                        <td class="labelvalue price">{{$orderSettle['suppliers_goods_price']|bzf_money_display}}</td>
                    </tr>
                    <tr>
                        <td class="labelkey">快递金额</td>
                        <td class="labelvalue price">{{$orderSettle['suppliers_shipping_fee']|bzf_money_display}}</td>
                    </tr>
                    <tr>
                        <td class="labelkey">退款金额</td>
                        <td class="labelvalue discount">{{$orderSettle['suppliers_refund']|bzf_money_display}}</td>
                    </tr>
                    <tr>
                        <td class="labelkey">结算金额</td>
                        <td class="labelvalue price">{{($orderSettle['suppliers_goods_price'] + $orderSettle['suppliers_shipping_fee'] - $orderSettle['suppliers_refund'])|bzf_money_display}}</td>
                    </tr>
                    <tr>
                        <td class="labelkey">付款方式</td>
                        <td class="labelvalue">
                            <input type="text" name="orderSettle[pay_type]" value="{{$orderSettle['pay_type']}}"
                                   class="span2"/>(比如：支付宝、财付通
                            ...)
                        </td>
                    </tr>
                    <tr>
                        <td class="labelkey">付款交易号</td>
                        <td class="labelvalue">
                            <input type="text" name="orderSettle[pay_no]" value="{{$orderSettle['pay_no']}}"
                                   class="span2"/>(比如：支付宝的交易号)
                        </td>
                    </tr>
                    <tr>
                        <td class="labelkey">付款时间</td>
                        <td class="labelvalue">
                            <div class="input-append date datetimepicker">
                                <input class="span2" type="text" name="orderSettle[pay_time]"
                                       value="{{$orderSettle['pay_time']|bzf_localtime}}"/>
                                    <span class="add-on">
                                        <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                                    </span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="labelkey">备注</td>
                        <td class="labelvalue">
                            <textarea name="orderSettle[memo]" rows="5" cols="20"
                                      class="span4">{{$orderSettle['memo']}}</textarea>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <!-- /结算记录详情 -->

        {{/if}}
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-danger">确定</button>
        <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
    </div>

</form>
<!-- /更新提交表单 -->

<!-- /div -->
<!-- /结算记录设置 modal -->

