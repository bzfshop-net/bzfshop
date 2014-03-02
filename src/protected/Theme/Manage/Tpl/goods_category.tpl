{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Category'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '商品分类', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row" id="bzf_goods_category_tree_table_panel">
        <h4>商品分类</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <button id="bzf_goods_category_tree_table_button_expand" class="btn btn-success">全部展开</button>
            <button id="bzf_goods_category_tree_table_button_collapse" class="btn btn-success">全部收起</button>
            <button class="btn btn-info" onclick="bZF.show_goods_category_edit_modal();">新建分类</button>
        </div>
        <!-- /这里是条件筛选区 -->

        <table id="bzf_goods_category_tree_table" class="table table-bordered table-hover">
            <thead>
            <tr>
                <th class="well">商品分类</th>
                <th class="well">商品数量</th>
                <th class="well">分类ID</th>
                <th class="well">排序</th>
                <th class="well">是否显示</th>
                <th class="well">操作</th>
            </tr>
            </thead>
            <tbody>

            {{if isset($goodsCategoryFlatArray)}}

                {{foreach $goodsCategoryFlatArray as $goodsCategoryItem}}
                    <tr data-tt-id="bzf_goods_category_{{$goodsCategoryItem['meta_id']}}"
                        data-jsonData='{{json_encode($goodsCategoryItem)}}'
                            {{if $goodsCategoryItem['parent_meta_id'] > 0}}
                        data-tt-parent-id="bzf_goods_category_{{$goodsCategoryItem['parent_meta_id']}}"
                            {{/if}}>
                        <td class="bzf_category_title">
                            {{$goodsCategoryItem['meta_name']}}
                        </td>
                        <td>
                            {{if isset($categoryIdToGoodsCountArray[$goodsCategoryItem['meta_id']])}}
                                {{$categoryIdToGoodsCountArray[$goodsCategoryItem['meta_id']]}}
                            {{else}}&nbsp;{{/if}}
                        </td>
                        <td>{{$goodsCategoryItem['meta_id']}}</td>
                        <td>{{$goodsCategoryItem['meta_sort_order']}}</td>
                        <td>
                            {{if $goodsCategoryItem['meta_status'] > 0}}
                                <i class="icon-ok"></i>
                            {{else}}
                                <i class="icon-remove"></i>
                            {{/if}}
                        </td>
                        <td>
                            <button onclick="bZF.show_goods_category_transfer_goods_modal(this.parentNode.parentNode);"
                                    class="btn btn-mini btn-info">转移商品
                            </button>
                            <button onclick="bZF.show_goods_category_edit_modal(this.parentNode.parentNode);"
                                    class="btn btn-mini btn-success">编辑
                            </button>
                            <a href="{{bzf_make_url controller='/Goods/Category/Remove' meta_id=$goodsCategoryItem['meta_id']}}"
                               class="btn btn-mini btn-danger">删除</a>
                        </td>
                    </tr>
                {{/foreach}}

            {{/if}}

            </tbody>
        </table>

    </div>
    <!-- /页面主体内容 -->

    <!-- 商品分类编辑 modal -->
    <div id="goods_category_edit_modal" class="modal hide fade" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4>商品分类信息</h4>
        </div>
        <form class="form-horizontal form-horizontal-inline" method="POST"
              action="{{bzf_make_url controller="/Goods/Category/Edit"}}">

            <input type="hidden" name="meta_id" value=""/>

            <div class="modal-body">
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">分类名</span>
                        <input type="text" class="span2"
                               name="meta_name" value=""
                               data-validation-required-message="不能为空"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">上级分类</span>
                        <!-- 商品分类有可能层级很长 -->
                        <select class="span4 select2-simple" name="parent_meta_id"
                                data-placeholder="顶级分类" data-initValue=""
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Goods/ListCategoryTree?nocache=1"}}"
                                data-option-value-key="meta_id" data-option-text-key="meta_name">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">排序</span>
                        <input type="text" class="span1"
                               name="meta_sort_order" value=""
                               pattern="[0-9]+"
                               data-validation-required-message="不能为空"
                               data-validation-pattern-message="排序必须是数字"/>
                        <span class="comments">顺序越大排序越前</span>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">是否显示</span>
                        <select class="span2 select2-simple" name="meta_status">
                            <option value="1">显示</option>
                            <option value="0">不显示</option>
                        </select>
                    </div>
                </div>

                <!-- 属性筛选 -->
                <div id="bzf_goods_category_edit_modal_attr_filter" class="row well well-small">

                    <!-- 筛选属性 模板
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">筛选属性</span>
                            <select style="width:160px;"
                                    onchange="bZF.goods_category_edit_modal.loadTypeAttr(this.parentNode);"
                                    class="span1 select2-simple"
                                    data-placeholder="商品类型"
                                    data-ajaxCallUrl="/Ajax/GoodsType/ListType"
                                    data-option-value-key="meta_id" data-option-text-key="meta_name">
                                <option value=""></option>
                            </select>
                            <select class="span2 select2-simple"
                                    name="filter[]"
                                    data-placeholder="类型属性"
                                    data-option-value-key="meta_id" data-option-text-key="meta_name">
                                <option value=""></option>
                            </select>
                            &nbsp;&nbsp;
                            <button type="button" class="btn btn-mini btn-info"
                                    onclick="bZF.goods_category_edit_modal.addFilter();"><i
                                        class="icon-plus"></i></button>
                        </div>
                    </div>
                    -->
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-success">保存
                </button>
                <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
            </div>
        </form>
    </div>
    <!-- /商品分类编辑 modal -->

    <!-- 商品分类转移商品 modal -->
    <div id="goods_category_transfer_goods_modal" class="modal hide fade" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4>商品转移</h4>
        </div>
        <form class="form-horizontal form-horizontal-inline" method="POST"
              action="{{bzf_make_url controller="/Goods/Category/TransferGoods"}}">

            <input type="hidden" name="meta_id" value=""/>

            <div class="modal-body">

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">目标分类</span>
                        <!-- 商品分类有可能层级很长 -->
                        <select class="span4 select2-simple" name="target_meta_id"
                                data-placeholder="选择目标分类"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Goods/ListCategoryTree?nocache=1"}}"
                                data-option-value-key="meta_id" data-option-text-key="meta_name">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">转移商品</button>
                <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
            </div>
        </form>
    </div>
    <!-- /商品分类转移商品 modal -->

{{/block}}
{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {

            /************* goods_category.tpl 页面，商品分类树形结构 *************/
            $("#bzf_goods_category_tree_table").detach().treetable({ expandable: true, clickableNodeNames: true, initialState: 'collapsed' }).appendTo('#bzf_goods_category_tree_table_panel');

            $('#bzf_goods_category_tree_table_button_expand').click(function () {
                $("#bzf_goods_category_tree_table").treetable('expandAll');
            });

            $('#bzf_goods_category_tree_table_button_collapse').click(function () {
                $("#bzf_goods_category_tree_table").treetable('collapseAll');
            });

            // 编辑对话框
            bZF.show_goods_category_edit_modal = function (categoryBlock) {
                if (categoryBlock) {
                    var jsonData = $.parseJSON($(categoryBlock).attr("data-jsonData"));
                    if (jsonData.meta_data) {
                        jsonData.meta_data = $.parseJSON(jsonData.meta_data);
                    }
                    // 编辑,给对话框赋值
                    $('#goods_category_edit_modal input[name="meta_id"]').val(jsonData.meta_id);
                    $('#goods_category_edit_modal input[name="meta_name"]').val(jsonData.meta_name);
                    $('#goods_category_edit_modal input[name="meta_sort_order"]').val(jsonData.meta_sort_order);
                    $('#goods_category_edit_modal select[name="meta_status"]').select2('val', jsonData.meta_status);
                    $('#goods_category_edit_modal select[name="parent_meta_id"]').select2('val', jsonData.parent_meta_id);
                    var $filterDiv = $('#bzf_goods_category_edit_modal_attr_filter');
                    // 清除已经存在的选择
                    $('select.select2-simple', $filterDiv).select2('destroy');
                    $filterDiv.html('');
                    if (jsonData.meta_data && jsonData.meta_data.filterArray && jsonData.meta_data.filterArray.length > 0) {
                        var isFirst = true;
                        $.each(jsonData.meta_data.filterArray, function (index, data) {
                            $filterDiv.append(bZF.goods_category_edit_modal.getFilterTemplate(isFirst, data.typeId, data.attrItemId));
                            isFirst = false;
                        });
                    } else {
                        // 如果没有过滤数据，加一个空的
                        $filterDiv.append(bZF.goods_category_edit_modal.getFilterTemplate(true));
                    }
                } else {
                    // 新建
                    $('#goods_category_edit_modal input[name="meta_id"]').val(0);
                    $('#goods_category_edit_modal input[name="meta_name"]').val('');
                    $('#goods_category_edit_modal input[name="meta_sort_order"]').val(0);
                    $('#goods_category_edit_modal select[name="meta_status"]').select2('val', 1);
                    $('#goods_category_edit_modal select[name="parent_meta_id"]').select2('val', 0);
                    $(bZF.goods_category_edit_modal.getFilterTemplate(true)).appendTo('#bzf_goods_category_edit_modal_attr_filter');
                }
                // 渲染 html
                bZF.enhanceHtml($('#bzf_goods_category_edit_modal_attr_filter'));
                // 显示对话框
                $('#goods_category_edit_modal').modal({dynamic: true});
            };

            // 建立命名空间
            bZF.goods_category_edit_modal = {};

            // 生成属性筛选模板
            bZF.goods_category_edit_modal.getFilterTemplate = function (isFirst, typeId, attrItemId) {
                if (!isFirst) {
                    isFirst = false;
                }
                var attrAjaxCallUrl = '';
                if (!typeId) {
                    typeId = '';
                } else {
                    attrAjaxCallUrl = bZF.makeUrl('/Ajax/GoodsType/ListAttrItem?typeId=' + typeId);
                }
                if (!attrItemId) {
                    attrItemId = '';
                }

                var template = '<div class="control-group"><div class="controls"><span class="input-label">筛选属性</span><select style="width:160px;" name="filterTypeIdArray[]" onchange="bZF.goods_category_edit_modal.loadTypeAttr(this.parentNode);" class="span1 select2-simple" data-placeholder="商品类型" data-ajaxCallUrl="' + bZF.makeUrl('/Ajax/GoodsType/ListType') + '" data-option-value-key="meta_id" data-option-text-key="meta_name" data-initValue="' + typeId + '"> <option value=""></option></select><select class="span2 select2-simple"  name="filterAttrItemIdArray[]"  data-placeholder="类型属性"  data-option-value-key="meta_id" data-option-text-key="meta_name" data-ajaxCallUrl="' + attrAjaxCallUrl + '" data-initValue="' + attrItemId + '"><option value=""></option></select>&nbsp;&nbsp; ';

                if (isFirst) {
                    // 第一个元素，需要一个增加按钮
                    template += '<button type="button" class="btn btn-mini btn-info" onclick="bZF.goods_category_edit_modal.addFilter();"><i class="icon-plus"></i></button>';
                } else {
                    // 其它元素，需要一个删除按钮
                    template += '<button type="button" class="btn btn-mini btn-danger" onclick="bZF.goods_category_edit_modal.removeFilter(this.parentNode.parentNode);"><i class="icon-remove"></i></button>';
                }

                template += '</div></div>';

                return template;
            }

            // 增加一个过滤属性
            bZF.goods_category_edit_modal.addFilter = function () {
                var $template = $(bZF.goods_category_edit_modal.getFilterTemplate());
                bZF.enhanceHtml($template.appendTo('#bzf_goods_category_edit_modal_attr_filter'));
            }

            // 删除一个过滤属性
            bZF.goods_category_edit_modal.removeFilter = function (node) {
                $(node).remove();
            }

            // 用户选择商品类型之后，加载对应的类型属性列表
            bZF.goods_category_edit_modal.loadTypeAttr = function (node) {
                var $selectArray = $('select', node);
                var $select1 = $($selectArray[0]);// 第一级选择
                var $select2 = $($selectArray[1]);// 第二级选择
                var typeId = $select1.find('option:selected').val();
                $select2.select2('destroy');
                if (!typeId) {
                    return; // do nothing
                }
                // ajax 调用更新
                $select2.attr('data-ajaxCallUrl', bZF.makeUrl('/Ajax/GoodsType/ListAttrItem?typeId=' + typeId));
                $select2.attr('data-initValue', null);
                bZF.select2AjaxLoad($select2);
            };

            // 转移商品对话框
            bZF.show_goods_category_transfer_goods_modal = function (categoryBlock) {
                var jsonData = $.parseJSON($(categoryBlock).attr("data-jsonData"));
                $('#goods_category_transfer_goods_modal input[name="meta_id"]').val(jsonData.meta_id);
                // 显示对话框
                $('#goods_category_transfer_goods_modal').modal({dynamic: true});
            };

        })(window, jQuery));
    </script>
{{/block}}