{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#order_tabbar li:has(a[href='{{bzf_make_url controller='/Order/Excel'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 1, text: '批量下载订单', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        <h4>批量下载订单（限制最多 {{$max_query_record_count|default}} 条记录）</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline"
                  action="{{bzf_make_url controller='/Order/Excel/Download'}}"
                  method="GET" style="margin: 0px 0px 0px 0px;">

                <div class="control-group" style="padding-top:3px; padding-bottom:3px;">
                    <div class="controls">
                        <span class="input-label">付款时间</span>

                        <div class="input-append date datetimepicker">
                            <input id="order_excel_pay_time_start"
                                   class="span2" type="text" name="pay_time_start"
                                   value="{{$pay_time_start|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                        <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                        <div class="input-append date datetimepicker">
                            <input id="order_excel_pay_time_end"
                                   class="span2" type="text" name="pay_time_end"
                                   value="{{$pay_time_end|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>

                    </div>
                </div>

                <div class="control-group" style="padding-top:3px; padding-bottom:3px;">
                    <div class="controls">
                        <span class="input-label">额外退款时间</span>

                        <div class="input-append date datetimepicker">
                            <input id="order_excel_extra_refund_time_start"
                                   class="span2" type="text" name="extra_refund_time_start"
                                   value="{{$extra_refund_time_start|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                        <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                        <div class="input-append date datetimepicker">
                            <input id="order_excel_extra_refund_time_end"
                                   class="span2" type="text" name="extra_refund_time_end"
                                   value="{{$extra_refund_time_end|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                        <span class="comments">如果选择了额外退款时间，订单下载里面就只有退款订单</span>
                    </div>
                </div>

                <div class="control-group" style="padding-top:3px; padding-bottom:3px;">
                    <div class="controls" style="vertical-align: middle;line-height: 25px;">
                        <span class="input-label">供货商</span>
                        <select id="order_settle_supplier_select"
                                class="span3 select2-simple" name="suppliers_id"
                                data-validation-required-message="供货商不能为空"
                                data-placeholder="选择供货商"
                                data-initValue="{{$suppliers_id|default}}"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Supplier/ListOrderGoodsSupplierIdName" pay_time_start={{$pay_time_start|default}}   pay_time_end={{$pay_time_end|default}} extra_refund_time_start={{$extra_refund_time_start|default}} extra_refund_time_end={{$extra_refund_time_end|default}} }}"
                                data-option-value-key="suppliers_id" data-option-text-key="suppliers_name">
                            <option value=""></option>
                        </select>
                        &nbsp;&nbsp;
                        <button type="button" class="btn btn-info" onclick="bZF.order_excel_supplier_select();"
                                style="display: inline-block;">&lt;--筛选供货商
                        </button>
                        &nbsp;&nbsp;
                        <button type="submit" class="btn btn-success">批量下载</button>
                    </div>
                </div>

            </form>

            <form name="order_goods_excel_upload_file_form" class="form-horizontal form-horizontal-inline"
                  action="{{bzf_make_url controller='/Order/Excel/Upload'}}"
                  method="POST" enctype="multipart/form-data" style="margin: 0px 0px 0px 0px;">

                <div class="control-group" style="padding-top:3px; padding-bottom:3px;">
                    <div class="controls">
                        <label id="order_goods_excel_upload_file" class="cabinet">
                            <input id="order_goods_excel_upload_file_input" type="file" class="file" name="uploadfile"
                                   onchange="document.order_goods_excel_upload_file_form.submit();"/>
                        </label>
                        <span class="comments">把您批量下载的订单文件，填写上“快递ID”和“快递单号”从这里上传即可。注意：请不要修改文件的内容格式，否则无法识别</span>
                    </div>
                </div>

            </form>

        </div>
        <!-- /这里是条件筛选区 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {
            /**
             *  order_excel.tpl    批量下载订单页面，美化上传文件按钮
             */
            if ($('#order_goods_excel_upload_file_input').size() > 0) {
                SI.Files.stylizeById('order_goods_excel_upload_file_input');
            }

            /**
             * order_excel.tpl
             *
             * 订单批量下载页面，根据用户选择的时间段取得这个时间段里面有销售的供货商
             */
            bZF.order_excel_supplier_select = function () {
                var pay_time_start = $('#order_excel_pay_time_start').val();
                var pay_time_end = $('#order_excel_pay_time_end').val();
                var extra_refund_time_start = $('#order_excel_extra_refund_time_start').val();
                var extra_refund_time_end = $('#order_excel_extra_refund_time_end').val();

                var callUrl = bZF.makeUrl('/Ajax/Supplier/ListOrderGoodsSupplierIdName?pay_time_start=' + encodeURI(pay_time_start)
                        + '&pay_time_end=' + encodeURI(pay_time_end) + '&extra_refund_time_start=' + encodeURI(extra_refund_time_start)
                        + '&extra_refund_time_end=' + encodeURI(extra_refund_time_end));

                // ajax  调用
                bZF.ajaxCallGet(callUrl, function (data) {
                    if (!data) {
                        bZF.showMessage('没有供货商');
                        return;
                    }
                    var supplierArray = data;
                    // 设置 360tuan_cateogry_1 的数据
                    var optionHtml = '<option value=""></option>';
                    $.each(supplierArray, function (index, elem) {
                        optionHtml += '<option value="' + elem.suppliers_id + '">' + elem.suppliers_name + '</option>';
                    });
                    $('#order_settle_supplier_select').html(optionHtml);
                    //重新设置一次初始值
                    $('#order_settle_supplier_select').select2('val', null);
                    bZF.showMessage('取供货商列表成功');
                });
            };

            /************************** order_excel.tpl  订单下载 *****************************/
            $('#stat_order_refer_download_button').on('click', function () {

                var add_time_start = $('#stat_order_refer_add_time_start').val();
                var add_time_end = $('#stat_order_refer_add_time_end').val();
                var pay_time_start = $('#stat_order_refer_pay_time_start').val();
                var pay_time_end = $('#stat_order_refer_pay_time_end').val();
                var utm_source = $('#stat_order_refer_utm_source').find('option:selected').val();
                var utm_medium = $('#stat_order_refer_utm_medium').find('option:selected').val();
                var login_type = $('#stat_order_refer_login_type').find('option:selected').val();

                // 参数检查
                if (!add_time_start && !add_time_end && !pay_time_start && !pay_time_end) {
                    bZF.showMessage('必须提供最少一个查询时间');
                    return;
                }

                // 构造调用链接
                var callUrl = bZF.makeUrl('/Stat/Order/Refer/Download'
                        + '?add_time_start=' + add_time_start
                        + '&add_time_end=' + add_time_end
                        + '&pay_time_start=' + pay_time_start
                        + '&pay_time_end=' + pay_time_end
                        + '&utm_source=' + utm_source
                        + '&utm_medium=' + utm_medium
                        + '&login_type=' + login_type);

                window.open(encodeURI(callUrl));
            });

        })(window, jQuery));
    </script>
{{/block}}