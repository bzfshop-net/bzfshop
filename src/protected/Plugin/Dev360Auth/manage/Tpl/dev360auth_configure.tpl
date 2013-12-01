{{extends file='plugin_layout.tpl'}}
{{block name=plugin_main_body}}
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>360一站通登陆插件配置</h4>
        <br/>
        <!-- 更新管理员信息的表单  -->
        <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
              style="margin: 0px 0px 0px 0px;">

            <div class="well">

                <!-- 分割条 -->
                <div class="row inline-divider">
                    <div class="divider"></div>
                    <label class="label label-info">Shop</label>
                </div>
                <!-- /分割条 -->

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">AppId</span>
                        <input class="span6" type="text" name="shop_dev360auth_app_id"
                               pattern="[0-9]+" data-validation-pattern-message="必须是全数字"
                               value="{{$shop_dev360auth_app_id|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">AppKey</span>
                        <input class="span6" type="text" name="shop_dev360auth_app_key"
                               value="{{$shop_dev360auth_app_key|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">AppSecrect</span>
                        <input class="span6" type="text" name="shop_dev360auth_app_secrect"
                               value="{{$shop_dev360auth_app_secrect|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <!-- 分割条 -->
                <div class="row inline-divider">
                    <div class="divider"></div>
                    <label class="label label-info">Aimeidaren</label>
                </div>
                <!-- /分割条 -->

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">AppId</span>
                        <input class="span6" type="text" name="aimeidaren_dev360auth_app_id"
                               pattern="[0-9]+" data-validation-pattern-message="必须是全数字"
                               value="{{$aimeidaren_dev360auth_app_id|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">AppKey</span>
                        <input class="span6" type="text" name="aimeidaren_dev360auth_app_key"
                               value="{{$aimeidaren_dev360auth_app_key|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">AppSecrect</span>
                        <input class="span6" type="text" name="aimeidaren_dev360auth_app_secrect"
                               value="{{$aimeidaren_dev360auth_app_secrect|default}}"
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
