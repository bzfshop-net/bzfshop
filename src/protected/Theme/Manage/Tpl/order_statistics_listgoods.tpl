{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#order_tabbar li:has(a[href='{{bzf_make_url controller='/Order/Statistics/ListGoods'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 1, text: '销售排行', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">下单时间</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2" type="text" name="create_time_start"
                                   value="{{$create_time_start|default}}"
                                   data-validation-required-message="开始时间不能为空"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                        <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2" type="text" name="create_time_end"
                                   value="{{$create_time_end|default}}"
                                   data-validation-required-message="结束时间不能为空"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>

                        <span class="input-label">来源系统</span>
                        <select class="span2 select2-simple" name="system_id" data-placeholder="全部"
                                data-initValue="{{$system_id|default}}"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Order/ListOrderSystemId"}}"
                                data-option-value-key="word" data-option-text-key="name">
                            <option value=""></option>
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

        <!-- 销售排行 -->
        <table class="table table-bordered table-hover sortable">
            <thead>
            <tr>
                <th>商品ID</th>
                <th width="25%">商品名称</th>
                <th>售出总订单</th>
                <th>未付款</th>
                <th>已付款</th>
                <th>售出总商品</th>
                <th>未付款</th>
                <th>已付款</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>

            {{if isset($sortGoodsStatisticsArray)}}
                {{foreach $sortGoodsStatisticsArray as $goodsStatisticsItem}}
                    <tr>
                        <td>{{$goodsStatisticsItem['goods_id']}}</td>
                        <td>
                            {{$goodsStatisticsItem['goods_name']}}<br/>
                            <span style="color: blue;">编辑[{{$goodsStatisticsItem['goods_admin_user_name']}}]</span>
                        </td>
                        <td>{{$goodsStatisticsItem['total_order_number']}}</td>
                        <td>{{$goodsStatisticsItem['unpay_order_number']}}</td>
                        <td>{{$goodsStatisticsItem['pay_order_number']}}</td>
                        <td>{{$goodsStatisticsItem['total_goods_number']}}</td>
                        <td>{{$goodsStatisticsItem['unpay_goods_number']}}</td>
                        <td>{{$goodsStatisticsItem['pay_goods_number']}}</td>
                        <td>
                            <a class="btn btn-mini btn-info" target="_blank"
                               href="{{bzf_make_url controller='/Order/Goods/Search' create_time_start=$create_time_start create_time_end=$create_time_end goods_admin_user_id=$goodsStatisticsItem['goods_admin_user_id'] goods_id=$goodsStatisticsItem['goods_id'] }}">订单明细</a>
                        </td>
                    </tr>
                {{/foreach}}
            {{/if}}

            </tbody>
        </table>
        <!-- /销售排行 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}