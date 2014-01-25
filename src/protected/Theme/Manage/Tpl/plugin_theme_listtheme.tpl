{{extends file='plugin_layout.tpl'}}
{{block name=plugin_main_body}}
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#plugin_tabbar li:has(a[href='{{bzf_make_url controller='/Plugin/Theme/ListTheme'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '主题管理', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        {{if isset($themeArray)}}
            {{foreach $themeArray as $themeItem}}

                <!-- 一个主题 -->
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr class="well">
                        <th>主题</th>
                        <th width="70%">主题描述</th>
                    </tr>
                    </thead>
                    <tbody>

                    <tr>
                        <td>[{{$themeItem['pluginDirName']}}]&nbsp;&nbsp;{{$themeItem['pluginDisplayName']}}</td>
                        <td rowspan="3" style="text-align: left;">{{$themeItem['pluginDescText'] nofilter}}</td>
                    </tr>
                    <tr>
                        <td>
                            已安装版本：{{$themeItem['installVersion']}}&lt;----代码版本：{{$themeItem['pluginVersion']}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{if $themeItem['pluginIsInstall']}}
                                {{if $themeItem['pluginIsActive']}}
                                    <button class="btn btn-info">使用中</button>
                                    <a class="btn btn-danger" onclick="return confirm('你确定要停用这个主题吗？');"
                                       href="{{bzf_make_url controller='/Plugin/Theme/DeactivateTheme' themeDirName=$themeItem['pluginDirName']}}">停用</a>
                                {{else}}
                                    <a class="btn" onclick="return confirm('你确定要启用这个主题吗？');"
                                       href="{{bzf_make_url controller='/Plugin/Theme/ActivateTheme' themeDirName=$themeItem['pluginDirName']}}">启用</a>
                                    <a class="btn btn-danger" onclick="return confirm('你确定要卸载这个主题吗？');"
                                       href="{{bzf_make_url controller='/Plugin/Theme/UninstallTheme' themeDirName=$themeItem['pluginDirName']}}">卸载</a>
                                {{/if}}

                                {{if $themeItem['pluginIsNeedUpdate']}}
                                    <a class="btn btn-warning" onclick="return confirm('你确定要升级这个主题吗？');"
                                       href="{{bzf_make_url controller='/Plugin/Theme/UpdateTheme' themeDirName=$themeItem['pluginDirName']}}">升级</a>
                                {{/if}}

                                {{if $themeItem['pluginIsActive'] && $themeItem['pluginConfigureUrl']}}
                                    <a class="btn btn-success"
                                       href="{{$themeItem['pluginConfigureUrl']}}">设置</a>
                                {{/if}}
                            {{else}}
                                <a class="btn" onclick="return confirm('你确定要安装这个主题吗？');"
                                   href="{{bzf_make_url controller='/Plugin/Theme/InstallTheme' themeDirName=$themeItem['pluginDirName']}}">安装</a>
                            {{/if}}

                            <!-- a class="btn btn-small btn-danger" onclick="return confirm('你确定要删除这个主题吗？');" href="#">删除</a -->

                        </td>
                    </tr>
                    </tbody>
                </table>
                <!-- /一个主题  -->
            {{/foreach}}
        {{/if}}

    </div>
    <!-- /页面主体内容 -->

{{/block}}
