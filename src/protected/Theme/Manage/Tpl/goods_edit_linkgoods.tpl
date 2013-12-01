{{extends file='goods_edit_layout.tpl'}}
{{block name=goods_edit_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script>
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
