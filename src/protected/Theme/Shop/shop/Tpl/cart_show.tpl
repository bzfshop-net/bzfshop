{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bzf_set_nav_status.push(function ($) {
            $("#bzf_header_nav_menu li:has(a[href='{{bzf_make_url controller='/'}}'])").addClass("active");
        });
    </script>
    <!-- 主体内容 row -->
    <div class="row" style="background-color: white;padding: 10px 10px; border: solid 1px white;">

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
            <div class="bar bar-warning" style="width: 25%;">
                <h5>3.选择支付方式&nbsp;&nbsp;&gt;&gt;</h5>
            </div>
            <div class="bar bar-warning" style="width: 25%;">
                <h5>4.支付成功</h5>
            </div>
        </div>
    </div>
    <!-- /购买过程步骤显示 -->

    <!-- 购物车提交表单  -->
    <form class="form-horizontal" method="post">

    <!-- 购物车显示列表 -->
    <div id="cart_main_panel" class="row">
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
                <th width="5%">操作</th>
            </tr>
            </thead>
            <tbody>

            {{if isset($orderGoodsArray)}}
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
                        <td class="price">
                            {{($orderGoodsItem->getValue('goods_price') + $orderGoodsItem->getValue('shipping_fee') - $orderGoodsItem->getValue('discount') -$orderGoodsItem->getValue('extra_discount'))|bzf_money_display}}
                        </td>
                        <td>
                            <a href="{{bzf_make_url controller='/Goods/View'  goods_id=$orderGoodsItem->getValue('goods_id') }}"
                               class="btn btn-small">修改</a>
                            <a href="{{bzf_make_url controller='/Cart/Show/Remove'
                            goods_id=$orderGoodsItem->getValue('goods_id') specListStr=urlencode($orderGoodsItem->getValue('goods_attr')) }}"
                               class="btn btn-small btn-warning">删除</a>
                        </td>
                    </tr>
                    <!-- /购物车一个商品的内容 -->

                {{/foreach}}

                <!-- 总计结果 -->
                <tr>
                    <td><a href="{{bzf_make_url controller='/Cart/Show/Clear'}}"
                           class="btn btn-small btn-danger">清空购物车</a></td>
                    <td colspan="3">总计</td>
                    <td class="price">{{$cartContext->getValue('shipping_fee')|bzf_money_display}}</td>
                    <td class="discount"
                        colspan="2">{{($cartContext->getValue('discount') + $cartContext->getValue('extra_discount'))|bzf_money_display}}</td>
                    <td class="price" colspan="2">{{$cartContext->getValue('order_amount')|bzf_money_display}}</td>
                </tr>
                <!-- /总计结果 -->
            {{else}}
                <tr>
                    <td colspan="9" class="well price">购物车为空</td>
                </tr>
            {{/if}}

            </tbody>
        </table>
    </div>
    <!-- /购物车显示列表 -->

    <!-- 继续购物按钮 -->
    <div class="row" style="text-align: center;">
        <a href="{{bzf_make_url controller='/' }}" class="btn btn-info">
            &lt;&lt;&nbsp;继续购物</a>
        <button type="submit" class="btn btn-success">
            去结算&nbsp;&gt;&gt;
        </button>
    </div>
    <!-- /继续购物按钮 -->

    <table class="table table-bordered table-hover" style="margin-top: 10px;">
        <!-- 分割条 -->
        <tr>
            <td class="well well-small discount">填写收件人信息</td>
        </tr>
        <!-- /分割条  -->
    </table>

    <!-- 物流信息 -->
    <div class="row">

        <div class="control-group">
            <label class="control-label">姓名*</label>

            <div class="controls">
                <input class="span2" type="text" name="consignee" value="{{$consignee|default}}"
                       data-validation-required="data-validation-required"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="address">收货地址*</label>

            <div class="controls">
                <input class="span7" type="text" name="address" value="{{$address|default}}"
                       data-validation-required="data-validation-required"/>

                <p>
                    请按照省、市、区方式填写，例如“浙江省杭州市萧山区 xxx大街 xxx号楼 111号”
                </p>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="mobile">手机*</label>

            <div class="controls">
                <input class="span2" type="text" name="mobile" value="{{$mobile|default}}"
                       pattern="1([0-9]{10})" data-validation-pattern-message="号码格式不正确"
                       data-validation-required="data-validation-required"/>

                <p>
                    手机格式例如：138xxxxxxxx （短信通知您发货进度）
                </p>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="tel">固定电话</label>

            <div class="controls">
                <input class="span2" type="text" name="tel" value="{{$tel|default}}" pattern="[0-9 -]+"
                       data-validation-pattern-message="电话格式不正确"/>

                <p>
                    固定电话，格式例如：010-6277xxxx
                </p>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="zipcode">邮编</label>

            <div class="controls">
                <input class="span2" type="text" name="zipcode" value="{{$zipcode|default}}" pattern="[0-9]+"
                       data-validation-pattern-message="邮编格式不正确"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="postscript">订单附言</label>

            <div class="controls">
                <textarea class="span7" rows="5" cols="20" name="postscript">{{$postscript|default}}</textarea>

                <p>
                    在这里填写你的额外要求，比如选择哪种颜色等等
                </p>
            </div>
        </div>

    </div>
    <!-- /物流信息 -->

    <!-- 表单结束的操作按钮 -->
    <div class="row" style="text-align: center;">
        <button type="submit" class="btn btn-success">
            去结算&nbsp;&gt;&gt;
        </button>
    </div>
    <!-- /表单结束的操作按钮 -->

    </form>
    <!-- /购物车提交表单 -->

    </div>
    <!-- /购物车页面 -->

{{/block}}
