{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bzf_set_nav_status.push(function ($) {
            $("#bzf_header_nav_menu li:has(a[href='{{bzf_make_url controller='/'}}'])").addClass("active");
        });
    </script>
    <!-- 主体内容 row -->
    <div class="row" style="background-color: white;padding: 10px 10px;border: solid 1px white;">

    <!-- 购买过程步骤显示 -->
    <div class="row">
        <h4> 购物仅需四步： </h4>

        <div class="progress progress-striped active" style="height: 40px;">
            <div class="bar bar-success active" style="width: 25%;">
                <h5>1.放入购物车&nbsp;&nbsp;&gt;&gt;</h5>
            </div>
            <div class="bar bar-success active" style="width: 25%;">
                <h5>2.购物车确认&nbsp;&nbsp;&gt;&gt;</h5>
            </div>
            <div class="bar bar-success active" style="width: 25%;">
                <h5>3.选择支付方式&nbsp;&nbsp;&gt;&gt;</h5>
            </div>
            <div class="bar bar-warning" style="width: 25%;">
                <h5>4.支付成功</h5>
            </div>
        </div>
    </div>
    <!-- /购买过程步骤显示 -->

    {{if isset($orderNotifyUrlArray)}}
        <!-- 订单推送 url -->
        <div style="display: none;">
            {{foreach $orderNotifyUrlArray as $orderNotifyUrl}}
                <img src="{{$orderNotifyUrl}}"/>
            {{/foreach}}
        </div>
        <!-- /订单推送 url -->
    {{/if}}

    <!-- 购物车提交表单  -->
    <form class="form-horizontal" method="post" target="_blank">

    <!-- /支付页面主面板 -->
    <div id="pay_main_panel" class="row">

    <!-- 购物车显示列表 -->
    <table id="cart_goods_show_table" class="table table-bordered table-hover">
        <thead>
        <tr>
            <th width="20%">商品</th>
            <th width="20%">选项</th>
            <th>数量</th>
            <th>单价</th>
            <th>邮费</th>
            <th>优惠</th>
            <th>额外</th>
            <th>小计</th>
        </tr>
        </thead>
        <tbody>

        {{foreach $orderGoodsArray as $orderGoodsItem}}

            <!-- 购物车一个商品的内容 -->
            <tr>
                <td>

                    {{if $orderGoodsItem->getValue('discount') > 0}}
                        <!-- 享受优惠的提示 -->
                        <div style="line-height: 20px;">
                            <span style="color:blue;font-weight: bold;">享受优惠</span><br/>
                            <span id="goods_buy_discount_notice_message"
                                  style="color:red;font-weight: bold;">{{$orderGoodsItem->getValue('discount_note')}}</span>
                        </div>
                        <!-- 享受优惠的提示 -->
                    {{/if}}

                    <a href="{{bzf_make_url controller='/Goods/View' goods_id=$orderGoodsItem->getValue('goods_id') }}"
                       target="_blank" ref="popover" data-trigger="hover" data-placement="right"
                       data-content="{{$orderGoodsItem->getValue('goods_name')}}">
                        <img width="150" class="lazyload"
                             src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                             data-original="{{bzf_goods_thumb_image goods_id=$orderGoodsItem->getValue('goods_id') }}"/>
                    </a>
                </td>
                <td>{{$orderGoodsItem->getValue('goods_attr')}}</td>
                <td>{{$orderGoodsItem->getValue('goods_number')}}</td>
                <td class="price">{{$orderGoodsItem->getValue('shop_price')|bzf_money_display}}</td>
                <td>
                    <a style="color:red;" href="#" ref="popover" data-trigger="hover" data-placement="right"
                       data-content="{{$orderGoodsItem->getValue('shipping_fee_note')}}">
                        {{$orderGoodsItem->getValue('shipping_fee')|bzf_money_display}}
                    </a>
                </td>
                <td>
                    <a style="color:blue;" href="#" ref="popover" data-trigger="hover"
                       data-placement="right"
                       data-content="{{$orderGoodsItem->getValue('discount_note')}}">
                        {{$orderGoodsItem->getValue('discount')|bzf_money_display}}
                    </a>
                </td>
                <td>
                    <a style="color:blue;" href="#" ref="popover" data-trigger="hover"
                       data-placement="right"
                       data-content="{{$orderGoodsItem->getValue('extra_discount_note')}}">
                        {{$orderGoodsItem->getValue('extra_discount')|bzf_money_display}}
                    </a>
                </td>
                <td class="price">{{($orderGoodsItem->getValue('goods_price') + $orderGoodsItem->getValue('shipping_fee') - $orderGoodsItem->getValue('discount') - $orderGoodsItem->getValue('extra_discount'))|bzf_money_display}}</td>
            </tr>
            <!-- /购物车一个商品的内容 -->

        {{/foreach}}

        <!-- 总计结果 -->
        <tr>
            <td colspan="4">总计</td>
            <td class="price">{{$cartContext->getValue('shipping_fee')|bzf_money_display}}</td>
            <td class="discount"
                colspan="2">{{($cartContext->getValue('discount') + $cartContext->getValue('extra_discount'))|bzf_money_display}}</td>
            <td id="cart_pay_order_amount"
                class="price">{{$cartContext->getValue('order_amount')|bzf_money_display}}</td>
        </tr>
        <!-- /总计结果 -->

        <!-- 分割条 -->
        <tr>
            <td colspan="8" class="well well-small discount">收件人信息</td>
        </tr>
        <!-- /分割条  -->

        <!-- 物流信息 -->
        <tr>
            <td class="labelkey">姓名*</td>
            <td colspan="7" class="labelvalue">{{$consignee|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">收货地址*</td>
            <td colspan="7" class="labelvalue">{{$address|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">手机*</td>
            <td colspan="7" class="labelvalue">{{$mobile|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">固定电话</td>
            <td colspan="7" class="labelvalue">{{$tel|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">邮编</td>
            <td colspan="7" class="labelvalue">{{$zipcode|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">订单附言</td>
            <td colspan="7" class="labelvalue">{{$postscript|default}}</td>
        </tr>
        <!-- /物流信息 -->

        <!-- 修改购物车 -->
        <tr>
            <td colspan="8" class="well well-small discount">
                还想再修改？
                <a style="margin-left:20px;" class="btn btn-info"
                   href="{{bzf_make_url controller='/My/Order/Cart' order_id=$order_id }}">
                    点击->返回购物车
                </a>
            </td>
        </tr>
        <!-- /修改购物车  -->

        </tbody>
    </table>
    <!-- /购物车显示列表 -->

    <!-- 使用 账户余额、红包 -->
    <table class="table table-bordered table-hover">
        <tbody>
        <tr class="control-group">
            <td colspan="2" class="controls discount well well-small">积分、红包</td>
        </tr>

        <!-- 使用账户余额 -->
        <tr class="control-group">
            <td class="labelkey" width="10%">使用余额</td>
            <td class="controls labelvalue">
                <input type="text" class="input-small" id="cart_pay_surplus" name="surplus"
                       value="{{min($userInfo['user_money'], $cartContext->getValue('order_amount'), $cartContext->getValue('surplus'))|bzf_money_display}}"
                       pattern="^\d+(\.\d+)?$" data-validation-pattern-message="必须是有效数字" min="0"
                       max="{{min($userInfo['user_money'], $cartContext->getValue('order_amount'))|bzf_money_display}}"/>

                <p>
                    您当前的可用余额为：{{$userInfo['user_money']|bzf_money_display}}
                    &nbsp;元，最多使用{{min($userInfo['user_money'], $cartContext->getValue('order_amount'))|bzf_money_display}}
                    &nbsp;元
                </p></td>
        </tr>
        <!-- /账户余额 -->

        </tbody>
    </table>
    <!-- /使用 账户余额、红包 -->

    <!-- 在线支付 -->

    <!-- 支付方式分隔条 -->
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td class="discount well well-small">请选择支付方式</td>
        </tr>
        </tbody>
    </table>
    <!-- /支付方式分隔条 -->

    <!-- 查询财付通插件的版本，我们的网银支付是通过财付通实现的 -->
    {{bzf_query_plugin_feature_var varName='tenpayVersion' uniqueId='585CB626-AC0F-4801-8597-C7065CC1F48B' command='pluginGetVersion' }}
    {{if !empty($tenpayVersion)}}
        <!-- 网银支付 -->
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td colspan="4" class="discount well well-small">网银支付</td>
            </tr>

            <tr>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1001" checked="checked"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 cmb-v2">招商银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1002"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 icbc-v2">工商银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1003"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 ccb-v2">建设银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1005"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 abc-v2">农业银行</span></label>
                </td>
            </tr>

            <tr>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1052"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 boc-v2">中国银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1004"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 spdb-v2">浦发银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1027"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 gdb-v2">广发银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1028"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 post-v2">邮储银行</span></label>
                </td>
            </tr>

            <tr>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1022"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 cebb-v2">光大银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1006"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 cmbc-v2">民生银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1021"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 ecitic-v2">中信银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1009"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 cib-v2">兴业银行</span></label>
                </td>
            </tr>

            <tr>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1010"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 pingan-v2">平安银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1008"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 sdb-v2">深圳发展银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1020"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 boco-v2">交通银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1032"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 bob-v2">北京银行</span></label>
                </td>
            </tr>

            <tr>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1025"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 hxb-v2">华夏银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1024"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 shanghai-v2">上海银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1082"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 srcb-v2">上海农商</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1054"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 njcb-v2">南京银行</span></label>
                </td>
            </tr>

            <tr>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1056"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 nbcb-v2">宁波银行</span></label>
                </td>
                <td>
                    <input type="radio" name="pay_gateway_type" value="tenpay_1076"/>
                    <label class="bank-logo-wrap"><span class="bank-logo-v2 hkbea-v2">东亚银行</span></label>
                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>

            </tbody>
        </table>
        <!-- /网银支付 -->
    {{/if}}

    <!-- 查询支付宝插件的版本 -->
    {{bzf_query_plugin_feature_var varName='alipayVersion' uniqueId='53A3B5B0-8038-47BB-BB45-EDC90934467E' command='pluginGetVersion' }}
    {{if !empty($alipayVersion)}}
        <!-- 支付宝支付 -->
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td colspan="2" class="discount well well-small">支付宝支付</td>
            </tr>
            <tr>
                <td class="labelkey" width="10%">支付宝</td>
                <td class="labelvalue">
                    <input type="radio" name="pay_gateway_type" value="alipay"/>
                    <img src="{{bzf_get_asset_url asset='img/alipay.gif'}}"/></td>
            </tr>
            </tbody>
        </table>
        <!-- /支付宝支付 -->
    {{/if}}

    <!-- 查询财付通插件的版本 -->
    {{bzf_query_plugin_feature_var varName='tenpayVersion' uniqueId='585CB626-AC0F-4801-8597-C7065CC1F48B' command='pluginGetVersion' }}
    {{if !empty($tenpayVersion)}}
        <!-- 财付通支付 -->
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td colspan="2" class="discount well well-small">财付通支付</td>
            </tr>
            <tr>
                <td class="labelkey" width="10%">财付通</td>
                <td class="labelvalue">
                    <input type="radio" name="pay_gateway_type" value="tenpay"/>
                    <img src="{{bzf_get_asset_url asset='img/tenpay.gif'}}"/></td>
            </tr>
            </tbody>
        </table>
        <!-- /财付通支付 -->
    {{/if}}

    <!-- 评价客服 -->
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td colspan="2" class="discount well well-small">客服评价</td>
        </tr>
        <tr>
            <td class="labelkey" width="10%">选择客服</td>
            <td class="labelvalue">
                <select class="span2 select2-simple" name="kefu_user_id" data-placeholder="客服列表"
                        data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/AdminUser/ListKefuIdName"}}"
                        data-option-value-key="user_id" data-option-text-key="user_name"
                        data-initValue="{{$orderInfo['kefu_user_id']}}">
                    <option value="0">没有客服服务</option>
                </select>
                <span class="comments">（如果有客服为您服务过，您可以选择评价她，您的评价会用于客服的服务考核）</span>
            </td>
        </tr>
        <tr>
            <td class="labelkey">服务打分</td>
            <td class="labelvalue">
                <input id="bzf_cart_pay_kefu_user_rate" type="hidden" name="kefu_user_rate"
                       value="{{$orderInfo['kefu_user_rate']}}"/>

                <div class="bzf_rate_star" targetInputSelector="#bzf_cart_pay_kefu_user_rate"
                     rateValue="{{$orderInfo['kefu_user_rate']}}"></div>
            </td>
        </tr>
        <tr>
            <td class="labelkey">服务评价</td>
            <td class="labelvalue">
                <input type="text" class="span10" name="kefu_user_comment"
                       value="{{$orderInfo['kefu_user_comment']}}"/>
            </td>
        </tr>
        </tbody>
    </table>
    <!-- /评价客服 -->

    <!-- 支付信息总结 -->
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td id="cart_pay_final_price_desc">&nbsp;</td>
        </tr>
        </tbody>
    </table>
    <!-- /支付信息总结  -->

    <!-- /在线支付 -->

    </div>
    <!-- /支付页面主面板 -->

    <!-- 表单结束的操作按钮 -->
    <div class="row" style="text-align: center;">
        <button type="submit" class="btn btn-large btn-success"
                onclick='jQuery("#pay_modal_dialog").modal()'>
            确认支付
        </button>
    </div>
    <!-- /表单结束的操作按钮 -->

    <!-- 弹出对话框 -->
    <div id="pay_modal_dialog" class="modal hide fade">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                &times;
            </button>
            <h3>正在跳转支付</h3>
        </div>
        <div class="modal-body">
            <p>
                <label class="label label-important">注意：</label>浏览器已经弹出一个新页面，请在新页面上完成支付，如果没有新窗口打开，请检查支付页面上您的输入是否有错误
            </p>

            <p>
                <label class="label label-info">提示：</label>如果您支付遇到问题，请联系网页右侧QQ在线客服
            </p>
        </div>
        <div class="modal-footer">
            <a href="{{bzf_make_url controller='/Cart/Pay' order_id=$order_id }}" class="btn btn-danger">支付失败</a>
            <a href="{{bzf_make_url controller='/My/Order/Detail' order_id=$order_id }}"
               class="btn btn-success">支付成功</a>
        </div>
    </div>
    <!-- 弹出对话框 -->

    </form>
    <!-- /购物车提交表单 -->

    </div>
    <!-- /购物车页面 -->

{{/block}}