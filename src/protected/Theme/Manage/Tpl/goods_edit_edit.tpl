{{extends file='goods_edit_layout.tpl'}}
{{block name=goods_edit_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_edit_tab_left li:has(a[href='{{bzf_make_url controller='/Goods/Edit/Edit' goods_id=$goods_id }}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 2, text: '商品信息', link: window.location.href});
    </script>
    <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
          style="margin: 0px 0px 0px 0px;">

    <!-- 左侧每个标签的具体内容 -->
    <div class="tab-content">

    <!-- 商品的基本信息 -->
    <div id="goods_edit_basic_info" class="tab-pane well active">

    <div class="control-group">
        <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="前台显示给购买用户看的名称">商品名称</span>
            <input class="span9" name="goods[goods_name]" value="{{$goods['goods_name']|default}}"
                   type="text"
                   data-validation-required-message="商品名称不能为空"/>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="后台显示报表用">商品短标题</span>
            <input class="span4" name="goods[goods_name_short]" value="{{$goods['goods_name_short']|default}}"
                   type="text"
                   data-validation-required-message="商品短标题不能为空"/>
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="用空格分隔每个关键词，用于网站自身搜索">商品关键词</span>
            <input class="span3" name="goods[keywords]" value="{{$goods['keywords']|default}}" type="text"/>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="用于SEO优化">SEO标题</span>
            <input class="span9" name="goods[seo_title]" value="{{$goods['seo_title']|default}}"
                   type="text"/>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="用于SEO优化">SEO关键词</span>
            <input class="span9" name="goods[seo_keyword]" value="{{$goods['seo_keyword']|default}}"
                   type="text"/>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="用于SEO优化">SEO描述</span>
            <textarea class="span6" rows="5" cols="20" name="goods[seo_description]"
                      maxlength="250">{{$goods['seo_description']}}</textarea>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="input-label">商品货号</span>
            <input class="span2" name="goods[goods_sn]"
                   data-validation-required-message="商品货号不能为空"
                   value="{{$goods['goods_sn']|default}}" type="text"/>
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="用于商品拣货">商品仓库</span>
            <input class="span2" type="text" name="goods[warehouse]"
                   maxlength="15"
                   value="{{$goods['warehouse']}}"/>
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="用于商品拣货">商品货架</span>
            <input class="span2" type="text" name="goods[shelf]"
                   maxlength="15"
                   value="{{$goods['shelf']}}"/>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="input-label">管理员</span>
            <select class="span2 select2-simple" name="goods[admin_user_id]" data-placeholder="管理员列表"
                    data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/AdminUser/ListUserIdName"}}"
                    data-option-value-key="user_id" data-option-text-key="user_name"
                    data-initValue="{{$goods['admin_user_id']|default}}">
                <option value=""></option>
            </select>
            <span class="comments">标明当前商品是谁编辑的</span>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="商品发布到哪些系统里面去">系统Tag</span>
            <!-- 商品发布到那些系统 -->
            <select class="span9 select2-simple" multiple="multiple"
                    name="goods[system_tag_list][]"
                    data-placeholder="选择商品发布系统" data-initValue="{{$goods['system_tag_list']|default}}"
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
                    data-placeholder="选择商品分类" data-initValue="{{$goods['cat_id']|default}}"
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
                    data-initValue="{{$goods['brand_id']|default}}"
                    data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Brand/ListBrand"}}"
                    data-option-value-key="brand_id" data-option-text-key="brand_name">
                <option value=""></option>
            </select>
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="如果是自己发货的产品，请先给你自己建一个供货商账号">供货商</span>
            <select class="span2 select2-simple" name="goods[suppliers_id]"
                    data-validation-required-message="供货商不能为空"
                    data-placeholder="选择供货商" data-initValue="{{$goods['suppliers_id']|default}}"
                    data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Supplier/ListSupplierIdName"}}"
                    data-option-value-key="suppliers_id" data-option-text-key="suppliers_name">
                <option value=""></option>
            </select>
            <span class="input-label">独立销售</span>
            <select class="span2 select2-simple" name="goods[is_alone_sale]"
                    data-initValue="{{$goods['is_alone_sale']|default}}">
                <option value="1">作为普通商品销售</option>
                <option value="0">只作为配件或赠品销售</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="input-label">精品推荐</span>
            <select class="span2 select2-simple" name="goods[is_best]"
                    data-initValue="{{$goods['is_best']|default}}">
                <option value="0">不推荐</option>
                <option value="1">推荐到精品展示</option>
            </select>
            <span class="input-label">新品推荐</span>
            <select class="span2 select2-simple" name="goods[is_new]"
                    data-initValue="{{$goods['is_new']|default}}">
                <option value="0">不推荐</option>
                <option value="1">推荐到新品展示</option>
            </select>
            <span class="input-label">热销推荐</span>
            <select class="span2 select2-simple" name="goods[is_hot]"
                    data-initValue="{{$goods['is_hot']|default}}">
                <option value="0">不推荐</option>
                <option value="1">推荐到热销展示</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="上架商品在前台会立即出现">是否上架</span>
            <select class="span2 select2-simple" name="goods[is_on_sale]"
                    data-value="{{$goods['is_on_sale']|default}}"
                    data-validation-required-message="商品状态不能为空"
                    data-initValue="{{$goods['is_on_sale']|default}}">
                <option value="0">未上架</option>
                <option value="1">已上架</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="市场上别人的售价，只是一个显示的参考价，不是实际销售价">市场价</span>
            <input class="span2" type="text" name="goods[market_price]"
                   value="{{$goods['market_price']|bzf_money_display}}" pattern="^\d+(\.\d+)?$"
                   data-validation-required-message="市场价不能为空"
                   data-validation-pattern-message="市场价无效"/>
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="本店的销售价，区别于前面的市场价">本店销售价</span>
            <input class="span2" type="text" name="goods[shop_price]"
                   value="{{$goods['shop_price']|bzf_money_display}}" pattern="^\d+(\.\d+)?$"
                   data-validation-required-message="本店销售价不能为空"
                   data-validation-pattern-message="本店销售价无效"/>
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="商品库存，如果为0则无法购买">商品库存</span>
            <input class="span2" type="text" name="goods[goods_number]"
                   value="{{$goods['goods_number']|default}}" pattern="[0-9]+"
                   data-validation-required-message="商品库存不能为空"
                   data-validation-pattern-message="商品库存无效"/>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="每个订单需要支付的快递费用">快递费用</span>
            <input class="span2" type="text" name="goods[shipping_fee]"
                   value="{{$goods['shipping_fee']|bzf_money_display}}" pattern="^\d+(\.\d+)?$"
                   data-validation-required-message="快递费用为空"
                   data-validation-pattern-message="快递费用无效"/>
                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="一次买多少件可以免邮费，0表示不免邮费">免邮数量</span>
            <input class="span2" type="text" name="goods[shipping_free_number]"
                   value="{{$goods['shipping_free_number']|default}}"
                   pattern="[0-9]+"
                   data-validation-required-message="免邮数量不能为空"
                   data-validation-pattern-message="免邮数量无效"/>
                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="库存少于这个值的时候，系统会给出警告，0表示不警告">库存警告值</span>
            <input class="span2" type="text" name="goods[warn_number]"
                   value="{{$goods['warn_number']|default}}" pattern="[0-9]+"
                   data-validation-required-message="库存警告值不能为空"
                   data-validation-pattern-message="库存警告值无效"/>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="商品成本，用于利润统计">商品供货价</span>
            <input class="span2" type="text" name="goods[suppliers_price]"
                   value="{{$goods['suppliers_price']|bzf_money_display}}" pattern="^\d+(\.\d+)?$"
                   data-validation-required-message="商品供货价不能为空"
                   data-validation-pattern-message="商品供货价无效"/>
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="快递成本，用于利润统计">供货快递费</span>
            <input class="span2" type="text" name="goods[suppliers_shipping_fee]"
                   value="{{$goods['suppliers_shipping_fee']|bzf_money_display}}"
                   pattern="^\d+(\.\d+)?$"
                   data-validation-required-message="供货快递费不能为空"
                   data-validation-pattern-message="供货快递费无效"/>
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="商品出现的排序，数字越大排序越前">商品排序</span>
            <input class="span2" type="text" name="goods[sort_order]"
                   value="{{$goods['sort_order']|default}}"
                   pattern="[0-9]+"
                   data-validation-pattern-message="商品排序非法"/>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="用于累加到购买数量中，前台显示">虚拟购买数量</span>
            <input class="span2" type="text" name="goods[virtual_buy_number]"
                   value="{{$goods['virtual_buy_number']|default}}"
                   pattern="[0-9]+"
                   data-validation-pattern-message="虚拟购买数量非法"/>
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="真实的用户下单数量">用户下单数量</span>
            <input class="span2" type="text" disabled="disabled"
                   value="{{$goods['user_buy_number']|default}}"/>
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="真实的用户支付数量">用户支付数量</span>
            <input class="span2" type="text" disabled="disabled"
                   value="{{$goods['user_pay_number']|default}}"/>
        </div>
    </div>

    <div class="control-group" style="margin-top: 10px;">
        <div class="controls">
                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="用于前台商品的展示">商品简介</span>
            <textarea class="span6 editor-html-simple" rows="5" cols="20"
                      name="goods[goods_brief]">{{$goods['goods_brief'] nofilter}}</textarea>
        </div>
    </div>

    <div class="control-group" style="margin-top: 10px;">
        <div class="controls">
            <span class="input-label">商品提示</span>
            <textarea class="span6 editor-html-simple" rows="5" cols="20"
                      name="goods[goods_notice]">{{$goods['goods_notice'] nofilter}}</textarea>
        </div>
    </div>

    <div class="control-group" style="margin-top: 10px;">
        <div class="controls">
            <span class="input-label">售后服务</span>
            <textarea class="span6 editor-html-simple" rows="5" cols="20"
                      name="goods[goods_after_service]">{{$goods['goods_after_service'] nofilter}}</textarea>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="input-label">商家备注</span>
            <textarea class="span6" rows="3" cols="20" name="goods[seller_note]"
                      maxlength="250">{{$goods['seller_note']}}</textarea>
            <span class="comments">仅供商家自己看的信息，限250字</span>
        </div>
    </div>

    <div class="control-group" style="margin-top: 15px;">
        <div class="controls">
            <span class="input-label">商品详情</span>
            <textarea id="goods_edit_goods_desc_textarea" class="span9" style="height:600px;"
                      rows="5" cols="20" data-no-validation="data-no-validation"
                      name="goods[goods_desc]">{{$goods['goods_desc'] nofilter}}</textarea>
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

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {

            /************ goods_edit_edit.tpl 商品编辑页面，商品详情编辑框的创建 ****************/

            /****** 注意，由于上传采用了 swfupload 插件，我们需要做 post 认证，否则无法上传“bzfshop_auth_cookie_key” *****/
            KindEditor.create('#goods_edit_goods_desc_textarea', {
                filterMode: true,
                themeType: 'default',
                cssData: "body {font-family: '微软雅黑', 'Microsoft Yahei', '宋体', 'songti', STHeiti, Helmet, Freesans, sans-serif;font-size: 15px; }",
                uploadJson: bZF.makeUrl('/File/KindEditor?action=upload'), // '/File/Upload'
                fileManagerJson: bZF.makeUrl('/File/KindEditor?action=manage'),
                extraFileUploadParams: {
                    bzfshop_auth_cookie_key: $.cookie(WEB_COOKIE_AUTH_KEY)
                },
                formatUploadUrl: false,
                allowFileManager: true,
                width: $('#goods_edit_goods_desc_textarea').outerWidth(false)
            });

        })(window, jQuery));
    </script>
{{/block}}