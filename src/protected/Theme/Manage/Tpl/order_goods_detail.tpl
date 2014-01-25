{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#order_tabbar li:has(a[href='{{bzf_make_url controller='/Order/Goods/Search'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 2, text: '售出商品详情', link: window.location.href});
    </script>
    <!-- /用 JS 设置页面的导航菜单 -->

    {{if isset($orderGoods) && isset($orderInfo)}}

        <!-- 订单详情 -->
        <div class="row">
        <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th width="11%">&nbsp;</th>
            <th><a class="btn btn-small"
                   href="{{bzf_make_url controller="/Order/Order/Detail" order_id=$orderInfo['order_id'] }}">查看整个订单</a>
            </th>
            <th width="11%">&nbsp;</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="labelkey">商品标题</td>
            <td id="order_detail_goods_name"
                class="labelvalue">
                {{$orderGoods['goods_name']}}<br/>
                <span style="color: blue;">编辑[{{$orderGoods['goods_admin_user_name']}}]</span>
            </td>
            <td class="labelkey">商品图片</td>
            <td id="order_detail_goods_name"
                class="labelvalue">
                <a href="#" rel="clickover" data-placement="top"
                   data-content="{{bzf_goods_view_toolbar goods_id=$orderGoods['goods_id']}}">
                    <img class="lazyload" width="150" style="width:150px;height:auto;"
                         src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                         data-original="{{bzf_goods_thumb_image goods_id=$orderGoods['goods_id']}}"/>
                </a>
            </td>
        </tr>
        <tr>
            <td class="labelkey">订单号</td>
            <td id="order_detail_order_id" class="labelvalue">
                {{$orderGoods['order_id']}}--{{$orderGoods['rec_id']}}
            </td>
            <td class="labelkey">订单流水号</td>
            <td class="labelvalue">{{$orderInfo['order_sn']|default}}</td>
        </tr>
        <tr>
            <td class="labelkey">商品选项</td>
            <td id="order_detail_goods_number" class="labelvalue">
                {{$orderGoods['goods_sn']}}&nbsp;:&nbsp;{{$orderGoods['goods_attr']}}
            </td>
            <td class="labelkey">商品状态</td>
            <td id="order_detail_order_status" class="labelvalue">
                {{$orderGoods['order_goods_status_desc']}}&nbsp;&nbsp;
                <!-- 已付款、申请退款、退款中 可以设置退款 -->
                {{if (1 == $orderGoods['order_goods_status']
                || 2 == $orderGoods['order_goods_status'])
                && 0 == $orderGoods['settle_id']}} <!-- 已经结算的订单不能退款 -->
                    <button type="button" class="btn btn-mini"
                            onclick="jQuery('#order_goods_detail_modal_set_refund').modal();">
                        申请退款
                    </button>
                {{/if}}
            </td>
        </tr>
        <tr>
            <td class="labelkey">商品单价</td>
            <td class="labelvalue price">{{$orderGoods['shop_price']|bzf_money_display}}</td>
            <td class="labelkey">购买数量</td>
            <td id="order_detail_goods_number" class="labelvalue">{{$orderGoods['goods_number']}}</td>
        </tr>
        <tr>
            <td class="labelkey">商品总价</td>
            <td class="labelvalue price">{{$orderGoods['goods_price']|bzf_money_display}}</td>
            <td class="labelkey">总价说明</td>
            <td class="labelvalue">{{$orderGoods['goods_price_note']}}</td>
        </tr>
        <tr>
            <td class="labelkey">商品优惠</td>
            <td class="labelvalue discount">{{$orderGoods['discount']|bzf_money_display}}</td>
            <td class="labelkey">优惠说明</td>
            <td class="labelvalue">{{$orderGoods['discount_note']}}</td>
        </tr>

        <tr>
            <td class="labelkey">
                <span rel="tooltip" data-placement="top"
                      data-title="这里可以手动设置额外的优惠给用户">
                    额外优惠金额
                </span>
            </td>
            <td class="labelvalue discount">
                {{$orderGoods['extra_discount']|bzf_money_display}}
                <!-- 用户没有付款的情况下可以给用户额外的优惠 -->
                {{if 0 == $orderGoods['order_goods_status']}}
                    &nbsp;&nbsp;
                    <button type="button" class="btn btn-mini"
                            onclick="jQuery('#order_goods_detail_modal_set_extra_discount').modal();">
                        给予额外优惠
                    </button>
                {{/if}}
            </td>
            <td class="labelkey">额外优惠说明</td>
            <td class="labelvalue">{{$orderGoods['extra_discount_note']}}</td>
        </tr>

        <tr>
            <td class="labelkey">商品邮费</td>
            <td class="labelvalue price">{{$orderGoods['shipping_fee']|bzf_money_display}}</td>
            <td class="labelkey">邮费说明</td>
            <td class="labelvalue">{{$orderGoods['shipping_fee_note']}}</td>
        </tr>

        <tr>
            <td class="labelkey">
                <span rel="tooltip" data-placement="top"
                      data-title="我们给顾客的退款金额">顾客退款</span>
            </td>
            <td class="labelvalue discount">{{$orderGoods['refund']|bzf_money_display}}</td>
            <td class="labelkey">退款说明</td>
            <td class="labelvalue">{{$orderGoods['refund_note']}}</td>
        </tr>

        <tr>
            <td class="labelkey">
                <span rel="tooltip" data-placement="top"
                      data-title="供货商给我们的退款金额，用于和供货商结算">供货商退款</span>
            </td>
            <td class="labelvalue discount">{{$orderGoods['suppliers_refund']|bzf_money_display}}</td>
            <td class="labelkey">退款说明</td>
            <td class="labelvalue">{{$orderGoods['suppliers_refund_note']}}</td>
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
            <td class="labelkey">下单时间</td>
            <td class="labelvalue">
                <span id="order_detail_add_time">{{$orderInfo['add_time']|bzf_localtime}}</span>
            </td>
            <td class="labelkey">支付时间</td>
            <td class="labelvalue">
                <span id="order_detail_pay_time">{{$orderInfo['pay_time']|bzf_localtime}}</span>
            </td>
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

        <!-- 分割符 -->
        <tr>
            <td colspan="4" class="well well-small">&nbsp;</td>
        </tr>
        <!-- /分割符 -->

        <tr>
            <td class="labelkey">用户信息</td>
            <td colspan="3" class="labelvalue">
                <a href="#"
                   onclick="bZF.Account_User_ajaxDetail({{$userInfo['user_id']}});return false;">{{$userInfo['user_name']|default}}</a>
                |{{$userInfo['email']|default}}</td>
        </tr>

        <tr>
            <td class="labelkey">收件人</td>
            <td id="order_detail_consignee" class="labelvalue">{{$orderInfo['consignee']}}</td>
            <td class="labelkey">手机号</td>
            <td id="order_detail_mobile" class="labelvalue">
                {{$orderInfo['mobile']}}&nbsp;&nbsp;
                <a href="{{bzf_mobile_query_url mobile=$orderInfo['mobile']}}" class="btn btn-small" target="_blank">
                    查询
                </a>
            </td>
        </tr>
        <tr>
            <td class="labelkey">收件地址</td>
            <td id="order_detail_address" class="labelvalue">{{$orderInfo['address']}}</td>
            <td class="labelkey">电话</td>
            <td class="labelvalue">{{$orderInfo['tel']}}</td>
        </tr>
        <tr>
            <td class="labelkey">用户留言</td>
            <td id="order_detail_postscript" class="labelvalue">{{$orderInfo['postscript']}}</td>
            <td class="labelkey">邮编</td>
            <td id="order_detail_address" class="labelvalue">{{$orderInfo['zipcode']}}</td>
        </tr>

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

        <!-- 分割符 -->
        <tr>
            <td colspan="4" class="well well-small">&nbsp;</td>
        </tr>
        <!-- /分割符 -->

        <tr>
            <td class="labelkey">结算状态</td>
            <td class="labelvalue">
                {{if $orderGoods['settle_id'] > 0}}
                    <a href="#"
                       onclick="bZF.Order_Settle_ajaxDetail({{$orderGoods['settle_id']}});return false;">
                        [已结算：{{$orderGoods['settle_id']}}]
                    </a>
                {{/if}}
            </td>
            <td class="labelkey">供货商</td>
            <td class="labelvalue">[{{$orderGoods['suppliers_id']}}]{{$supplierInfo['suppliers_name']}}</td>
        </tr>

        <tr>
            <td class="labelkey">商家备注</td>
            <td class="labelvalue discount">{{$goods['seller_note']}}</td>
            <td class="labelkey">仓库货架</td>
            <td class="labelvalue">{{$orderGoods['warehouse']}}&nbsp;|&nbsp;{{$orderGoods['shelf']}}</td>
        </tr>

        <tr>
            <td style="text-align: right;padding-right: 10px;">
                <a href="#" ref="popover" data-trigger="hover" data-placement="right"
                   data-content="异常流程记录，退款金额超过订单本身金额，已经结算完成的订单还需要退款，都可以记录在这里，后面需要人工自己处理">
                    额外退款
                </a>
            </td>
            <td class="labelvalue">
                {{$orderGoods['extra_refund']|bzf_money_display}}&nbsp;&nbsp;
                {{if $orderGoods['order_goods_status'] > 0}}
                    <button type="button" class="btn btn-mini"
                            onclick="jQuery('#order_goods_detail_modal_set_extra_refund').modal();">
                        额外退款
                    </button>
                {{/if}}
                {{$orderGoods['extra_refund_time']|bzf_localtime}}
            </td>
            <td class="labelkey">退款说明</td>
            <td class="labelvalue">{{$orderGoods['extra_refund_note']}}</td>
        </tr>

        <tr>
            <td class="labelkey">供货单价</td>
            <td class="labelvalue">
                {{$orderGoods['suppliers_price']|bzf_money_display}}&nbsp;&nbsp;
                <button type="button" class="btn btn-mini"
                        onclick="jQuery('#order_goods_detail_modal_set_suppliers_price').modal();">
                    修改供货价
                </button>
            </td>
            <td class="labelkey">供货快递费</td>
            <td class="labelvalue">{{$orderGoods['suppliers_shipping_fee']|bzf_money_display}}
                &nbsp;&nbsp;{{$orderGoods['suppliers_shipping_fee_note']}}</td>
        </tr>

        <tr>
            <td class="labelkey">客服备注</td>
            <td class="labelvalue">
                {{$orderGoods['memo']|escape|nl2br nofilter}}
                <br/>
                <button type="button" class="btn btn-mini"
                        onclick="jQuery('#order_goods_detail_modal_set_memo').modal();">
                    修改备注
                </button>
            </td>
            <td class="labelkey">快递信息</td>
            <td class="labelvalue">
                {{if $orderGoods['shipping_id'] > 0}}
                    {{$orderGoods['shipping_name']}}:{{$orderGoods['shipping_no']}}
                    <br/>
                    <br/>
                    <a target="_blank" class="btn btn-mini"
                       href="{{bzf_express_query_url expressName=$orderGoods['shipping_name'] expressNo=$orderGoods['shipping_no']}}">查询快递</a>
                {{/if}}
                &nbsp;&nbsp;
                <button type="button" class="btn btn-mini"
                        onclick="jQuery('#order_goods_detail_modal_set_shipping_no').modal();">
                    设置快递
                </button>
            </td>
        </tr>

        </tbody>
        </table>
        </div>
        <!-- /订单详情 -->

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

        <!--  ======================================  这里往下是一系列的操作 Modal ===================================== -->

        <!-- 给予额外优惠 modal -->
        <div id="order_goods_detail_modal_set_extra_discount" class="modal hide fade" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4>给予额外优惠</h4>
            </div>
            <form class="form-horizontal form-horizontal-inline" method="POST"
                  action="Update?action=set_extra_discount">
                <input type="hidden" name="rec_id" value="{{$orderGoods['rec_id']}}"/>

                <div class="modal-body">
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">优惠金额</span>
                            <input type="text" name="extra_discount"
                                   value="{{$orderGoods['extra_discount']|bzf_money_display}}"
                                   class="span1" pattern="^\d+(\.\d+)?$" max="{{$maxExtraDiscount|bzf_money_display}}"
                                   data-validation-pattern-message="额外优惠无效"/>
                            <span class="comments">最大优惠允许 {{$maxExtraDiscount|bzf_money_display}} 元</span>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">优惠说明</span>
                            <textarea name="extra_discount_note" class="span4" rows="5"
                                      data-validation-required-message="优惠说明不能为空"
                                      cols="20">{{$orderGoods['extra_discount_note']}}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">确定</button>
                    <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
                </div>
            </form>
        </div>
        <!-- /给予额外优惠 modal -->

        <!-- 设置供货价 modal -->
        <div id="order_goods_detail_modal_set_suppliers_price" class="modal hide fade" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4>设置供货价</h4>
            </div>
            <form class="form-horizontal form-horizontal-inline" method="POST"
                  action="Update?action=set_suppliers_price">
                <input type="hidden" name="rec_id" value="{{$orderGoods['rec_id']}}"/>

                <div class="modal-body">
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">供货单价</span>
                            <input type="text"
                                   name="suppliers_price" value="{{$orderGoods['suppliers_price']|bzf_money_display}}"
                                   class="span1" pattern="^\d+(\.\d+)?$"
                                   data-validation-pattern-message="供货单价无效"
                                   data-validation-required-message="供货单价不能为空"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">供货快递</span>
                            <input type="text" name="suppliers_shipping_fee"
                                   value="{{$orderGoods['suppliers_shipping_fee']|bzf_money_display}}"
                                   class="span1" pattern="^\d+(\.\d+)?$"
                                   data-validation-pattern-message="供货快递无效"
                                   data-validation-required-message="供货快递不能为空"/>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">确定</button>
                    <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
                </div>
            </form>
        </div>
        <!-- /设置供货价 modal -->

        <!-- 客服备注 modal -->
        <div id="order_goods_detail_modal_set_memo" class="modal hide fade" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4>客服备注</h4>
            </div>
            <form class="form-horizontal form-horizontal-inline" method="POST"
                  action="Update?action=set_memo">
                <input type="hidden" name="rec_id" value="{{$orderGoods['rec_id']}}"/>

                <div class="modal-body">
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">客服备注</span>
                            <textarea name="memo" class="span4" rows="5"
                                      cols="20">{{$orderGoods['memo']}}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">确定</button>
                    <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
                </div>
            </form>
        </div>
        <!-- /客服备注 modal -->

        <!-- 设置快递信息 modal -->
        <div id="order_goods_detail_modal_set_shipping_no" class="modal hide fade" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4>设置快递信息</h4>
            </div>
            <form class="form-horizontal form-horizontal-inline" method="POST"
                  action="Update?action=set_shipping_no">
                <input type="hidden" name="rec_id" value="{{$orderGoods['rec_id']}}"/>

                <div class="modal-body">
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">快递公司</span>
                            <select class="span2 select2-simple" name="shipping_id"
                                    data-placeholder="请选择快递"
                                    data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Express/ListExpress"}}"
                                    data-option-value-key="meta_id" data-option-text-key="meta_name"
                                    data-initValue="{{$orderGoods['shipping_id']}}">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">快递单号</span>
                            <input type="text" class="span2" name="shipping_no"
                                   value="{{$orderGoods['shipping_no']}}"/>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">确定</button>
                    <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
                </div>
            </form>
        </div>
        <!-- /设置快递信息 modal -->

        <!-- 退款 modal -->
        <div id="order_goods_detail_modal_set_refund" class="modal hide fade" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4>申请退款</h4>
            </div>
            <form class="form-horizontal form-horizontal-inline" method="POST"
                  action="Update?action=set_refund">
                <input type="hidden" name="rec_id" value="{{$orderGoods['rec_id']}}"/>

                <div class="modal-body">
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label" rel="tooltip" data-placement="top"
                                  data-title="我们给顾客的退款金额">顾客金额</span>
                            <input type="text"
                                   name="refund" value="{{$orderGoods['refund']|bzf_money_display}}"
                                   class="span1" pattern="^\d+(\.\d+)?$" max="{{$maxRefund|bzf_money_display}}"
                                   data-validation-pattern-message="退款金额无效"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label" rel="tooltip" data-placement="top"
                                  data-title="我们给顾客的退款说明">顾客说明</span>
                            <textarea name="refund_note" class="span4" rows="5"
                                      data-validation-required-message="退款说明不能为空"
                                      cols="20">{{$orderGoods['refund_note']}}</textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label" rel="tooltip" data-placement="top"
                                  data-title="供货商给我们的退款金额，用于和供货商结算">供货商金额</span>
                            <input type="text"
                                   name="suppliers_refund" value="{{$orderGoods['suppliers_refund']|bzf_money_display}}"
                                   class="span1" pattern="^\d+(\.\d+)?$"
                                   data-validation-pattern-message="退款金额无效"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label" rel="tooltip" data-placement="top"
                                  data-title="供货商给我们的退款说明，用于和供货商结算">供货商说明</span>
                            <textarea name="suppliers_refund_note" class="span4" rows="5"
                                      data-validation-required-message="退款说明不能为空"
                                      cols="20">{{$orderGoods['suppliers_refund_note']}}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">确定</button>
                    <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
                </div>
            </form>
        </div>
        <!-- /退款 modal -->

        <!-- 额外退款 modal -->
        <div id="order_goods_detail_modal_set_extra_refund" class="modal hide fade" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4>商品额外退款</h4>
            </div>
            <form class="form-horizontal form-horizontal-inline" method="POST"
                  action="Update?action=set_extra_refund">
                <input type="hidden" name="rec_id" value="{{$orderGoods['rec_id']}}"/>

                <div class="modal-body">

                    <div class="row">
                        <p>注意：额外退款只应用于“非正常退款流程”，正常退款请用“申请退款”</p>

                        <p>
                            非正常退款包括：
                        </p>
                        <ul>
                            <li>需要退款的金额大于用户订单本身金额，用户买了100元的东西，你却要退款200元给用户</li>
                            <li>订单已经和供货商结算完毕了，用户又要求退款</li>
                            <li>其它任何不符合逻辑但是你确实需要操作的情况</li>
                        </ul>
                        <p>非正常退款这里只是一个记录而已，所有真实的退款都需要你自己手动去操作</p>
                    </div>

                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">退款金额</span>
                            <input type="text"
                                   name="extra_refund" value="{{$orderGoods['extra_refund']|bzf_money_display}}"
                                   class="span1" pattern="^\d+(\.\d+)?$"
                                   data-validation-pattern-message="退款金额无效"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">退款说明</span>
                            <textarea name="extra_refund_note" class="span4" rows="5"
                                      data-validation-required-message="退款说明不能为空"
                                      cols="20">{{$orderGoods['extra_refund_note']}}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">确定</button>
                    <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
                </div>
            </form>
        </div>
        <!-- /额外退款 modal -->

        <!-- 结算记录设置 modal -->
        <div id="order_settle_listsettle_modal_update" class="modal hide fade" tabindex="-1" role="dialog"
             aria-hidden="true">
        </div>
        <!-- /结算记录设置 modal -->

        <!-- 用户详情对话框 -->
        <div id="user_detail_dialog" class="modal hide fade">
        </div>
        <!-- 用户详情对话框 -->

    {{/if}}


{{/block}}
