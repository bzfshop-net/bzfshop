{{extends file='goods_edit_layout.tpl'}}
{{block name=goods_edit_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_edit_tab_left li:has(a[href='{{bzf_make_url controller='/Goods/Edit/Type' goods_id=$goods_id }}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '类型属性', link: window.location.href});
    </script>
    <form id="bzf_goods_edit_type_form"
          class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
          style="margin: 0px 0px 0px 0px;">

        <!-- 左侧每个标签的具体内容 -->
        <div class="tab-content">

            <!-- 商品的类型属性设置 -->
            <div class="tab-pane well active">
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品类型</span>
                        <select id="bzf_goods_type_select"
                                class="span2 select2-simple"
                                name="type_id"
                                data-placeholder="商品类型列表"
                                data-ajaxCallUrl="{{bzf_make_url controller='/Ajax/GoodsType/ListType'}}"
                                data-option-value-key="meta_id" data-option-text-key="meta_name"
                                data-initValue="{{$goods['type_id']|default}}">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <table id="bzf_goods_attr_value_tree_table" class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th width="30%">&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

            </div>
            <!-- /商品的类型属性设置 -->

            <!-- 提交按钮 -->
            <div class="row" style="text-align: center;">
                <button type="button" class="btn btn-success" onclick="bZF.goods_edit_type.submit();">确认提交</button>
            </div>
            <!-- /提交按钮 -->

            <!-- 隐藏的 DIV 用于写入 hidden 的值 -->
            <div id="goods_edit_type_form_hidden_div" class="row" style="display: none;">
            </div>

        </div>
        <!-- /左侧每个标签的具体内容 -->

    </form>
{{/block}}

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {

            //  自己独立的命名空间
            bZF.goods_edit_type = {};

            bZF.goods_edit_type.strValue = function (str) {
                if (str) {
                    return str;
                }
                return '';
            };

            // 生成属性组
            bZF.goods_edit_type.renderAttrGroup = function (elem) {
                return '<tr class="info"><td colspan="2">' + elem.meta_name + '</td></tr>';
            };
            // 生成单选
            bZF.goods_edit_type.renderSelect = function (elem) {

                var optionValueList = elem.meta_data.split(",");
                var component = '<select class="span2 select2-simple" ' +
                        ' data-placeholder="请选择" data-no-validation="true" data-initValue="'
                        + bZF.goods_edit_type.strValue(elem.attr_item_value) + '" >';
                component += '<option value=""></option>';
                $.each(optionValueList, function (index, optionValue) {
                    component += '<option value="' + optionValue + '">' + optionValue + '</option>';
                });
                component += '</select>';

                return '<tr>' +
                        '<td style="text-align: right;">' + elem.meta_name + '</td>' +
                        '<td style="text-align: left;">' + component + '</td>' +
                        '</tr>';
            };
            // 生成手动输入-单行
            bZF.goods_edit_type.renderInput = function (elem) {

                var component = '<input type="text" class="span2" data-no-validation="true" '
                        + ' value="' + bZF.goods_edit_type.strValue(elem.attr_item_value) + '" />';

                return '<tr>' +
                        '<td style="text-align: right;">' + elem.meta_name + '</td>' +
                        '<td style="text-align: left;">' + component + '</td>' +
                        '</tr>';
            };
            // 生成手动输入-多行
            bZF.goods_edit_type.renderTextarea = function (elem) {
                var component = '<textarea class="span2" data-no-validation="true">'
                        + bZF.goods_edit_type.strValue(elem.attr_item_value) + '</textarea>';

                return '<tr>' +
                        '<td style="text-align: right;">' + elem.meta_name + '</td>' +
                        '<td style="text-align: left;">' + component + '</td>' +
                        '</tr>';
            };

            // 生成属性表
            bZF.goods_edit_type.renderGoodsAttrTable = function (goodsId, typeId) {
                var callUrl = bZF.makeUrl('/Goods/Edit/Type/ajaxListAttrValue?goods_id=' + goodsId + '&typeId=' + typeId);
                bZF.ajaxCallGet(callUrl, function (data) {
                    if (!data) {
                        // 没有数据，什么都不用操作
                        return;
                    }
                    // 挨个生成
                    $.each(data, function (index, elem) {
                        var renderComponent = '';
                        if ('goods_type_attr_group' === elem.meta_type) {
                            renderComponent = bZF.goods_edit_type.renderAttrGroup(elem);
                        } else {
                            switch (elem.meta_ename) {
                                case 'select':
                                    renderComponent = bZF.goods_edit_type.renderSelect(elem);
                                    break;
                                case 'input':
                                    renderComponent = bZF.goods_edit_type.renderInput(elem);
                                    break;
                                case 'textarea':
                                    renderComponent = bZF.goods_edit_type.renderTextarea(elem);
                                    break;
                                default:
                                    break;
                            }
                        }
                        // 把 elem 绑定
                        $renderComponent = $(renderComponent);
                        delete elem.meta_data; // 节省点内存
                        $('select,input,textarea', $renderComponent).data('dataJson', elem);
                        // 加入到结果表中
                        $('#bzf_goods_attr_value_tree_table tbody').append($renderComponent);
                    });

                    // 对页面做一次渲染
                    bZF.enhanceHtml($('#bzf_goods_attr_value_tree_table tbody'));
                });
            };

            // 商品类型选择发生变化的时候我们需要重新生成属性表
            bZF.goods_edit_type.typeChange = function (typeId) {
                // 清空属性表
                $('#bzf_goods_attr_value_tree_table tbody').html('');

                var goodsId = {{$goods['goods_id']}};
                if (!typeId) {
                    typeId = $('#bzf_goods_type_select option:selected').val();
                }

                if (isNaN(typeId) || typeId <= 0) {
                    // 用户选择商品不是任何类型
                    return;
                }

                // 生成属性值表格
                bZF.goods_edit_type.renderGoodsAttrTable(goodsId, typeId);
            };

            $('#bzf_goods_type_select').change(function () {
                bZF.goods_edit_type.typeChange();
            });

            // 第一次加载显示
            if ($('#bzf_goods_type_select').attr('data-initValue') > 0) {
                bZF.goods_edit_type.typeChange($('#bzf_goods_type_select').attr('data-initValue'));
            }

            bZF.goods_edit_type.clearHiddenValue = function () {
                $('#goods_edit_type_form_hidden_div').html('');
            };

            bZF.goods_edit_type.writeHiddenValue = function (dataJson) {
                $('#goods_edit_type_form_hidden_div').append("<input type='hidden' name='goodsAttrValueArray[]' value='" +
                        JSON.stringify(dataJson) + "' />");
            };

            bZF.goods_edit_type.submit = function () {
                // 清除之前的数据
                bZF.goods_edit_type.clearHiddenValue();

                // 收集所有数据
                $('#bzf_goods_attr_value_tree_table select').each(function (index, elem) {
                    // 取得绑定的数据
                    var $elem = $(elem);
                    var dataJson = $elem.data('dataJson');
                    if (!dataJson) {
                        // 不是我们生成的节点
                        return;
                    }
                    // 不允许有引号
                    dataJson.attr_item_value = $elem.find('option:selected').val().replace("'", '').replace('"', '');
                    bZF.goods_edit_type.writeHiddenValue(dataJson);
                });

                $('#bzf_goods_attr_value_tree_table input[type="text"], #bzf_goods_attr_value_tree_table textarea').each(function (index, elem) {
                    // 取得绑定的数据
                    var $elem = $(elem);
                    var dataJson = $elem.data('dataJson');
                    if (!dataJson) {
                        // 不是我们生成的节点
                        return;
                    }
                    // 不允许有引号
                    dataJson.attr_item_value = $elem.val().replace("'", '').replace('"', '');
                    bZF.goods_edit_type.writeHiddenValue(dataJson);
                });

                // 提交表单
                $('#bzf_goods_edit_type_form').submit();
            };

        })(window, jQuery));
    </script>
{{/block}}