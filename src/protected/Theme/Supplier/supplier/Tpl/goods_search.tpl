{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Search'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 0, text: '商品列表', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>{{if isset($is_on_sale)}} {{if 1 == $is_on_sale}}在线商品{{else}}下架商品{{/if}} {{else}}全部商品{{/if}}</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">

                <div class="control-group">
                    <div class="controls">

                        <span class="input-label">商品ID</span>
                        <input class="span2" type="text" pattern="[0-9]*" data-validation-pattern-message="商品ID应该是全数字"
                               name="goods_id" value="{{$goods_id|default}}"/>

                        <span class="input-label">商品货号</span>
                        <input class="span2" type="text" name="goods_sn" value="{{$goods_sn|default}}"/>

                        <span class="input-label">商品名称</span>
                        <input class="span2" type="text" name="goods_name" value="{{$goods_name|default}}"/>

                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">

                        <span class="input-label">仓库</span>
                        <input class="span2" type="text" name="warehouse" value="{{$warehouse|default}}"/>

                        <span class="input-label">货架</span>
                        <input class="span2" type="text" name="shelf" value="{{$shelf|default}}"/>

                        <span class="input-label">选择状态</span>
                        <select class="span2 select2-simple" name="is_on_sale" data-placeholder="商品状态"
                                data-initValue="{{$is_on_sale|default}}">
                            <option value=""></option>
                            <option value="1">销售中</option>
                            <option value="0">已下线</option>
                        </select>

                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-success">查询</button>
                    </div>
                </div>

            </form>
        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 商品列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>商品ID</th>
                <th width="15%">商品图片</th>
                <th width="30%">商品名称</th>
                <th>价格</th>
                <th>库存剩余</th>
                <th>下单数量</th>
                <th>付款数量</th>
                <th width="5%">状态</th>
                <th width="8%">操作</th>
            </tr>
            </thead>
            <tbody>

            {{if isset($goodsArray)}}
                {{foreach $goodsArray as $goodsItem}}

                    <!-- 一个商品 -->
                    {{if 0 == $goodsItem['is_on_sale']}}
                        <tr class="error">
                            {{else}}
                        <tr>
                    {{/if}}
                    <td>{{$goodsItem['goods_id']}}</td>
                    <td>
                        <a rel="clickover" data-placement="top" href="#"
                           data-content="{{bzf_goods_view_toolbar goods_id=$goodsItem['goods_id']  system_tag_list=$goodsItem['system_tag_list']}}">
                            <img class="lazyload"
                                 width="150" style="width:150px;height:auto;"
                                 src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                 data-original="{{bzf_goods_thumb_image goods_id=$goodsItem['goods_id']}}"/>
                        </a>
                    </td>
                    <td>
                        {{$goodsItem['goods_name_short']}}
                        <br/>
                        货号[{{$goodsItem['goods_sn']}}]
                        {{if !empty($goodsItem['warehouse'])}}
                            <br/>
                            {{$goodsItem['warehouse']}}&nbsp;|&nbsp;{{$goodsItem['shelf']}}
                        {{/if}}
                    </td>
                    <td><p>供货价：{{$goodsItem['suppliers_price']|bzf_money_display}}元</p>

                        <p>快递费：{{$goodsItem['suppliers_shipping_fee']|bzf_money_display}}元</p></td>
                    <td>
                        {{if empty($goodsItem['goods_spec'])}}
                            {{$goodsItem['goods_number']}}
                        {{else}}
                            {{foreach $goodsItem['goods_spec'] as $specStr => $specDataArray }}
                                {{$specStr}}&nbsp;:&nbsp;{{$specDataArray['goods_number']|default:'0'}}
                                <br/>
                            {{/foreach}}
                        {{/if}}
                    </td>
                    <td>{{$goodsItem['user_buy_number']}}</td>
                    <td>{{$goodsItem['user_pay_number']}}</td>
                    <td>
                        {{foreach $goodsItem['system_array'] as $goodsSystemName}}
                            <label class="label label-success">{{$goodsSystemName}}</label>
                        {{/foreach}}
                        <br/>{{if $goodsItem['is_on_sale'] >= 1}}销售中{{else}}已下线{{/if}}
                    </td>
                    <td>
                        <button class="btn btn-small"
                                onclick='bZF.Goods_ListGoods_Statistics({{$goodsItem['goods_id']}})'>销售统计
                        </button>
                        <a target="_blank" class="btn btn-small"
                           href="{{bzf_make_url controller='/Order/Excel/Download' goods_id=$goodsItem['goods_id'] excelType="1"}}">订单下载</a>
                    </td>
                    </tr><!-- /一个商品 -->

                {{/foreach}}
            {{/if}}

            </tbody>
        </table>
        <!-- /商品列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->

    <!-- 商品统计详情对话框 -->
    <div id="goods_statistics_dialog" class="modal hide fade">
    </div>
    <!-- 商品统计详情对话框 -->

{{/block}}

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {
            /******* 商品列表页面显示商品统计数据的调用 ***********/
            bZF.Goods_ListGoods_Statistics = function (goods_id) {
                var ajaxCallUrl = bZF.makeUrl('/Goods/Search/Statistics');
                $('#goods_statistics_dialog').load(ajaxCallUrl + '?goods_id=' + goods_id, function () {
                    $('#goods_statistics_dialog').modal({dynamic: true});
                });
            };
        })(window, jQuery));
    </script>
{{/block}}