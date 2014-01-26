{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Type/ListType'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 3, text: '属性详情', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>属性详情</h4>

        <!-- 更新商品类型的表单  -->
        <form class="form-horizontal" method="POST" action="AttrItemEdit?meta_id={{$meta_id|default}}">

            <input type="hidden" name="typeId" value="{{$typeId}}"/>

            <!-- 商品类型详细信息 -->
            <div class="row">

                <div class="control-group">
                    <label class="control-label">属性组</label>

                    <div class="controls">
                        <select class="span2 select2-simple" name="meta_key" data-placeholder="属性组列表"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/GoodsType/ListAttrGroup" typeId=$typeId}}"
                                data-option-value-key="meta_id" data-option-text-key="meta_name"
                                data-initValue="{{$meta_key|default}}">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">属性名称</label>

                    <div class="controls">
                        <input class="span3" type="text" name="meta_name" value="{{$meta_name|default}}"
                               data-validation-required-message="不能为空"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">属性描述</label>

                    <div class="controls">
                        <textarea class="span5" rows="3" cols="20"
                                  data-validation-required-message="不能为空"
                                  name="meta_desc">{{$meta_desc|default}}</textarea>
                    </div>

                </div>

                <div class="control-group">
                    <label class="control-label">&nbsp; </label>

                    <div class="controls">
                        <button type="submit" class="btn btn-success">
                            提交
                        </button>
                    </div>
                </div>

            </div>
            <!-- /商品类型详细信息 -->

        </form>
        <!-- /更新商品类型的表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}
