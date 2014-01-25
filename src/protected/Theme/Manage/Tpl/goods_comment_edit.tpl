{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Comment/ListComment'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '用户评价详情', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>用户评价详情</h4>

        <!-- 更新用户评价的表单  -->
        <form class="form-horizontal" method="POST" action="Edit?comment_id={{$comment_id|default}}">

            <!-- 用户评价详细信息 -->
            <div class="row">

                <div class="control-group">
                    <label class="control-label">创建时间</label>

                    <div class="controls">
                        <input class="span3" type="text" value="{{$create_time|bzf_localtime}}" disabled="disabled"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">用户</label>

                    <div class="controls">
                        <input class="span3" type="text" name="user_name" value="{{$user_name|default}}"
                               data-validation-required-message="不能为空"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">商品ID</label>

                    <div class="controls">
                        <input class="span3" type="text" name="goods_id" value="{{$goods_id|default:'0'}}"
                               pattern="[0-9]+" data-validation-pattern-message="商品ID必须是全数字"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">商品选择</label>

                    <div class="controls">
                        <input class="span3" type="text" name="goods_attr" value="{{$goods_attr|default}}"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">总货款</label>

                    <div class="controls">
                        <input class="span3" type="text" name="goods_price" value="{{$goods_price|bzf_money_display}}"
                               pattern="^\d+(\.\d+)?$"
                               data-validation-required-message="不能为空"
                               data-validation-pattern-message="总货款无效"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">购买数量</label>

                    <div class="controls">
                        <input class="span3" type="text" name="goods_number" value="{{$goods_number|default:'0'}}"
                               min=1 pattern="[0-9]+" data-validation-pattern-message="购买数量必须是数字"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">是否显示</label>

                    <div class="controls">
                        <select class="span2 select2-simple" name="is_show"
                                data-initValue="{{$is_show|default}}">
                            <option value="0">不显示</option>
                            <option value="1">显示</option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">评价时间</label>

                    <div class="controls">
                        <div class="input-append date datetimepicker">
                            <input class="span2" type="text" name="comment_time"
                                   value="{{$comment_time|default|bzf_localtime}}"
                                   data-validation-required-message="评价时间不能为空"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">商品评分</label>

                    <div class="controls">
                        <input class="span3" type="text" name="comment_rate" value="{{$comment_rate|default:'5'}}"
                               pattern="[0-5]?" data-validation-pattern-message="评分必须是1个0-5的数字"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">用户评价</label>

                    <div class="controls">
                        <textarea class="span5" rows="3" cols="20"
                                  name="comment">{{$comment|default}}</textarea>
                    </div>

                </div>

                <div class="control-group">
                    <label class="control-label">管理员回复</label>

                    <div class="controls">
                        <textarea class="span5" rows="3" cols="20"
                                  name="reply">{{$reply|default}}</textarea>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">回复时间</label>

                    <div class="controls">
                        <input class="span3" type="text"
                               value="{{$reply_time|default|bzf_localtime}}"
                               disabled="disabled"/>
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
            <!-- /用户评价详细信息 -->

        </form>
        <!-- /更新用户评价的表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}