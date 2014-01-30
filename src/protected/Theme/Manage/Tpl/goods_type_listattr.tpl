{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Type/ListType'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '属性列表', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>{{$goodsType['meta_name']}}--属性列表</h4>
        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <button id="bzf_goods_attr_tree_table_button_expand" class="btn btn-success">全部展开</button>
            <button id="bzf_goods_attr_tree_table_button_collapse" class="btn btn-success">全部收起</button>
            <a href="{{bzf_make_url controller='/Goods/Type/AttrGroupCreate' typeId=$typeId}}"
               class="btn btn-info">新建组</a>
            <a href="{{bzf_make_url controller='/Goods/Type/AttrItemCreate' typeId=$typeId}}"
               class="btn btn-info">新建属性</a>
        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 列表 -->
        <table id="bzf_goods_type_attr_tree_table" class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>名称</th>
                <th>组</th>
                <th>说明</th>
                <th>排序</th>
                <th>类型</th>
                <th>数据</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($goodsAttrTreeTable)}}
                {{foreach $goodsAttrTreeTable as $goodsTypeAttr}}

                    {{if 'goods_type_attr_group' == $goodsTypeAttr['meta_type']}}
                        <!-- 一个属性组 -->
                        <tr data-tt-id="goods_type_tree_id_{{$goodsTypeAttr['meta_id']}}" class="info">
                            <td>{{$goodsTypeAttr['meta_name']}}</td>
                            <td>组</td>
                            <td>{{$goodsTypeAttr['meta_desc']|nl2br}}</td>
                            <td>{{$goodsTypeAttr['meta_sort_order']}}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <a href="{{bzf_make_url controller='/Goods/Type/AttrGroupEdit' meta_id=$goodsTypeAttr['meta_id']}}"
                                   class="btn btn-small">编辑</a>
                                <a href="{{bzf_make_url controller='/Goods/Type/AttrGroupRemove' meta_id=$goodsTypeAttr['meta_id']}}"
                                   onclick="return confirm('你确定要删除？');"
                                   class="btn btn-small btn-danger">删除</a>
                            </td>
                        </tr>
                        <!-- /一个属性组 -->
                    {{else}}
                        <!-- 一个属性 -->
                        {{if {{$goodsTypeAttr['meta_key']}} > 0}}
                            <tr data-tt-parent-id="goods_type_tree_id_{{$goodsTypeAttr['meta_key']}}"
                                data-tt-id="goods_type_tree_id_{{$goodsTypeAttr['meta_id']}}">
                                {{else}}
                            <tr data-tt-id="goods_type_tree_id_{{$goodsTypeAttr['meta_id']}}">
                        {{/if}}
                        <td>{{$goodsTypeAttr['meta_name']}}</td>
                        <td>&nbsp;</td>
                        <td>{{$goodsTypeAttr['meta_desc']|nl2br}}</td>
                        <td>{{$goodsTypeAttr['meta_sort_order']}}</td>
                        <td>{{$goodsTypeAttr['attr_type_desc']|default}}</td>
                        <td>{{$goodsTypeAttr['meta_data']|nl2br nofilter}}</td>
                        <td>
                            <a href="{{bzf_make_url controller='/Goods/Type/AttrItemEdit' meta_id=$goodsTypeAttr['meta_id']}}"
                               class="btn btn-small">编辑</a>
                            <a href="{{bzf_make_url controller='/Goods/Type/AttrItemRemove' meta_id=$goodsTypeAttr['meta_id']}}"
                               onclick="return confirm('你确定要删除？');"
                               class="btn btn-small btn-danger">删除</a>
                        </td>
                        </tr>
                        <!-- /一个属性 -->
                    {{/if}}



                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:20 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {

            /************* 树形结构 *************/
            $("#bzf_goods_type_attr_tree_table").treetable({ expandable: true, clickableNodeNames: true, initialState: 'expanded' });
            $('#bzf_goods_attr_tree_table_button_expand').click(function () {
                $("#bzf_goods_type_attr_tree_table").treetable('expandAll');
            });
            $('#bzf_goods_attr_tree_table_button_collapse').click(function () {
                $("#bzf_goods_type_attr_tree_table").treetable('collapseAll');
            });
        })(window, jQuery));
    </script>
{{/block}}