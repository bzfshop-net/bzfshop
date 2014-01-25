{{extends file='plugin_layout.tpl'}}
{{block name=plugin_main_body}}
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#plugin_tabbar li:has(a[href='{{bzf_make_url controller='/Plugin/Plugin/ListPlugin'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '插件管理', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        {{if isset($pluginArray)}}
            {{foreach $pluginArray as $pluginItem}}
                <!-- 一个插件 -->
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr class="well">
                        <th>插件</th>
                        <th width="70%">插件描述</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>[{{$pluginItem['pluginDirName']}}]&nbsp;&nbsp;{{$pluginItem['pluginDisplayName']}}</td>
                        <td rowspan="3" style="text-align: left;">{{$pluginItem['pluginDescText'] nofilter}}</td>
                    </tr>
                    <tr>
                        <td>
                            已安装版本：{{$pluginItem['installVersion']}}&lt;----代码版本：{{$pluginItem['pluginVersion']}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{if $pluginItem['pluginIsInstall']}}
                                {{if $pluginItem['pluginIsActive']}}
                                    <a class="btn btn-danger" onclick="return confirm('你确定要停用这个插件吗？');"
                                       href="{{bzf_make_url controller='/Plugin/Plugin/DeactivatePlugin' pluginDirName=$pluginItem['pluginDirName']}}">停用</a>
                                {{else}}
                                    <a class="btn" onclick="return confirm('你确定要启用这个插件吗？');"
                                       href="{{bzf_make_url controller='/Plugin/Plugin/ActivatePlugin' pluginDirName=$pluginItem['pluginDirName']}}">启用</a>
                                    <a class="btn btn-danger" onclick="return confirm('你确定要卸载这个插件吗？');"
                                       href="{{bzf_make_url controller='/Plugin/Plugin/UninstallPlugin' pluginDirName=$pluginItem['pluginDirName']}}">卸载</a>
                                {{/if}}

                                {{if $pluginItem['pluginIsNeedUpdate']}}
                                    <a class="btn btn-warning" onclick="return confirm('你确定要升级这个插件吗？');"
                                       href="{{bzf_make_url controller='/Plugin/Plugin/UpdatePlugin' pluginDirName=$pluginItem['pluginDirName']}}">升级</a>
                                {{/if}}

                                {{if $pluginItem['pluginIsActive'] && $pluginItem['pluginConfigureUrl']}}
                                    <a class="btn btn-success"
                                       href="{{$pluginItem['pluginConfigureUrl']}}">设置</a>
                                {{/if}}
                            {{else}}
                                <a class="btn" onclick="return confirm('你确定要安装这个插件吗？');"
                                   href="{{bzf_make_url controller='/Plugin/Plugin/InstallPlugin' pluginDirName=$pluginItem['pluginDirName']}}">安装</a>
                            {{/if}}

                            <!-- a class="btn btn-small btn-danger" onclick="return confirm('你确定要删除这个插件吗？');" href="#">删除</a -->

                        </td>
                    </tr>
                    </tbody>
                </table>
                <!-- /一个插件 -->
            {{/foreach}}
        {{/if}}

    </div>
    <!-- /页面主体内容 -->

{{/block}}
