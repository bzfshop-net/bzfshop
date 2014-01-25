{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#order_tabbar li:has(a[href='{{bzf_make_url controller='/Order/Order/Search'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 2, text: '订单详情', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

    <!-- 订单总信息 -->
    <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th width="11%">&nbsp;</th>
            <th>
                {{if $orderInfo['pay_status'] != 2 }}
                    <a class="btn btn-danger" onclick="return confirm('你确定要标记这个订单为已经付款吗？');"
                       href="{{bzf_make_url controller='/Order/Order/MarkPay' order_id=$orderInfo['order_id']}}">标记为已经付款</a>
                {{/if}}
            </th>
            <th width="11%">&nbsp;</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>

        <!-- 分割条 -->
        <tr>
            <td colspan="4" class="well well-small">&nbsp;</td>
        </tr>
        <!-- /分割条  -->

        <tr>
            <td class="labelkey">订单ID</td>
            <td class="labelvalue">{{$orderInfo['order_id']|default}}
                &nbsp;&nbsp;订单流水号：{{$orderInfo['order_sn']|default}}</td>
            <td class="labelkey">订单状态</td>
            <td class="labelvalue">{{$orderInfo['order_status_desc']|default}}
                ,{{$orderInfo['pay_status_desc']|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">下单时间</td>
            <td class="labelvalue">{{$orderInfo['add_time']|bzf_localtime}}
                |{{$orderInfo['pay_time']|bzf_localtime}}</td>
            <td class="labelkey">最后更新</td>
            <td class="labelvalue">{{$orderInfo['update_time']|bzf_localtime}}</td>
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
            <td class="labelkey">订单金额</td>
            <td class="labelvalue price">{{$orderInfo['order_amount']|bzf_money_display}}</td>
            <td class="labelkey">&nbsp;</td>
            <td class="labelvalue price">&nbsp;</td>
        </tr>

        <tr>
            <td class="labelkey">余额支付</td>
            <td class="labelvalue discount">{{$orderInfo['surplus']|bzf_money_display}}</td>
            <td class="labelkey">红包支付</td>
            <td class="labelvalue discount">{{$orderInfo['bonus']|bzf_money_display}}</td>
        </tr>

        <tr>
            <td class="labelkey">实际支付</td>
            <td class="labelvalue price">{{$orderInfo['money_paid']|bzf_money_display}}</td>
            <td class="labelkey">退款金额</td>
            <td class="labelvalue discount">{{$orderInfo['refund']|bzf_money_display}}</td>
        </tr>

        <tr>
            <td class="labelkey">
                <span rel="tooltip" data-placement="top"
                      data-title="我们网站发送给第三方支付网关的支付号码">
                    网站支付号
                </span>
            </td>
            <td class="labelvalue">{{$orderInfo['order_sn']}}_{{$orderInfo['order_id']}}</td>
            <td class="labelkey">
                <span rel="tooltip" data-placement="top"
                      data-title="用户是通过哪个系统下的这个订单">
                    来源系统
                </span>
            </td>
            <td class="labelvalue">
                <label class="label label-success">{{$orderInfo['system_id']|bzf_system_name}}</label>
            </td>
        </tr>

        <tr>
            <td class="labelkey">支付方式</td>
            <td class="labelvalue">{{$orderInfo['pay_type']|default}}</td>
            <td class="labelkey">
                <span rel="tooltip" data-placement="top"
                      data-title="第三方支付网关返回的支付号码">
                    {{$orderInfo['pay_type']|default}}&nbsp;支付号
                </span>
            </td>
            <td class="labelvalue">{{$orderInfo['pay_no']|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">订单来源</td>
            <td class="labelvalue" colspan="3">{{$orderRefer['refer_url']|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">来源渠道(主)</td>
            <td class="labelvalue">{{$orderRefer['utm_source']|bzf_dictionary_name}}</td>
            <td class="labelkey">来源渠道(次)</td>
            <td class="labelvalue">{{$orderRefer['utm_medium']|bzf_dictionary_name}}</td>
        </tr>

        <!-- 分割条 -->
        <tr>
            <td colspan="4" class="well well-small">&nbsp;</td>
        </tr>
        <!-- /分割条  -->

        <tr>
            <td class="labelkey">用户信息</td>
            <td colspan="3" class="labelvalue">
                <a href="#"
                   onclick="bZF.Account_User_ajaxDetail({{$userInfo['user_id']}});return false;">{{$userInfo['user_name']|default}}</a>
                |{{$userInfo['email']|default}}</td>
        </tr>

        <!-- 物流信息 -->
        <tr>
            <td class="labelkey">收货地址</td>
            <td class="labelvalue">{{$orderInfo['address']|default}}</td>
            <td class="labelkey">姓名</td>
            <td class="labelvalue">{{$orderInfo['consignee']|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">手机</td>
            <td class="labelvalue">
                {{$orderInfo['mobile']|default}}&nbsp;&nbsp;
                <a href="{{bzf_mobile_query_url mobile=$orderInfo['mobile']}}" class="btn btn-small" target="_blank">
                    查询
                </a>
            </td>
            <td class="labelkey">固定电话</td>
            <td class="labelvalue">{{$orderInfo['tel']|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">订单附言</td>
            <td class="labelvalue">{{$orderInfo['postscript']|default}}</td>
            <td class="labelkey">邮编</td>
            <td class="labelvalue">{{$orderInfo['zipcode']|default}}</td>
        </tr>
        <!-- /物流信息 -->

        <!-- 分割条 -->
        <tr>
            <td colspan="4" class="well well-small">&nbsp;</td>
        </tr>
        <!-- /分割条  -->

        <!-- 客服评价信息 -->
        <tr>
            <td class="labelkey">服务客服</td>
            <td class="labelvalue">[{{$orderInfo['kefu_user_id']}}]{{$orderInfo['kefu_user_name']}}</td>
            <td class="labelkey">服务打分</td>
            <td class="labelvalue">{{$orderInfo['kefu_user_rate']}}</td>
        </tr>
        <tr>
            <td class="labelkey">服务评价</td>
            <td class="labelvalue" colspan="3">{{$orderInfo['kefu_user_comment']}}</td>
        </tr>
        <!-- /客服评价信息 -->

        </tbody>
    </table>
    <!-- /订单总信息 -->

    <!-- 订单包含的商品列表 -->
    <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th>订单子ID</th>
            <th width="15%">商品</th>
            <th>选项</th>
            <th>数量</th>
            <th>状态</th>
            <th>商品金额</th>
            <th>商品优惠</th>
            <th>额外优惠</th>
            <th>快递费</th>
            <th>快递信息</th>
            <th>退款</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        {{if isset($orderGoodsArray)}}
            {{foreach $orderGoodsArray as $orderGoodsItem}}

                {{if $orderGoodsItem['order_goods_status'] > 2}}
                    <tr class="error"> <!-- 退款商品 -->
                        {{else}}
                    <tr>
                {{/if}}
                <td>{{$orderGoodsItem['rec_id']}}</td>
                <td>
                    <a href="#" rel="clickover" data-placement="top"
                       data-content="{{bzf_goods_view_toolbar goods_id=$orderGoodsItem['goods_id']}}">
                        <img class="lazyload" width="150" style="width:150px;height:auto;"
                             src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                             data-original="{{bzf_goods_thumb_image goods_id=$orderGoodsItem['goods_id']}}"/>
                    </a>
                </td>
                <td>{{$orderGoodsItem['goods_attr']}}</td>
                <td>{{$orderGoodsItem['goods_number']}}</td>
                <td>{{$orderGoodsItem['order_goods_status_desc']}}</td>
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
                    <a style="color:blue;" href="#" ref="popover" data-trigger="hover" data-placement="right"
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
                <td>{{if $orderGoodsItem['shipping_id'] > 0 }}
                        {{$orderGoodsItem['shipping_name']}}
                        <br/>
                        {{$orderGoodsItem['shipping_no']}}
                        <br/>
                        <a target="_blank" class="btn btn-small"
                           href="{{bzf_express_query_url expressName=$orderGoodsItem['shipping_name'] expressNo=$orderGoodsItem['shipping_no']}}">查询快递</a>
                    {{/if}}
                </td>
                <td>
                    <a style="color:blue;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                       data-content="{{$orderGoodsItem['refund_note']}}">
                        {{$orderGoodsItem['refund']|bzf_money_display}}
                    </a>
                </td>
                <td>
                    <a class="btn btn-small"
                       href="{{bzf_make_url controller='/Order/Goods/Detail' rec_id=$orderGoodsItem['rec_id']}}">详情</a>
                </td>
                </tr>
            {{/foreach}}
        {{/if}}
        </tbody>
    </table>
    <!-- /订单包含的商品列表 -->

    <!-- 订单操作日志 -->
    <div class="row">
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th width="15%">操作时间</th>
                <th width="6%">订单状态</th>
                <th width="6%">支付状态</th>
                <th>订单子ID</th>
                <th width="6%">商品状态</th>
                <th>操作人</th>
                <th>操作日志</th>
            </tr>
            </thead>
            <tbody>

            {{foreach $orderLogArray as $orderLog }}
                <tr>
                    <td>{{$orderLog['log_time']|bzf_localtime}}</td>
                    <td>{{$orderLog['order_status']|default}}</td>
                    <td>{{$orderLog['pay_status']|default}}</td>
                    <td>{{$orderLog['rec_id']|default}}</td>
                    <td>{{$orderLog['order_goods_status']|default}}</td>
                    <td>{{$orderLog['action_user']|default}}</td>
                    <td>{{$orderLog['action_note'] nofilter}}</td>
                </tr>
            {{/foreach}}

            </tbody>
        </table>
    </div>
    <!-- /订单操作日志 -->

    </div>
    <!-- /页面主体内容 -->

    <!-- 用户详情对话框 -->
    <div id="user_detail_dialog" class="modal hide fade">
    </div>
    <!-- 用户详情对话框 -->

{{/block}}