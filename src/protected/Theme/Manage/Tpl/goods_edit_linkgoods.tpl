{{extends file='goods_edit_layout.tpl'}}
{{block name=goods_edit_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_edit_tab_left li:has(a[href='{{bzf_make_url controller='/Goods/Edit/LinkGoods' goods_id=$goods_id }}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '关联商品', link: window.location.href});
    </script>
    <!-- 左侧每个标签的具体内容 -->
    <div class="tab-content">
        <div class="tab-pane active">

            <!-- 关联商品操作 -->
            <div class="row">

                <!-- 这里是条件筛选区 -->
                <div class="row well well-small">
                    <form class="form-horizontal form-horizontal-inline" style="margin: 0px 0px 0px 0px;">
                        <div class="control-group">
                            <div class="controls">

                                <span class="input-label">商品ID</span>
                                <input id="goods_edit_linkgoods_goods_id" class="span2" type="text" pattern="[0-9]*"
                                       data-validation-pattern-message="商品ID应该是全数字"/>
                                <span class="input-label">商品名称</span>
                                <input id="goods_edit_linkgoods_goods_name" class="span4" type="text"/>

                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls" style="padding-top:8px;">

                                <span class="input-label">选择状态</span>
                                <select id="goods_edit_linkgoods_is_on_sale"
                                        class="span2 select2-simple" name="is_on_sale" data-placeholder="商品状态">
                                    <option value=""></option>
                                    <option value="1">销售中</option>
                                    <option value="0">已下线</option>
                                </select>

                                <span class="input-label">选择供货商</span>
                                <select id="goods_edit_linkgoods_suppliers_id"
                                        class="span2 select2-simple" data-placeholder="供货商列表"
                                        data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Supplier/ListSupplierIdName"}}"
                                        data-option-value-key="suppliers_id" data-option-text-key="suppliers_name">
                                    <option value=""></option>
                                </select>

                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls" style="padding-top:8px;">
                                <span class="input-label">商品分类</span>
                                <!-- 商品分类有可能层级很长 -->
                                <select id="goods_edit_linkgoods_cat_id"
                                        class="span7 select2-simple" name="cat_id"
                                        data-placeholder="选择商品分类"
                                        data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Goods/ListCategoryTree"}}"
                                        data-option-value-key="meta_id" data-option-text-key="meta_name">
                                    <option value=""></option>
                                </select>

                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls">
                                <button id="goods_edit_linkgoods_filter_goods_button" type="button"
                                        class="btn btn-success">筛选商品
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /这里是条件筛选区 -->

                <input type="hidden" id="goods_edit_linkgoods_current_goods_id" value="{{$goods_id|default}}"/>

                <!-- 筛选出来的商品在这里展示 -->
                <h5>从这里选择商品</h5>
                <select class="span10" id="goods_edit_linkgoods_filter_goods_list" multiple="multiple"
                        style="height: 250px;">
                    <option>请先从上面筛选商品</option>
                </select>
                <!-- /筛选出来的商品在这里展示 -->

                <!-- 操作按钮 -->
                <div class="row" style="text-align: center;">
                    <button id="goods_edit_linkgoods_add_link_goods_button" class="btn btn-success" type="button">添加关联
                    </button>
                    <button id="goods_edit_linkgoods_remove_link_goods_button" class="btn btn-danger" type="button">
                        取消关联
                    </button>
                </div>
                <!-- /操作按钮 -->

            </div>
            <!-- /关联商品操作 -->

            <!-- 已经关联的商品在这里展示 -->
            <h5>我关联了这些商品</h5>
            <select class="span10" id="goods_edit_linkgoods_link_goods_list" multiple="multiple"
                    style="height: 250px;">
                <option>数据加载中...</option>
            </select>
            <!-- /已经关联的商品在这里展示  -->


            <!-- 被别的商品关联了在这里展示 -->
            <h5>我“被”别的商品关联</h5>
            <select class="span10" id="goods_edit_linkgoods_link_by_goods_list" multiple="multiple"
                    style="height: 250px;">
                <option>数据加载中...</option>
            </select>
            <!-- /被别的商品关联了在这里展示  -->


            <!-- 操作按钮 -->
            <div class="row" style="text-align: center;">
                <button id="goods_edit_linkgoods_remove_link_by_goods_button" class="btn btn-danger" type="button">
                    取消 "被" 关联
                </button>
            </div>
            <!-- /操作按钮 -->


        </div>
        <!-- /左侧每个标签的具体内容 -->

    </div>
    <!-- /商品编辑页面主体内容 -->

{{/block}}

{{block name=page_js_block append}}
    <script type="text/javascript">
    /**
     * 这里的代码等 document.ready 才执行
     */
    jQuery((function (window, $) {

        /********************** goods_edit_linkgoods.tpl 根据条件筛选商品列表 *************************/
        $('#goods_edit_linkgoods_filter_goods_button').on('click', function () {
            // 根据用户选择的条件筛选商品
            var goods_id = $('#goods_edit_linkgoods_goods_id').val();
            var goods_name = $('#goods_edit_linkgoods_goods_name').val();
            var is_on_sale = $('#goods_edit_linkgoods_is_on_sale').find('option:selected').val();
            var suppliers_id = $('#goods_edit_linkgoods_suppliers_id').find('option:selected').val();
            var cat_id = $('#goods_edit_linkgoods_cat_id').find('option:selected').val();

            // 构造调用链接
            var callUrl = bZF.makeUrl('/Ajax/Goods/Search'
                    + '?goods_id=' + goods_id
                    + '&goods_name=' + encodeURI(goods_name)
                    + '&is_on_sale=' + is_on_sale
                    + '&suppliers_id=' + suppliers_id
                    + '&cat_id=' + cat_id);

            // ajax  调用
            bZF.ajaxCallGet(callUrl, function (data) {
                if (!data) {
                    bZF.showMessage('没有商品列表');
                    return;
                }

                var goodsArray = data;
                // 设置 goods_edit_linkgoods_filter_goods_list 的数据
                var optionHtml = '';
                $.each(goodsArray, function (index, elem) {
                    optionHtml += '<option value="' + elem.goods_id + '">(' + elem.goods_id + ')'
                            + elem.goods_name + '</option>';
                });
                $('#goods_edit_linkgoods_filter_goods_list').html(optionHtml);
            });
        });

        /**
         * goods_edit_linkgoods.tpl
         *
         *  取得商品的关联商品并且展示
         *
         * @param goods_id
         */
        bZF.goods_edit_linkgoods_ajaxlistlinkgoods = function (goods_id) {
            // 构造调用链接
            var callUrl = bZF.makeUrl('/Goods/Edit/LinkGoods/ajaxListLinkGoods'
                    + '?goods_id=' + goods_id);

            // ajax  调用
            bZF.ajaxCallGet(callUrl, function (data) {
                if (!data) {
                    $('#goods_edit_linkgoods_link_goods_list').html('');
                    return;
                }

                var goodsArray = data;
                // 设置 goods_edit_linkgoods_filter_goods_list 的数据
                var optionHtml = '';
                $.each(goodsArray, function (index, elem) {
                    optionHtml += '<option value="' + elem.link_id + '">(' + elem.goods_id + ')' + elem.goods_name + '</option>';
                });
                $('#goods_edit_linkgoods_link_goods_list').html(optionHtml);
            });
        };

        // 页面加载的时候自动列出关联商品列表
        if ($('#goods_edit_linkgoods_link_goods_list').size() > 0) {
            bZF.goods_edit_linkgoods_ajaxlistlinkgoods($('#goods_edit_linkgoods_current_goods_id').val());
        }

        /**
         * goods_edit_linkgoods.tpl
         *
         *  取得商品被谁关联
         *
         * @param link_goods_id
         */
        bZF.goods_edit_linkgoods_ajaxlistlinkbygoods = function (link_goods_id) {
            // 构造调用链接
            var callUrl = bZF.makeUrl('/Goods/Edit/LinkGoods/ajaxListLinkByGoods'
                    + '?link_goods_id=' + link_goods_id);

            // ajax  调用
            bZF.ajaxCallGet(callUrl, function (data) {
                if (!data) {
                    $('#goods_edit_linkgoods_link_by_goods_list').html('');
                    return;
                }

                var goodsArray = data;
                // 设置 goods_edit_linkgoods_filter_goods_list 的数据
                var optionHtml = '';
                $.each(goodsArray, function (index, elem) {
                    optionHtml += '<option value="' + elem.link_id + '">(' + elem.goods_id + ')' + elem.goods_name + '</option>';
                });
                $('#goods_edit_linkgoods_link_by_goods_list').html(optionHtml);
            });
        };

        // 页面加载的时候自动列出被关联商品列表
        if ($('#goods_edit_linkgoods_link_by_goods_list').size() > 0) {
            bZF.goods_edit_linkgoods_ajaxlistlinkbygoods($('#goods_edit_linkgoods_current_goods_id').val());
        }

        /********* goods_edit_linkgoods.tpl 取消商品关联 *********/
        $('#goods_edit_linkgoods_remove_link_goods_button').on('click', function () {

            // 对每个选中的商品依次处理
            var totalCount = $('#goods_edit_linkgoods_link_goods_list').find('option:selected').size();

            $('#goods_edit_linkgoods_link_goods_list').find('option:selected').each(function (index, elem) {

                var linkId = parseInt($(elem).val());

                if (isNaN(linkId)) {
                    bZF.showMessage('请先选择一个已经关联的商品');
                    return;
                }

                var callUrl = bZF.makeUrl('/Goods/Edit/LinkGoods/ajaxRemoveLink'
                        + '?link_id=' + linkId);

                // ajax  调用
                bZF.ajaxCallGet(callUrl, function (data) {
                    // 最后一个商品了
                    if (index == totalCount - 1) {
                        // 刷新商品关联列表
                        bZF.goods_edit_linkgoods_ajaxlistlinkgoods($('#goods_edit_linkgoods_current_goods_id').val());
                    }
                });
            });

        });

        /********* goods_edit_linkgoods.tpl 取消商品 "被" 关联 *********/
        $('#goods_edit_linkgoods_remove_link_by_goods_button').on('click', function () {

            // 对每个选中的商品依次处理
            var totalCount = $('#goods_edit_linkgoods_link_by_goods_list').find('option:selected').size();

            $('#goods_edit_linkgoods_link_by_goods_list').find('option:selected').each(function (index, elem) {

                var linkId = parseInt($(elem).val());

                if (isNaN(linkId)) {
                    bZF.showMessage('请先选择一个已经关联的商品');
                    return;
                }

                var callUrl = bZF.makeUrl('/Goods/Edit/LinkGoods/ajaxRemoveLink'
                        + '?link_id=' + linkId);

                // ajax  调用
                bZF.ajaxCallGet(callUrl, function (data) {
                    // 最后一个商品了
                    if (index == totalCount - 1) {
                        // 刷新商品关联列表
                        bZF.goods_edit_linkgoods_ajaxlistlinkbygoods($('#goods_edit_linkgoods_current_goods_id').val());
                    }
                });

            });

        });

        /********* goods_edit_linkgoods.tpl 添加商品关联 *********/
        $('#goods_edit_linkgoods_add_link_goods_button').on('click', function () {

            // 对每个选中的商品依次处理
            var totalCount = $('#goods_edit_linkgoods_filter_goods_list').find('option:selected').size();
            var currentGoodsId = $('#goods_edit_linkgoods_current_goods_id').val();

            $('#goods_edit_linkgoods_filter_goods_list').find('option:selected').each(function (index, elem) {

                var linkGoodsId = parseInt($(elem).val());

                if (isNaN(linkGoodsId)) {
                    bZF.showMessage('请先选择一个商品');
                    return;
                }

                var callUrl = bZF.makeUrl('/Goods/Edit/LinkGoods/ajaxAddLink'
                        + '?goods_id=' + currentGoodsId + '&link_goods_id=' + linkGoodsId);

                // ajax 调用
                bZF.ajaxCallGet(callUrl, function (data) {
                    // 最后一个商品了
                    if (index == totalCount - 1) {
                        // 刷新商品关联列表
                        bZF.goods_edit_linkgoods_ajaxlistlinkgoods(currentGoodsId);
                    }
                });
            });

        });

    })(window, jQuery));
    </script>
{{/block}}