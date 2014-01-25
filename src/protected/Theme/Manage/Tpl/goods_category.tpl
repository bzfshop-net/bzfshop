{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script>
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Category'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '商品分类', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
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
                            {{if $goodsCategoryItem['parent_meta_id'] > 0}}
                        data-tt-parent-id="bzf_goods_category_{{$goodsCategoryItem['parent_meta_id']}}"
                            {{/if}}>
                        <td class="bzf_category_title">
                            {{$goodsCategoryItem['meta_name']}}
                            <!-- 隐藏变量 -->
                            <input type="hidden" name="meta_id" value="{{$goodsCategoryItem['meta_id']}}"/>
                            <input type="hidden" name="parent_meta_id"
                                   value="{{$goodsCategoryItem['parent_meta_id']}}"/>
                            <input type="hidden" name="meta_name" value="{{$goodsCategoryItem['meta_name']}}"/>
                            <input type="hidden" name="meta_sort_order"
                                   value="{{$goodsCategoryItem['meta_sort_order']}}"/>
                            <input type="hidden" name="meta_status" value="{{$goodsCategoryItem['meta_status']}}"/>
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
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">保存</button>
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
            $("#bzf_goods_category_tree_table").treetable({ expandable: true, clickableNodeNames: true, initialState: 'collapsed' });
            $('#bzf_goods_category_tree_table_button_expand').click(function () {
                $("#bzf_goods_category_tree_table").treetable('expandAll');
            });
            $('#bzf_goods_category_tree_table_button_collapse').click(function () {
                $("#bzf_goods_category_tree_table").treetable('collapseAll');
            });

            bZF.show_goods_category_edit_modal = function (categoryBlock) {
                if (categoryBlock) {
                    // 编辑,给对话框赋值
                    $('#goods_category_edit_modal input[name="meta_id"]').val($('input[name="meta_id"]', categoryBlock).val());
                    $('#goods_category_edit_modal input[name="meta_name"]').val($('input[name="meta_name"]', categoryBlock).val());
                    $('#goods_category_edit_modal input[name="meta_sort_order"]').val($('input[name="meta_sort_order"]', categoryBlock).val());
                    $('#goods_category_edit_modal select[name="meta_status"]').select2('val', $('input[name="meta_status"]', categoryBlock).val());
                    $('#goods_category_edit_modal select[name="parent_meta_id"]').select2('val', $('input[name="parent_meta_id"]', categoryBlock).val());
                } else {
                    // 新建
                    $('#goods_category_edit_modal input[name="meta_id"]').val(0);
                    $('#goods_category_edit_modal input[name="meta_name"]').val('');
                    $('#goods_category_edit_modal input[name="meta_sort_order"]').val(0);
                    $('#goods_category_edit_modal select[name="meta_status"]').select2('val', 1);
                    $('#goods_category_edit_modal select[name="parent_meta_id"]').select2('val', 0);
                }
                // 显示对话框
                $('#goods_category_edit_modal').modal({dynamic: true});
            };

            bZF.show_goods_category_transfer_goods_modal = function (categoryBlock) {
                $('#goods_category_transfer_goods_modal input[name="meta_id"]').val($('input[name="meta_id"]', categoryBlock).val());
                // 显示对话框
                $('#goods_category_transfer_goods_modal').modal({dynamic: true});
            };


        })(window, jQuery));
    </script>
{{/block}}