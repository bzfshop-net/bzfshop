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

            <form class="form-horizontal form-horizontal-inline" method="POST">

                <div class="row">

                    <div class="span4">
                    </div>

                    <div class="span6">

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
                                       type="text" data-validation-required-message="数据库地址不能为空"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls">
                                <span class="input-label">数据库名称</span>
                                <input class="span2" name="dbName" value="{{$dbName}}"
                                       type="text" data-validation-required-message="数据库名称不能为空"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls">
                                <span class="input-label">用户名</span>
                                <input class="span2" name="dbUserName" value="{{$dbUserName}}"
                                       type="text" data-validation-required-message="用户名不能为空"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls">
                                <span class="input-label">密码</span>
                                <input class="span2" name="dbPassword" value=""
                                       type="password" data-validation-required-message="密码不能为空"/>
                            </div>
                        </div>

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