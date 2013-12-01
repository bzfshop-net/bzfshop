<!-- 这个 div 用于 ajax load 的时候把其中的内容加载到对应的地方 -->
<div id="cart_show_ajaxshow">

    <!-- 购物车显示列表 -->
    <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th>商品</th>
            <th>选项</th>
            <th>单价</th>
            <th>数量</th>
            <th>邮费</th>
            <th>优惠</th>
            <th>金额</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>

        {{if isset($orderGoodsArray)}}
            {{foreach $orderGoodsArray as $orderGoodsItem}}

                <!-- 购物车一个商品的内容 -->
                <tr>
                    <td>
                        <a href="{{bzf_make_url controller='/Goods/View' goods_id=$orderGoodsItem->getValue('goods_id') }}"
                           target="_blank" ref="popover" data-trigger="hover" data-placement="right"
                           data-content="{{$orderGoodsItem->getValue('goods_name')}}">
                            <img src="{{bzf_goods_thumb_image goods_id=$orderGoodsItem->getValue('goods_id') }}"/>
                        </a>
                    </td>
                    <td>{{$orderGoodsItem->getValue('goods_attr')}}</td>
                    <td class="price">{{$orderGoodsItem->getValue('shop_price')|bzf_money_display}}</td>
                    <td>{{$orderGoodsItem->getValue('goods_number')}}</td>
                    <td class="price">{{$orderGoodsItem->getValue('shipping_fee')|bzf_money_display}}</td>
                    <td class="discount">{{$orderGoodsItem->getValue('discount')|bzf_money_display}}</td>
                    <td class="price">{{($orderGoodsItem->getValue('goods_price') + $orderGoodsItem->getValue('shipping_fee') - $orderGoodsItem->getValue('discount') - $orderGoodsItem->getValue('extra_discount'))|bzf_money_display}}</td>
                    <td>
                        <a href="{{bzf_make_url controller='/Cart/Show/Remove'
                        goods_id=$orderGoodsItem->getValue('goods_id') isAjax='true' specListStr=urlencode($orderGoodsItem->getValue('goods_attr')) }}"
                           class="btn btn-small btn-warning cartRemoveGoods">删除</a>
                    </td>
                </tr>
                <!-- /购物车一个商品的内容 -->

            {{/foreach}}

        {{else}}
            <tr>
                <td colspan="8" class="well price">购物车为空</td>
            </tr>
        {{/if}}

        </tbody>
    </table>
    <!-- /购物车显示列表 -->

</div>
