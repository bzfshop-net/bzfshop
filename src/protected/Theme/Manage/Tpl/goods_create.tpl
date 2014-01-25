{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Create'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 1, text: '新建商品', link: window.location.href});
    </script>
    <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
          action="{{bzf_make_url controller='/Goods/Edit/Edit'}}"
          style="margin: 0px 0px 0px 0px;">

        <!-- 左侧每个标签的具体内容 -->
        <div class="tab-content">

            <!-- 商品的基本信息 -->
            <div id="goods_edit_basic_info" class="tab-pane well active">

                <div class="control-group">
                    <div class="controls">
                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="前台显示给购买用户看的名称">商品名称</span>
                        <input class="span9" name="goods[goods_name]"
                               type="text"
                               data-validation-required-message="商品名称不能为空"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="后台显示报表用">商品短标题</span>
                        <input class="span4" name="goods[goods_name_short]" type="text"
                               data-validation-required-message="商品短标题不能为空"/>
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="用空格分隔每个关键词，用于网站自身搜索">商品关键词</span>
                        <input class="span3" name="goods[keywords]" type="text"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="用于SEO优化">SEO标题</span>
                        <input class="span9" name="goods[seo_title]" type="text"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="用于SEO优化">SEO关键词</span>
                        <input class="span9" name="goods[seo_keyword]" type="text"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="用于SEO优化">SEO描述</span>
                        <textarea class="span6" rows="5" cols="20" name="goods[seo_description]"
                                  maxlength="250"></textarea>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品货号</span>
                        <input class="span2" name="goods[goods_sn]" type="text"/>
                        <span class="comments">如果您不输入商品货号，系统将自动生成一个唯一的货号</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="商品发布到哪些系统里面去">系统Tag</span>
                        <!-- 商品发布到那些系统 -->
                        <select class="span9 select2-simple" multiple="multiple"
                                name="goods[system_tag_list][]"
                                data-placeholder="选择商品发布系统"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/System/ListSystem"}}"
                                data-option-value-key="system_tag" data-option-text-key="system_name">
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls" style="padding-top:8px;">
                        <span class="input-label">商品分类</span>
                        <!-- 商品分类有可能层级很长 -->
                        <select class="span9 select2-simple" name="goods[cat_id]"
                                data-validation-required-message="商品分类不能为空"
                                data-placeholder="选择商品分类" data-initValue=""
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Goods/ListCategoryTree"}}"
                                data-option-value-key="meta_id" data-option-text-key="meta_name">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品品牌</span>
                        <select class="span2 select2-simple" name="goods[brand_id]" data-placeholder="选择商品品牌"
                                data-initValue=""
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Brand/ListBrand"}}"
                                data-option-value-key="brand_id" data-option-text-key="brand_name">
                            <option value=""></option>
                        </select>
                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="如果是自己发货的产品，请先给你自己建一个供货商账号">供货商</span>
                        <select class="span2 select2-simple" name="goods[suppliers_id]"
                                data-validation-required-message="供货商不能为空"
                                data-placeholder="选择供货商" data-initValue=""
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Supplier/ListSupplierIdName"}}"
                                data-option-value-key="suppliers_id" data-option-text-key="suppliers_name">
                            <option value=""></option>
                        </select>
                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="上架商品在前台会立即出现">是否上架</span>
                        <select class="span2 select2-simple" name="goods[is_on_sale]"
                                data-value="0"
                                data-validation-required-message="商品状态不能为空"
                                data-initValue="{{$goods['is_on_sale']|default}}">
                            <option value="0">未上架</option>
                            <option value="1">已上架</option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商家备注</span>
                        <textarea class="span6" rows="5" cols="20" name="goods[seller_note]"
                                  maxlength="250"></textarea>
                        <span class="comments">仅供商家自己看的信息，限250字</span>
                    </div>
                </div>


            </div>
            <!-- /商品的基本信息 -->

        </div>
        <!-- /左侧每个标签的具体内容 -->


        <!-- 提交按钮 -->
        <div class="row" style="text-align: center;">
            <button type="submit" class="btn btn-success">保存修改</button>
        </div>
        <!-- /提交按钮 -->

    </form>
{{/block}}
