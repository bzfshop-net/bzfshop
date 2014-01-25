{{extends file='article_layout.tpl'}}
{{block name=article_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#article_tabbar li:has(a[href='{{bzf_make_url controller='/Article/Category'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '分类详情', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>分类详情</h4>

        <!-- 更新管理员信息的表单  -->
        <form class="form-horizontal" method="POST" action="Edit?meta_id={{$meta_id|default}}">

            <!-- 管理员详细信息 -->
            <div class="row">

                <div class="control-group">
                    <label class="control-label">分类名</label>

                    <div class="controls">
                        <input class="span3" type="text" name="meta_name" value="{{$meta_name|default}}"
                               data-validation-required="data-validation-required"/>
                        <span class="comments">(用于列表显示)</span>
                    </div>
                </div>


                <div class="control-group">
                    <label class="control-label">排序</label>

                    <div class="controls">
                        <input class="span1" type="text" name="meta_sort_order"
                               value="{{$meta_sort_order|default}}"/>
                        <span class="comments">数字越大排序越前</span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">备注</label>

                    <div class="controls">
                        <textarea class="span5" rows="3" cols="40"
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
            <!-- /管理员详细信息 -->

        </form>
        <!-- /更新管理员信息的表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}