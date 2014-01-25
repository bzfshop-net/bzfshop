{{extends file='my_layout.tpl'}}
{{block name=main_body_my}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bzf_set_nav_status.push(function ($) {
            $("#my_nav_tabbar li:has(a[href='{{bzf_make_url controller='/My/Address'}}'])").addClass("active");
        });
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        <h4>我的地址</h4>

        <!-- 我的地址表单  -->
        <form class="form-horizontal" method="post">

            <div class="control-group">
                <label class="control-label">姓名*</label>

                <div class="controls">
                    <input class="span2" type="text" name="consignee" value="{{$consignee|default}}"
                           data-validation-required="data-validation-required"/>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="address">收货地址*</label>

                <div class="controls">
                    <input class="span7" type="text" name="address" value="{{$address|default}}"
                           data-validation-required="data-validation-required"/>

                    <p>
                        请按照省、市、区方式填写，例如“浙江省杭州市萧山区 xxx大街 xxx号楼 111号”
                    </p>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="mobile">手机*</label>

                <div class="controls">
                    <input class="span2" type="text" name="mobile" value="{{$mobile|default}}" pattern="1([0-9]{10})"
                           data-validation-pattern-message="号码格式不正确"
                           data-validation-required="data-validation-required"/>

                    <p>
                        手机格式例如：138xxxxxxxx （短信通知您发货进度）
                    </p>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="tel">固定电话</label>

                <div class="controls">
                    <input class="span2" type="text" name="tel" value="{{$tel|default}}" pattern="[0-9 -]+"
                           data-validation-pattern-message="电话格式不正确"/>

                    <p>
                        固定电话，格式例如：010-6277xxxx
                    </p>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="zipcode">邮编</label>

                <div class="controls">
                    <input class="span2" type="text" name="zipcode" value="{{$zipcode|default}}" pattern="[0-9]+"
                           data-validation-pattern-message="邮编格式不正确"/>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label">&nbsp; </label>

                <div class="controls">
                    <button type="submit" class="btn btn-success">
                        提交修改
                    </button>
                </div>
            </div>

        </form>
        <!-- /我的地址表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}