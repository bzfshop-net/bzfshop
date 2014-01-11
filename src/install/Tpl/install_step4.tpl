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
                <div class="bar bar-success active" style="width: 25%;">
                    <h5>4.安装成功</h5>
                </div>
            </div>
        </div>
        <!-- /安装过程步骤显示 -->


        <!-- 页面主体内容 -->
        <div class="row">
            <h4>棒主妇开源--安装成功</h4>

            <div class="row">

                <div class="span4">
                </div>

                <div class="span6">

                    <p>程序安装成功，现在你可以开始使用 棒主妇开源商城 了！</p>

                    <p style="font-weight: bold;color: red;">注意：安装完成之后请删除 install 目录</p>

                    <p>
                        商城首页：<a target="_blank" href="{{$WEB_ROOT_BASE}}/../">首页</a><br/>
                        （用户名：测试账号 密码：123456）
                    </p>

                    <p>手机访问：<a target="_blank" href="{{$WEB_ROOT_BASE}}/../mobile/">/mobile</a><br/>
                        （用户名：测试账号 密码：123456）
                    </p>

                    <p>
                        管理员后台：<a target="_blank" href="{{$WEB_ROOT_BASE}}/../manage/">/manage</a><br/>
                        用户名：admin 密码：123456 <br/>
                        用户名：商品编辑1号 密码：123456<br/>
                        用户名：客服1号 密码：123456
                    </p>

                    <p>
                        供货商后台：<a target="_blank" href="{{$WEB_ROOT_BASE}}/../supplier/">/supplier</a><br/>
                        （用户名：广州供货商 密码：123456 | 用户名：自己发货 密码：123456）
                    </p>
                    <br/><br/>

                    <p>如果你想手动编辑配置文件，可以看看这2个文件中的配置</p>

                    <p>环境配置：{{$envFile}}</p>

                    <p>数据配置：{{$configFile}}</p>
                </div>

                <div class="span2">
                </div>

            </div>


        </div>
        <!-- /页面主体内容 -->

    </div>
{{/block}}