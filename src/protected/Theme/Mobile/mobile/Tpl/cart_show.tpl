{{extends file='page_layout.tpl'}}
{{block name=main_page_content}}

    <!-- 标题 -->
    <h4>我的购物车</h4>
    <!-- /标题 -->
    <table data-role="table" id="my_order_table" data-mode="columntoggle"
           class="ui-body-d ui-shadow table-stripe ui-responsive" data-column-btn-theme="b"
           data-column-btn-text="选择显示列" data-column-popup-theme="a">
        <thead>
        <tr class="ui-bar-f">
            <th>商品</th>
            <th data-priority="2">选项</th>
            <th data-priority="3">数量</th>
            <th>金额</th>
            <th>邮费</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>

        {{if isset($orderGoodsArray)}}
            {{foreach $orderGoodsArray as $orderGoodsItem}}
                <tr>
                    <td>
                        <img class="lazyload goods_thumbnail_image"
                             src="{{bzf_get_asset_url asset='img/lazyload_placeholder_298_223.png'}}"
                             data-original="{{bzf_goods_thumb_image goods_id=$orderGoodsItem->getValue('goods_id')}}"/>
                        <noscript>
                            <img src="{{bzf_goods_thumb_image goods_id=$orderGoodsItem->getValue('goods_id')}}"/>>
                        </noscript>
                    </td>
                    <td>{{$orderGoodsItem->getValue('goods_attr')}}</td>
                    <td>{{$orderGoodsItem->getValue('goods_number')}}</td>
                    <td style="color:red;">{{$orderGoodsItem->getValue('goods_price')|bzf_money_display}}</td>
                    <td style="color:red;">{{$orderGoodsItem->getValue('shipping_fee')|bzf_money_display}}</td>
                    <td>
                        <a data-role="button" data-theme="b" data-mini="true"
                           href="{{bzf_make_url controller='/Goods/View'
                           goods_id=$orderGoodsItem->getValue('goods_id')}}">修改</a>
                        <a data-role="button" data-theme="b" data-mini="true"
                           href="{{bzf_make_url controller='/Cart/Show/Remove'
                           goods_id=$orderGoodsItem->getValue('goods_id') specListStr=$orderGoodsItem->getValue('goods_attr') }}">删除</a>
                    </td>
                </tr>
            {{/foreach}}
        {{else}}
            <tr>
                <td colspan="6" style="text-align: center;font-weight: bold;color: blue;">购物车为空，请返回选择商品</td>
            </tr>
        {{/if}}

        </tbody>
    </table>
    <br/>
    {{if isset($orderGoodsArray)}}
        <!-- 购买商品的选择和数量 -->
        <form id="cart_show_form" method="POST">

            <div style="padding:10px 20px;">

                <label for="consignee">收件地址*</label>
                <input type="text" name="address" required="required" value="{{$address|default nocache}}"
                       placeholder="请按照 省、市、区、地址 方式填写"/>

                <label for="consignee">收件人*</label>
                <input type="text" name="consignee" required="required" value="{{$consignee|default nocache}}"
                       placeholder="在这里填写收件人姓名"/>

                <label for="mobile">手机号*</label>
                <input type="text" name="mobile" required="required" pattern="1([0-9]{10})"
                       value="{{$mobile|default nocache}}"
                       placeholder="手机号用于快递联系您给您派件"/>

                <label for="postscript">订单留言</label>
                <textarea type="text" name="postscript" placeholder="在这里写你的额外要求"></textarea>

                <br/>

                <button type="submit" data-theme="e">确认购买</button>
            </div>

        </form>
        <!-- 购买商品的选择和数量 -->

    {{else}}
        <a data-role="button" data-theme="e"
           data-transition="slide" data-rel="back" data-icon="arrow-l" data-iconpos="left"
           href="{{bzf_make_url controller='/'}}">去挑选商品</a>
    {{/if}}

{{/block}}
