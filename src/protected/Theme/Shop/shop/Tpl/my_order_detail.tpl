{{extends file='my_layout.tpl'}}
{{block name=main_body_my}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bzf_set_nav_status.push(function ($) {
            $("#my_nav_tabbar li:has(a[href='{{bzf_make_url controller='/My/Order'}}'])").addClass("active");
        });
    </script>
    <!-- 页面主体内容 -->
    <div id="my_order_list_panel" class="row">

    <h4>订单详情</h4>

    <!-- 订单总信息 -->
    <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th width="10%">&nbsp;</th>
            <th>&nbsp;</th>
            <th width="10%">&nbsp;</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <!-- 分割条 -->
        <tr>
            <td colspan="4" class="well well-small discount">订单信息</td>
        </tr>
        <!-- /分割条  -->

        <tr>
            <td class="labelkey">订单流水号</td>
            <td class="labelvalue">{{$orderInfo['order_sn']|default}}</td>
            <td class="labelkey">订单状态</td>
            <td class="labelvalue">{{$orderInfo['pay_status_desc']|default}}
                ,{{$orderInfo['order_status_desc']|default}}
                {{if 0 == $orderInfo['order_status']}}&nbsp;&nbsp;&nbsp;
                    <a href="{{bzf_make_url controller='/My/Order/Cart' order_id=$orderInfo['order_id'] }}"
                       class="btn btn-small btn-success">现在付款</a>
                    &nbsp;&nbsp;
                    <a href="{{bzf_make_url controller='/My/Order/Cancel' order_id=$orderInfo['order_id'] }}"
                       class="btn btn-small btn-danger">取消订单</a>
                {{/if}}</td>
        </tr>

        <tr>
            <td class="labelkey">下单时间</td>
            <td class="labelvalue">{{$orderInfo['add_time']|bzf_localtime}}</td>
            <td class="labelkey">支付时间</td>
            <td class="labelvalue">{{$orderInfo['confirm_time']|bzf_localtime}}</td>
        </tr>

        <tr>
            <td class="labelkey">商品金额</td>
            <td class="labelvalue price">{{$orderInfo['goods_amount']|bzf_money_display}}</td>
            <td class="labelkey">快递费用</td>
            <td class="labelvalue price">{{$orderInfo['shipping_fee']|bzf_money_display}}</td>
        </tr>

        <tr>
            <td class="labelkey">商品优惠</td>
            <td class="labelvalue discount">{{$orderInfo['discount']|bzf_money_display}}</td>
            <td class="labelkey">额外优惠</td>
            <td class="labelvalue discount">{{$orderInfo['extra_discount']|bzf_money_display}}</td>
        </tr>

        <tr>
            <td class="labelkey">最终金额</td>
            <td class="labelvalue price">{{($orderInfo['goods_amount'] + $orderInfo['shipping_fee'] - $orderInfo['discount'] - $orderInfo['extra_discount'])|bzf_money_display}}</td>
            <td class="labelkey">&nbsp;</td>
            <td class="labelvalue">&nbsp;</td>
        </tr>

        <tr>
            <td class="labelkey">余额支付</td>
            <td class="labelvalue discount">{{$orderInfo['surplus']|bzf_money_display}}</td>
            <td class="labelkey">红包支付</td>
            <td class="labelvalue discount">{{$orderInfo['bonus']|bzf_money_display}}</td>
        </tr>

        <tr>
            <td class="labelkey">网络支付</td>
            <td class="labelvalue price">{{$orderInfo['money_paid']|bzf_money_display}}</td>
            <td class="labelkey">退款金额</td>
            <td class="labelvalue discount">{{$orderInfo['refund']|bzf_money_display}}</td>
        </tr>

        <tr>
            <td class="labelkey">支付方式</td>
            <td colspan="3" class="labelvalue">{{$orderInfo['pay_type']|default}}
                &nbsp;&nbsp;[{{$orderInfo['pay_no']|default}}]
            </td>
        </tr>

        </tbody>
    </table>
    <!-- /订单总信息 -->

    <!-- 物流信息 -->
    <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th width="20%">&nbsp;</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>

        <!-- 分割条 -->
        <tr>
            <td colspan="2" class="well well-small discount">物流信息</td>
        </tr>
        <!-- /分割条  -->

        <tr>
            <td class="labelkey">姓名*</td>
            <td class="labelvalue">{{$orderInfo['consignee']|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">收货地址*</td>
            <td class="labelvalue">{{$orderInfo['address']|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">手机*</td>
            <td class="labelvalue">{{$orderInfo['mobile']|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">固定电话</td>
            <td class="labelvalue">{{$orderInfo['tel']|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">邮编</td>
            <td class="labelvalue">{{$orderInfo['zipcode']|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">订单附言</td>
            <td class="labelvalue">{{$orderInfo['postscript']|default}}</td>
        </tr>

        </tbody>
    </table>
    <!-- /物流信息 -->

    {{if $orderInfo['kefu_user_id'] > 0}}

        <!-- 客服评价 -->
        <table class="table table-bordered table-hover">
            <tbody>

            <!-- 分割条 -->
            <tr>
                <td colspan="4" class="well well-small discount">客服评价</td>
            </tr>
            <!-- /分割条  -->

            <tr>
                <td class="labelkey" width="10%">服务客服</td>
                <td class="labelvalue">{{$orderInfo['kefu_user_name']}}</td>
                <td class="labelkey" width="10%">服务打分</td>
                <td class="labelvalue">
                    <div class="bzf_rate_star_readonly" rateValue="{{$orderInfo['kefu_user_rate']}}"></div>
                </td>
            </tr>

            <tr>
                <td class="labelkey" width="10%">服务评价</td>
                <td class="labelvalue" colspan="3">{{$orderInfo['kefu_user_comment']}}</td>
            </tr>

            </tbody>
        </table>
        <!-- /客服评价 -->

    {{/if}}

    <!-- 订单包含的商品列表 -->
    <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th width="20%">商品</th>
            <th>选项</th>
            <th>数量</th>
            <th>商品金额</th>
            <th>商品优惠</th>
            <th>额外优惠</th>
            <th>快递费</th>
            <th>状态</th>
            <th>退款</th>
            <th>快递信息</th>
        </tr>
        </thead>
        <tbody>
        {{if isset($orderGoodsArray)}}
            {{foreach $orderGoodsArray as $orderGoodsItem}}
                <tr>
                    <td>
                        <a target="_blank"
                           href="{{bzf_make_system_url system=$orderInfo['system_id']  controller='/Goods/View' goods_id=$orderGoodsItem['goods_id']}}"
                           ref="popover" data-trigger="hover" data-placement="right"
                           data-content="{{$orderGoodsItem['goods_name']}}">
                            <img class="lazyload" width="150" style="width:150px;height: auto;"
                                 src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                 data-original="{{bzf_goods_thumb_image goods_id=$orderGoodsItem['goods_id']}}"/>
                        </a>
                    </td>
                    <td>{{$orderGoodsItem['goods_attr']}}</td>
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
                        <a style="color:blue;" href="#" ref="popover" data-trigger="hover"
                           data-placement="right"
                           data-content="{{$orderGoodsItem['extra_discount_note']}}">
                            {{$orderGoodsItem['extra_discount']|bzf_money_display}}
                        </a>
                    </td>
                    <td>
                        <a style="color:red;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                           data-content="{{$orderGoodsItem['shipping_fee_note']}}">
                            {{$orderGoodsItem['shipping_fee']|bzf_money_display}}
                        </a>
                    </td>
                    <td>{{$orderGoodsItem['order_goods_status_desc']|default}}</td>
                    <td>
                        <a style="color:blue;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                           data-content="{{$orderGoodsItem['refund_note']}}">
                            {{$orderGoodsItem['refund']|bzf_money_display}}
                        </a>
                    </td>
                    <td>
                        {{if !empty($orderGoodsItem['shipping_no']) }}
                            {{$orderGoodsItem['shipping_name']}}
                            <br/>
                            {{$orderGoodsItem['shipping_no']}}
                            <br/>
                            <a target="_blank" class="btn btn-small"
                               href="{{bzf_express_query_url expressName=$orderGoodsItem['shipping_name'] expressNo=$orderGoodsItem['shipping_no']}}">快递查询</a>
                            <br/>
                            <button type="button" class="btn btn-small"
                                    onclick="bZF.my_order_goodscomment({{$orderGoodsItem['rec_id']}});">商品评价
                            </button>
                        {{elseif $orderGoodsItem['order_goods_status'] > 0 }}
                            正在发货，请耐心等待
                        {{/if}}
                    </td>
                </tr>
            {{/foreach}}
        {{/if}}
        </tbody>
    </table>
    <!-- /订单包含的商品列表 -->

    <!-- 商品评价对话框 -->
    <div id="bzf_my_order_detail_goods_comment" class="modal hide fade">
    </div>
    <!-- 商品评价对话框 -->

    </div>
    <!-- /页面主体内容 -->


{{/block}}