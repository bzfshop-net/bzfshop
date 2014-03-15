{{extends file='plugin_layout.tpl'}}
{{block name=plugin_main_body}}
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>页面文本替换配置</h4>
        <br/>
        <!-- 更新管理员信息的表单  -->
        <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
              style="margin: 0px 0px 0px 0px;">

            <div class="well">

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">Pattern</span>
                        <input class="span6" type="text" name="pattern" value="{{$pattern|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">Replace</span>
                        <input class="span6" type="text" name="replace"
                               value="{{$replace|default}}"
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
