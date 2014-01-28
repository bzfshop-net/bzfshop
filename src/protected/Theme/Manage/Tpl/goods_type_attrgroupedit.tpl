{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Type/ListType'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 3, text: '属性组详情', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>属性组详情</h4>

        <!-- 更新商品类型的表单  -->
        <form class="form-horizontal" method="POST" action="AttrGroupEdit?meta_id={{$meta_id|default}}">

            <input type="hidden" name="typeId" value="{{$typeId}}"/>

            <!-- 商品类型详细信息 -->
            <div class="row">

                <div class="control-group">
                    <label class="control-label">属性组名称</label>

                    <div class="controls">
                        <input class="span3" type="text" name="meta_name" value="{{$meta_name|default}}"
                               data-validation-required-message="不能为空"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">属性组描述</label>

                    <div class="controls">
                        <textarea class="span5" rows="3" cols="20"
                                  data-validation-required-message="不能为空"
                                  name="meta_desc">{{$meta_desc|default}}</textarea>
                    </div>

                </div>

                <div class="control-group">
                    <label class="control-label">排序</label>

                    <div class="controls">
                        <input class="span1" type="text" name="meta_sort_order"
                               value="{{$meta_sort_order|default}}"
                               pattern="[0-9]+"
                               data-validation-pattern-message="排序非法"
                               rel="tooltip" data-placement="top"
                               data-title="数字越大排序越前"/>
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
