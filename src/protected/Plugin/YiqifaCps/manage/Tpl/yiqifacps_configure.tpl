{{extends file='plugin_layout.tpl'}}
{{block name=plugin_main_body}}
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>亿起发CPS配置</h4>
        <br/>
        <!-- 更新管理员信息的表单  -->
        <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
              style="margin: 0px 0px 0px 0px;">

            <div class="well">

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">Web费率</span>
                        <input class="span6" type="text" name="yiqifacps_rate_web"
                               value="{{$yiqifacps_rate_web|default}}"
                               pattern="^\d+(\.\d+)?$" data-validation-pattern-message="格式错误"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">Mobile费率</span>
                        <input class="span6" type="text" name="yiqifacps_rate_mobile"
                               value="{{$yiqifacps_rate_mobile|default}}"
                               pattern="^\d+(\.\d+)?$" data-validation-pattern-message="格式错误"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">CPS有效期</span>
                        <input class="span6" type="text" name="yiqifacps_duration"
                               value="{{$yiqifacps_duration|default}}"
                               pattern="[0-9]+" data-validation-pattern-message="必须是全数字"
                               data-validation-required="data-validation-required"/>
                        <span class="comments">(CPS Cookie 有效期时间，单位秒)</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">QQ彩贝Key1</span>
                        <input class="span6" type="text" name="qqcaibei_key1" value="{{$qqcaibei_key1|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">QQ彩贝Key2</span>
                        <input class="span6" type="text" name="qqcaibei_key2" value="{{$qqcaibei_key2|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

            </div>

            <div class="control-group">
                <label class="control-label">&nbsp; </label>

                <div class="controls">
                    <button type="submit" class="btn btn-success">
                        保存设置
                    </button>
                </div>
            </div>

        </form>
        <!-- /更新管理员信息的表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}
