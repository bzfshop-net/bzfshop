{{extends file='page_layout.tpl'}}
{{block name=main_page_content}}

    {{if isset($orderNotifyUrlArray)}}
        <!-- 订单推送 url -->
        <div style="display: none;">
            {{foreach $orderNotifyUrlArray as $orderNotifyUrl}}
                <img src="{{$orderNotifyUrl}}"/>
            {{/foreach}}
        </div>
        <!-- /订单推送 url -->
    {{/if}}

    <!-- 显示商品信息 -->
    <table id="cart_pay_order_table" data-role="table"
           class="ui-body-d ui-shadow table-stripe ui-responsive">
        <thead>
        <tr class="ui-bar-f">
            <th>商品</th>
            <th>选项</th>
            <th>数量</th>
            <th>金额</th>
            <th>邮费</th>
        </tr>
        </thead>
        <tbody>

        {{if isset($orderGoodsArray)}}
            {{foreach $orderGoodsArray as $orderGoodsItem}}
                <tr>
                    <td>
                        <a href="{{bzf_make_url controller='/Goods/View' goods_id=$orderGoodsItem->getValue('goods_id')}}">
                            <img class="lazyload goods_thumbnail_image"
                                 src="{{bzf_get_asset_url asset='img/lazyload_placeholder_298_223.png'}}"
                                 data-original="{{bzf_goods_thumb_image goods_id=$orderGoodsItem->getValue('goods_id')}}"/>
                            <noscript>
                                <img src="{{bzf_goods_thumb_image goods_id=$orderGoodsItem->getValue('goods_id')}}"/>>
                            </noscript>
                        </a>
                    </td>
                    <td>{{$orderGoodsItem->getValue('goods_attr')}}</td>
                    <td>{{$orderGoodsItem->getValue('goods_number')}}</td>
                    <td style="color:red;">{{$orderGoodsItem->getValue('goods_price')|bzf_money_display}}</td>
                    <td style="color:red;">{{$orderGoodsItem->getValue('shipping_fee')|bzf_money_display}}</td>
                </tr>
            {{/foreach}}
        {{/if}}

        </tbody>
    </table>
    <!-- /显示商品信息 -->
    <br/>
    <!-- 显示地址信息 -->
    <div class="ui-grid-a">
        <div class="ui-block-a" style="width:30%;max-width: 80px;font-weight: bold;">收件地址</div>
        <div class="ui-block-b" style="width:70%">{{$address|default}}</div>
        <div class="ui-block-a" style="width:30%;max-width: 80px;font-weight: bold;">收件人</div>
        <div class="ui-block-b" style="width:70%">{{$consignee|default}}</div>
        <div class="ui-block-a" style="width:30%;max-width: 80px;font-weight: bold;">手机号</div>
        <div class="ui-block-b" style="width:70%">{{$mobile|default}}</div>
        <div class="ui-block-a" style="width:30%;max-width: 80px;font-weight: bold;">订单留言</div>
        <div class="ui-block-b" style="width:70%">{{$postscript|default}}</div>
    </div>
    <!-- /显示地址信息 -->
    <br/>
    <!-- 总价 -->
    <div class="ui-grid-a">
        <div class="ui-block-a" style="width:30%;max-width: 80px;font-weight: bold;">总金额</div>
        <div class="ui-block-b"
             style="width:70%;color:red;">{{$cartContext->getValue('order_amount')|bzf_money_display}}</div>
    </div>
    <!-- 总价 -->
    <br/>
    <!-- 选择支付方式进行支付 -->
    <form data-ajax="false" method="POST"
          action="{{bzf_make_url controller='/Cart/Pay' order_id=$order_id}}">

        <fieldset data-role="controlgroup">
            <legend>选择支付方式</legend>
            <input type="radio" name="pay_gateway_type" id="pay_gateway_type-alipaywap" value="alipaywap"
                   checked="checked">
            <label for="pay_gateway_type-alipaywap">支付宝</label>
            <input type="radio" name="pay_gateway_type" id="pay_gateway_type-tenpaywap" value="tenpaywap"
                   disabled="disabled">
            <label for="pay_gateway_type-tenpaywap">财付通</label>
        </fieldset>

        <button type="submit" data-theme="e">确认支付</button>
    </form>
    <!-- 选择支付方式进行支付 -->
{{/block}}
