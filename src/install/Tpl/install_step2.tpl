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
            <h4>棒主妇开源--环境检查</h4>

            <form class="form-horizontal" method="GET" action="{{bzf_make_url controller='/Install/Step3'}}">

                <div class="row">

                    <table class="table table-bordered table-hover table-condensed">
                        <thead>
                        <tr>
                            <th width="10%">检查项</th>
                            <th width="5%">必须</th>
                            <th>项目说明</th>
                            <th width="10%">当前环境</th>
                            <th width="5%">通过</th>
                            <th width="6%">&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>

                        {{foreach $envCheckResultArray  as $envCheckResultItem}}

                            {{if $envCheckResultItem['isMust'] && $envCheckResultItem['isPass'] <= 0}}
                                <tr class="error">
                                    {{else}}
                                <tr>
                            {{/if}}
                            <td>{{$envCheckResultItem['name']}}</td>
                            <td>
                                {{if $envCheckResultItem['isMust']}}
                                    是
                                {{else}}
                                    否
                                {{/if}}
                            </td>
                            <td style="text-align: left;">{{$envCheckResultItem['desc']}}</td>
                            <td>{{$envCheckResultItem['value']}}</td>
                            <td>
                                {{if $envCheckResultItem['isPass'] > 0 }}
                                    <i class="icon-ok"></i>
                                {{else}}
                                    <i class="icon-remove"></i>
                                {{/if}}
                            </td>
                            <td>
                                {{if $envCheckResultItem['isMust']}}
                                    <div class="control-group">
                                        <div class="controls" style="margin: 0px 0px;">
                                            <input type="hidden" value="{{$envCheckResultItem['isPass']}}" min="1"
                                                   data-validation-min-message="错误"/>
                                        </div>
                                    </div>
                                {{/if}}
                            </td>
                            </tr>
                        {{/foreach}}

                        </tbody>
                    </table>

                </div>

                <div class="row" style="text-align: center;">
                    <button type="submit" class="btn btn-success">下一步</button>
                </div>

            </form>

        </div>
        <!-- /页面主体内容 -->

    </div>
{{/block}}