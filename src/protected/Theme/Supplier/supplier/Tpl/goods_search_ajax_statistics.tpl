<!-- 商品统计详情对话框 -->
<!-- div id="goods_statistics_dialog" class="modal hide fade" -->

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
        &times;
    </button>
    <h3>商品销售详情</h3>
</div>
<div class="modal-body">

    {{if !empty($errorMessage)}}
        <!-- 错误警告 -->
        <div id="goods_statistics_error" class="row" style="text-align: center;font-weight: bold;">
            <label class="label label-important">错误：</label>{{$errorMessage}}
        </div>
        <!-- /错误警告 -->
    {{/if}}

    {{if isset($statistics) && isset($goodsInfo) }}
        <!-- 商品统计详情 -->
        <div class="row">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th width="20%">&nbsp;</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody id="goods_statistics_tbody">
                <tr>
                    <td class="labelkey">商品ID</td>
                    <td id="goods_statistics_goods_id" class="labelvalue">{{$goodsInfo['goods_id']|default}}</td>
                </tr>
                <tr>
                    <td class="labelkey">商品名称</td>
                    <td id="goods_statistics_goods_name" class="labelvalue">{{$goodsInfo['goods_name']|default}}</td>
                </tr>
                <tr>
                    <td class="labelkey">商品定价</td>
                    <td class="labelvalue">
                        供货价：<span
                                id="goods_statistics_suppliers_price">{{$goodsInfo['suppliers_price']|bzf_money_display}}</span>&nbsp;
                        供货快递：<span
                                id="goods_statistics_suppliers_shipping_fee">{{$goodsInfo['suppliers_shipping_fee']|bzf_money_display}}</span>
                    </td>
                </tr>
                <tr>
                    <td class="labelkey">销售时间</td>
                    <td class="labelvalue">
                        <span id="goods_statistics_goods_start_time">&nbsp;</span>--
                        <span id="goods_statistics_goods_end_time">&nbsp;</span>
                    </td>
                </tr>


                <!-- 分割符 -->
                <tr>
                    <td colspan="2" class="well well-small">&nbsp;</td>
                </tr>
                <!-- /分割符 -->

                <tr>
                    <td class="labelkey">订单统计</td>
                    <td class="labelvalue">
                        总订单：<span id="goods_statistics_total_order">{{$statistics['total_order']|default}}</span>&nbsp;
                        付款：<span id="goods_statistics_pay_order">{{$statistics['pay_order']|default}}</span>&nbsp;
                        退款：<span id="goods_statistics_refund_order">{{$statistics['refund_order']|default}}</span>&nbsp;
                        未付款：<span id="goods_statistics_pay_order">{{$statistics['unpay_order']|default}}</span>
                    </td>
                </tr>

                <tr>
                    <td class="labelkey">商品统计</td>
                    <td class="labelvalue">
                        总商品：<span id="goods_statistics_total_goods">{{$statistics['total_goods']|default}}</span>&nbsp;
                        付款：<span id="goods_statistics_pay_goods">{{$statistics['pay_goods']|default}}</span>&nbsp;
                        退款：<span id="goods_statistics_refund_goods">{{$statistics['refund_goods']|default}}</span>&nbsp;
                        未付款：<span id="goods_statistics_pay_goods">{{$statistics['unpay_goods']|default}}</span>
                    </td>
                </tr>

                {{foreach $statistics['goods_attr'] as $goodsAttr => $goodsAttrValueArray }}
                    <tr>
                        <td class="labelkey"></td>
                        <td class="labelvalue">
                            {{$goodsAttr}}<br/>
                            总订单：<span>{{$goodsAttrValueArray['total_order']|default}}</span>&nbsp;
                            付款：<span>{{$goodsAttrValueArray['pay_order']|default}}</span>&nbsp;
                            退款：<span>{{$goodsAttrValueArray['refund_order']|default}}</span>&nbsp;
                            未付款：<span>{{$goodsAttrValueArray['unpay_order']|default}}</span>
                            <br/>
                            总商品：<span>{{$goodsAttrValueArray['total_goods']|default}}</span>&nbsp;
                            付款：<span>{{$goodsAttrValueArray['pay_goods']|default}}</span>&nbsp;
                            退款：<span>{{$goodsAttrValueArray['refund_goods']|default}}</span>&nbsp;
                            未付款：<span>{{$goodsAttrValueArray['unpay_goods']|default}}</span>
                        </td>
                    </tr>
                {{/foreach}}

                </tbody>
            </table>
        </div>
        <!-- /商品统计详情 -->
    {{/if}}

</div>
<div class="modal-footer">
    <a href="#" class="btn btn-success" data-dismiss="modal">关闭</a>
</div>

<!-- /div --><!-- 商品统计详情对话框 -->            

