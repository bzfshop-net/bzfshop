{{extends file='goods_edit_layout.tpl'}}
{{block name=goods_edit_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script>
        window.bz_set_nav_status.push(function ($) {
            $("#goods_edit_tab_left li:has(a[href='{{bzf_make_url controller='/Goods/Edit/Spec' goods_id=$goods_id }}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '商品属性', link: window.location.href});
    </script>
    <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
          style="margin: 0px 0px 0px 0px;">

        <!-- 左侧每个标签的具体内容 -->
        <div class="tab-content">
            <div class="tab-pane active well">

                <!-- 根据商品类型生成的价格选择属性 -->
                <div class="row" id="goods_edit_spec_div">
                    <button type="button" class="btn btn-danger pull-right"
                            onclick="jQuery('#goods_edit_spec_div').html('');">清空设置
                    </button>
                    <!-- 规格设定 -->
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label" rel="tooltip" data-placement="top"
                                  data-title="商品选择">规格名称</span>
                            <input class="span1" name="goodsSpecNameArray[]" value="{{$goodsSpecNameArray[0]|default}}"
                                   type="text"
                                   rel="tooltip" data-placement="top"
                                   data-title="第一级选择，比如 颜色"
                                   data-validation-required-message="名称不能为空"/>
                            <input class="span1" name="goodsSpecNameArray[]" value="{{$goodsSpecNameArray[1]|default}}"
                                   type="text"
                                   rel="tooltip" data-placement="top"
                                   data-title="第二级选择，比如 尺码" style="margin-left: 20px;"/>
                            <input class="span1" name="goodsSpecNameArray[]" value="{{$goodsSpecNameArray[2]|default}}"
                                   type="text"
                                   rel="tooltip" data-placement="top"
                                   data-title="第三级选择，比如 男款/女款" style="margin-left: 20px;"/>
                            <span class="comments">我们最多支持三级选择，如果只用一级选择那后面两个就空着别填写</span>
                        </div>
                    </div>
                    <!-- /规格设定 -->

                    {{if !empty($goodsSpecValue1Array)}}
                        {{for $index=0 to count($goodsSpecValue1Array)-1 }}
                            <!-- 一个规格选择 -->
                            <div class="control-group">
                                <div class="controls">
                            <span class="input-label" rel="tooltip" data-placement="top"
                                  data-title="商品一个选择，中间不能有逗号">一个选择</span>
                                    <input class="span1" name="goodsSpecValue1Array[]"
                                           data-no-validation="true"
                                           value="{{$goodsSpecValue1Array[$index]|default}}" type="text"/>
                                    <input class="span1" name="goodsSpecValue2Array[]"
                                           data-no-validation="true"
                                           value="{{$goodsSpecValue2Array[$index]|default}}" type="text"
                                           style="margin-left: 20px;"/>
                                    <input class="span1" name="goodsSpecValue3Array[]"
                                           data-no-validation="true"
                                           value="{{$goodsSpecValue3Array[$index]|default}}" type="text"
                                           style="margin-left: 20px;"/>
                                    <span class="input-label">商品库存</span>
                                    <input class="span1" name="goodsNumberArray[]"
                                           data-no-validation="true"
                                           rel="tooltip" data-placement="top"
                                           data-title="当前规格有多少库存" style="width:50px;"
                                           value="{{$goodsNumberArray[$index]|default:'0'}}" type="text"/>
                                    <span class="input-label">属性加价</span>
                                    <input class="span1" name="goodsSpecAddPriceArray[]"
                                           data-no-validation="true"
                                           rel="tooltip" data-placement="top"
                                           data-title="这个规格加多少钱，大于等于0"
                                           style="width:30px;"
                                           value="{{$goodsSpecAddPriceArray[$index]|bzf_money_display}}" type="text"/>
                                    <span class="input-label">商品货号</span>
                                    <input class="span1" name="goodsSnArray[]" value="{{$goodsSnArray[$index]|default}}"
                                           type="text" data-no-validation="true"
                                           rel="tooltip" data-placement="top"
                                           data-title="如果这个规格对应一个另外的货号"/>
                                    <span class="input-label">关联头图</span>
                                    <img class="lazyload" src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                         data-original="{{bzf_thumb_image img_id=$imgIdArray[$index]|default}}"
                                         onclick="bZF.goods_edit_spec_select_image_modal(this.parentNode);"/>
                                    <input type="hidden" data-no-validation="true" name="imgIdArray[]"
                                           value="{{$imgIdArray[$index]|default}}"/>
                                    &nbsp;&nbsp;
                                    {{if $index == 0 }}
                                        <button type="button" class="btn btn-mini btn-info"
                                                onclick="bZF.goods_edit_spec_add_control_group(this);"><i
                                                    class="icon-plus"></i></button>
                                    {{else}}
                                        <button type="button" class="btn btn-mini btn-danger"
                                                onclick="bZF.goods_edit_spec_remove_control_group(this);"><i
                                                    class="icon-remove"></i></button>
                                        <button onclick="bZF.moveNodePrev(this.parentNode.parentNode);return false;"
                                                class="btn btn-mini btn-info" type="button">
                                            <i class="icon-arrow-up"></i>
                                        </button>
                                        <button onclick="bZF.moveNodeNext(this.parentNode.parentNode);return false;"
                                                class="btn btn-mini btn-info" type="button">
                                            <i class="icon-arrow-down"></i>
                                        </button>
                                    {{/if}}
                                </div>
                            </div>
                            <!-- /一个规格选择 -->
                        {{/for}}
                    {{else}}
                        <!-- 一个规格选择 -->
                        <div class="control-group">
                            <div class="controls">
                            <span class="input-label" rel="tooltip" data-placement="top"
                                  data-title="商品一个选择">一个选择</span>
                                <input class="span1" name="goodsSpecValue1Array[]" value="" type="text"
                                       data-validation-required-message="值不能为空"/>
                                <input class="span1" name="goodsSpecValue2Array[]" value="" type="text"
                                       style="margin-left: 20px;"/>
                                <input class="span1" name="goodsSpecValue3Array[]" value="" type="text"
                                       style="margin-left: 20px;"/>
                                <span class="input-label">商品库存</span>
                                <input class="span1" name="goodsNumberArray[]"
                                       rel="tooltip" data-placement="top"
                                       data-title="当前规格有多少库存"
                                       pattern="[0-9]+" style="width:50px;"
                                       data-validation-pattern-message="库存无效" value="-1" type="text"/>
                                <span class="input-label">属性加价</span>
                                <input class="span1" name="goodsSpecAddPriceArray[]"
                                       rel="tooltip" data-placement="top"
                                       data-title="这个规格加多少钱，大于等于0"
                                       pattern="^\d+(\.\d+)?$" style="width:30px;"
                                       data-validation-pattern-message="价格无效" value="-1" type="text"/>
                                <span class="input-label">商品货号</span>
                                <input class="span1" name="goodsSnArray[]" value="" type="text"
                                       rel="tooltip" data-placement="top"
                                       data-title="如果这个规格对应一个另外的货号"/>
                                <span class="input-label">关联头图</span>
                                <img src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                     onclick="bZF.goods_edit_spec_select_image_modal(this.parentNode);"/>
                                <input type="hidden" name="imgIdArray[]" value="0"/>
                                &nbsp;&nbsp;
                                <button type="button" class="btn btn-mini btn-info"
                                        onclick="bZF.goods_edit_spec_add_control_group(this);"><i
                                            class="icon-plus"></i></button>
                                <!-- button type="button" class="btn btn-mini btn-danger"
                                        onclick="bZF.goods_edit_spec_remove_control_group(this);"><i
                                            class="icon-remove"></i></button -->
                            </div>
                        </div>
                        <!-- /一个规格选择 -->
                    {{/if}}

                </div>
                <!-- /根据商品类型生成的价格选择属性  -->

            </div>
        </div>
        <!-- /左侧每个标签的具体内容 -->

        <!-- 提交按钮 -->
        <div class="row" style="text-align: center;">
            <button type="submit" class="btn btn-success">保存修改</button>
        </div>
        <!-- /提交按钮 -->

    </form>
    <!-- 选择商品的图片 modal -->
    <div id="goods_edit_spec_select_goods_image_modal" class="modal hide fade" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4>选择关联头图</h4>
        </div>

        <div class="modal-body" style="height: 50px;">
            <input type="hidden" name="goods_id" value="{{$goods_id}}"/>
            <select class="span2 select2-simple"
                    data-placeholder="请选择头图"
                    data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Goods/GalleryThumb" goods_id=$goods_id}}"
                    data-option-value-key="img_id" data-option-text-key="thumb_url"
                    data-option-value-image="true">
                <option value=""></option>
            </select>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-success" onclick="bZF.goods_edit_spec_select_image_confirm();">确定
            </button>
            <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
        </div>
    </div>
    <!-- /选择商品的图片 modal -->


{{/block}}
