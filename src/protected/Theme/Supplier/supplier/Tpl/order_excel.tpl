{{extends file='order_layout.tpl'}}
{{block name=order_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#order_tabbar li:has(a[href='{{bzf_make_url controller='/Order/Excel'}}'])").addClass("active");
        });
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
                            <input class="span2" type="text" name="pay_time_start" value="{{$pay_time_start|default}}"
                                   data-validation-required-message="付款时间必须填写"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                        <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2" type="text" name="pay_time_end" value="{{$pay_time_end|default}}"
                                   data-validation-required-message="付款时间必须填写"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>

                        <span class="input-label">表格类型</span>
                        <select class="span1 select2-simple" name="excelType">
                            <option value="1">配货单</option>
                            <option value="2">拣货单</option>
                        </select>
                        <span class="input-label">快递信息</span>
                        <select class="span1 select2-simple" name="expressType" data-placeholder="快递信息">
                            <option value=""></option>
                            <option value="1">未发快递</option>
                            <option value="2">已发快递</option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-success">批量下载</button>
                        <span class="comments">说明：拣货单 用于您从仓库中拣货，配货单 用于给用户配货并且发送快递</span>
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
                        <span class="comments">把您批量下载的“配货单”，填写上“快递ID”和“快递单号”从这里上传即可。注意：请不要修改文件的内容格式，否则无法识别</span>
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
             *  订单快递单号批量上传页面，美化上传文件按钮
             */
            (function ($) {
                if ($('#order_goods_excel_upload_file_input').length > 0) {
                    SI.Files.stylizeById('order_goods_excel_upload_file_input');
                }
            })(jQuery);

        })(window, jQuery));
    </script>
{{/block}}