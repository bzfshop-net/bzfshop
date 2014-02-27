{{extends file='goods_edit_layout.tpl'}}
{{block name=goods_edit_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_edit_tab_left li:has(a[href='{{bzf_make_url controller='/Goods/Edit/Promote' goods_id=$goods_id }}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '推广渠道', link: window.location.href});
    </script>
    <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
          style="margin: 0px 0px 0px 0px;">

        <!-- 左侧每个标签的具体内容 -->
        <div class="tab-content">

            <!-- 商品的推广设置 -->
            <div id="goods_edit_goods_promote" class="tab-pane well active">

                <!-- 分割条 -->
                <div class="row inline-divider">
                    <div class="divider"></div>
                    <label class="label label-info">360团购导航</label>
                </div>
                <!-- /分割条 -->

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品头图</span>
                        <!-- 一张图片 -->
                        <div class="thumbnail gallery-item" style="float:left;width:300px;">
                            <!-- 图片 -->
                            <div class="image-container">
                                <img id="goods_edit_promote_upload_360tuan_image"
                                     class="lazyload" width="300" height="180" style="width:300px;height:180px;"
                                     src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                     data-original="{{$goods_promote['360tuan_image']|default}}"/>
                            </div>
                        </div>
                        <!-- /一张图片 -->
                        <input id="goods_edit_promote_upload_360tuan_image_input"
                               name="goods_promote[360tuan_image]" type="hidden"
                               value="{{$goods_promote['360tuan_image']|default}}"/>
                        &nbsp;&nbsp;
                        <button id="goods_edit_promote_upload_360tuan_image_button" type="button"
                                class="btn btn-small btn-success">上传图片
                        </button>
                        <span class="comments">图片尺寸为 300x180</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="360团购导航API中的一级分类">一级分类</span>
                        <select id="goods_edit_360tuan_category_1" class="span2 select2-simple"
                                name="goods_promote[360tuan_category]"
                                data-initValue="{{$goods_promote['360tuan_category']|default:'网上购物'}}">
                        </select>

            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="360团购导航API中的子分类，尽量选择到最后一级分类">细分分类</span>
                        <select id="goods_edit_360tuan_category_2" class="span6 select2-simple"
                                name="goods_promote[360tuan_category_end]"
                                data-initValue="{{$goods_promote['360tuan_category_end']|default}}">
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品feature</span>
                        <input class="span6" name="goods_promote[360tuan_feature]" type="text"
                               value="{{$goods_promote['360tuan_feature']|default}}"/>
                        <span class="comments">关键词之间用 1 个空格分隔，空格不要多了</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="输出商品的排序，数字越大排序越前">商品排序</span>
                        <input class="span1" name="goods_promote[360tuan_sort_order]" type="text" pattern="[0-9]+"
                               value="{{$goods_promote['360tuan_sort_order']|default}}"
                               data-validation-pattern-message="商品排序无效"/>
                        <span class="comments">数字越大排序越前</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">Pin图列表</span>
                        <textarea class="span6" rows="5"
                                  name="goods_promote[360tuan_pin_images]">{{$goods_promote['360tuan_pin_images']|default}}</textarea>
                        <span class="comments">每行一个图片URL地址，请使用绝对地址</span>
                    </div>
                </div>

            </div>
            <!-- /商品的推广设置 -->

        </div>
        <!-- /左侧每个标签的具体内容 -->


        <!-- 提交按钮 -->
        <div class="row" style="text-align: center;">
            <button type="submit" class="btn btn-success">确认提交</button>
        </div>
        <!-- /提交按钮 -->

    </form>
{{/block}}

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {

            /*********** goods_edit_promote.tpl  商品推广渠道编辑页面，360团购导航商品图片 ***********/
            bZF.uploadGoodsPromoteImage('#goods_edit_promote_upload_360tuan_image_button',
                    function (clickObject, url, title, width, height, border, align) {
                        $('#goods_edit_promote_upload_360tuan_image').attr('src', url);
                        $('#goods_edit_promote_upload_360tuan_image_input').val(url);
                    });

            /************* goods_edit_promote.tpl 商品编辑推广渠道页面，用户选择 360团购 的分类 ********************/
            $('#goods_edit_360tuan_category_1').each(function () {
                var _360tuanCategoryJson = '';

                // Ajax 调用取得 360 的分类数据
                var callUrl = bZF.makeUrl('/Ajax/Proxy/Json?cache=3600&url=' + encodeURI('http://api.tuan.360.cn/open_category.php?format=json'));
                bZF.ajaxCallGet(callUrl, function (data) {
                    if (!data) {
                        bZF.showMessage('无法取得360团购商品分类');
                        return;
                    }

                    _360tuanCategoryJson = data;
                    // 设置 360tuan_cateogry_1 的数据
                    var category1Html = '';
                    $.each(_360tuanCategoryJson, function (index, elem) {
                        category1Html += '<option value="' + elem.name + '">' + elem.name + '</option>';
                    });
                    $('#goods_edit_360tuan_category_1').html(category1Html);

                    //处理初始值
                    $('#goods_edit_360tuan_category_1').select2('val', $('#goods_edit_360tuan_category_1').attr('data-initValue'));
                    //设置 Category 2
                    goods_edit_360tuan_update_category_2($('#goods_edit_360tuan_category_1').find('option:selected').val());
                    //设置 Category 2 的初始值
                    $('#goods_edit_360tuan_category_2').select2('val', $('#goods_edit_360tuan_category_2').attr('data-initValue'));
                });

                function goods_edit_360tuan_update_category_2(category1) {

                    function getCategoryHtml(optionsArray, prefix, elem) {
                        $.each(elem, function (elemIndex, elemItem) {
                            var optionItem = {};
                            optionItem.value = elemItem.name;
                            optionItem.text = prefix + elemItem.name;
                            optionsArray.push(optionItem);
                            if (elemItem.sons) {
                                getCategoryHtml(optionsArray, prefix + "---------->", elemItem.sons);
                            }
                        })
                    };

                    $.each(_360tuanCategoryJson, function (index, elem) {
                        if (elem.name != category1) {
                            return;
                        }
                        // 显示下面的分类数据
                        var optionsArray = [];
                        var category2Html = '';

                        getCategoryHtml(optionsArray, '', elem.sons);

                        $.each(optionsArray, function (optionIndex, optionItem) {
                            category2Html += '<option value="' + optionItem.value + '">' + optionItem.text + '</option>';
                        });
                        $('#goods_edit_360tuan_category_2').html(category2Html);
                    });
                };

                // 消息处理
                $('#goods_edit_360tuan_category_1').change(function () {
                    goods_edit_360tuan_update_category_2($('#goods_edit_360tuan_category_1').find('option:selected').val());
                    $('#goods_edit_360tuan_category_2').select2('val', null);
                });

            });

        })(window, jQuery));
    </script>
{{/block}}
