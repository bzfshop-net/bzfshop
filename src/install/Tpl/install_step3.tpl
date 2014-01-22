{{extends file='layout.tpl'}}
{{block name=main_body}}
    <div class="row bz_basic_content_block bz_box_shadow" style="padding:10px 10px 10px 10px;">

        <!-- 安装过程步骤显示 -->
        <div class="row" style="margin-top: 10px;">
            <div class="progress progress-striped active" style="height: 40px;">
                <div class="bar bar-success active" style="width: 25%;">
                    <h5>1.确认协议&nbsp;&nbsp;&gt;&gt;</h5>
                </div>
                <div class="bar bar-success active" style="width: 25%;">
                    <h5>2.环境检查&nbsp;&nbsp;&gt;&gt;</h5>
                </div>
                <div class="bar bar-success active" style="width: 25%;">
                    <h5>3.导入数据&nbsp;&nbsp;&gt;&gt;</h5>
                </div>
                <div class="bar bar-warning" style="width: 25%;">
                    <h5>4.安装成功</h5>
                </div>
            </div>
        </div>
        <!-- /安装过程步骤显示 -->


        <!-- 页面主体内容 -->
        <div class="row">
            <h4>棒主妇开源--导入数据</h4>

            {{if !empty($cloud_message)}}
                <h5 style="color: red;text-align: right;">{{$cloud_message}}</h5>
            {{/if}}

            <form class="form-horizontal form-horizontal-inline" method="POST">

                <div class="row">

                    <div class="span4">
                    </div>

                    <div class="span6">

                        <!-- Bae3 需要输入 API Key 和 Secret Key -->
                        {{if 'Bae3' == $currentEngineStr}}
                            <div class="control-group">
                                <div class="controls">
                                    <span class="input-label">API Key</span>
                                    <input class="span2" name="sysConfig[bae3_api_key]" value=""
                                           type="text" data-validation-required-message="API Key不能为空"/>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="controls">
                                    <span class="input-label">Secret Key</span>
                                    <input class="span2" name="sysConfig[bae3_secret_key]" value=""
                                           type="text" data-validation-required-message="Secret Key不能为空"/>
                                </div>
                            </div>
                        {{/if}}

                        <!--  Sae 平台不需要配置数据库，直接从环境变量中取得即可 -->
                        {{if 'Sae' != $currentEngineStr}}
                            <div class="control-group">
                                <div class="controls">
                                    <span class="input-label">数据库地址</span>
                                    <input class="span2" name="dbHost" value="{{$dbHost}}"
                                           type="text" data-validation-required-message="数据库地址不能为空"/>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="controls">
                                    <span class="input-label">数据库端口</span>
                                    <input class="span2" name="dbPort" value="{{$dbPort}}"
                                           type="text" data-validation-required-message="数据库端口不能为空"
                                           pattern="[0-9]+" data-validation-pattern-message="数据库端口应该是全数字"/>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="controls">
                                    <span class="input-label">数据库名称</span>
                                    <input class="span2" name="dbName" value="{{$dbName}}"
                                           type="text" data-validation-required-message="数据库名称不能为空"/>
                                </div>
                            </div>
                            <!-- Bae3 平台不需要配置数据库的用户名和密码，直接用 Api Key -->
                            {{if 'Bae3' != $currentEngineStr}}
                                <div class="control-group">
                                    <div class="controls">
                                        <span class="input-label">用户名</span>
                                        <input class="span2" name="sysConfig[db_username]" value=""
                                               type="text" data-validation-required-message="用户名不能为空"/>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <div class="controls">
                                        <span class="input-label">密码</span>
                                        <input class="span2" name="sysConfig[db_password]" value=""
                                               type="password" data-validation-required-message="密码不能为空"/>
                                    </div>
                                </div>
                            {{/if}}

                        {{/if}}

                    </div>

                    <div class="span2">
                    </div>

                </div>

                <div class="row" style="text-align: center;margin-top: 20px;">
                    <button type="submit" class="btn btn-success">导入数据</button>
                </div>

            </form>

        </div>
        <!-- /页面主体内容 -->

    </div>
{{/block}}