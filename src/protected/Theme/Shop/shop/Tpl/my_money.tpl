{{extends file='my_layout.tpl'}}
{{block name=main_body_my}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bzf_set_nav_status.push(function ($) {
            $("#my_nav_tabbar li:has(a[href='{{bzf_make_url controller='/My/Money'}}'])").addClass("active");
        });
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        <h4>我的资金</h4>

        <p class="pull-left">当前可用资金余额：<span style="font-weight: bold;color:red;">{{$userMoney|bzf_money_display}}</span>元
        </p>

        <!-- 我的资金变动列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr class="well well-small">
                <th>发生时间</th>
                <th>操作类型</th>
                <th>资金金额</th>
                <th>操作备注</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($accountLogArray)}}
                {{foreach from=$accountLogArray item=accountLogItem}}
                    {{if $accountLogItem['user_money'] > 0}}
                        <tr class="success">
                            {{else}}
                        <tr>
                    {{/if}}
                    <td>{{$accountLogItem['change_time']|bzf_localtime}}</td>
                    <td>{{$accountLogItem['change_type_desc']}}</td>
                    <td>{{$accountLogItem['user_money']|bzf_money_display}}</td>
                    <td>{{$accountLogItem['change_desc']|nl2br nofilter}}</td>
                    </tr>
                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /我的资金变动列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}