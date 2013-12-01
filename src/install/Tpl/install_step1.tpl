{{extends file='layout.tpl'}}
{{block name=main_body}}
    <div class="row bz_basic_content_block bz_box_shadow" style="padding:10px 10px 10px 10px;">

        <!-- 安装过程步骤显示 -->
        <div class="row" style="margin-top: 10px;">
            <div class="progress progress-striped active" style="height: 40px;">
                <div class="bar bar-success active" style="width: 25%;">
                    <h5>1.确认协议&nbsp;&nbsp;&gt;&gt;</h5>
                </div>
                <div class="bar bar-warning" style="width: 25%;">
                    <h5>2.环境检查&nbsp;&nbsp;&gt;&gt;</h5>
                </div>
                <div class="bar bar-warning" style="width: 25%;">
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
            <h4>棒主妇开源--同意协议</h4>

            <div class="row">
                <div class="span2">
                </div>
                <div class="span8">
                    <p>
                        本来按照惯例这地方应该是留着写点警告协议之类的东东，不过在中国这种印上了协议的纸大家拿来擦屁股都嫌它太硬，基本上只能写给自己看看的一种安慰罢了。既然这样，所以我们决定这地方干脆写点开心的内容好了，省得这么枯燥乏味。</p>

                    <p>使用棒主妇开源只有一个协议：希望我们的开源商城能对你的业务有所帮助，需要买服装就逛逛&nbsp;&nbsp;<a target="_blank"
                                                                                href="http://www.bangzhufu.com">棒主妇商城</a>&nbsp;&nbsp;支持我们一下吧
                    </p>

                    <p>没了，继续安装吧 ---></p>

                    <p style="text-align: right;"><--- 棒主妇开源团队</p>
                </div>
                <div class="span2">
                </div>
            </div>

            <div class="row" style="text-align: center;">
                <button class="btn btn-info">拒绝协议</button>
                <a class="btn btn-success" href="{{bzf_make_url controller='/Install/Step2' }}">同意协议</a>
            </div>

        </div>
        <!-- /页面主体内容 -->

    </div>
{{/block}}