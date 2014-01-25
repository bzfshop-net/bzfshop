{{extends file='account_layout.tpl'}}
{{block name=account_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#account_tabbar li:has(a[href='{{bzf_make_url controller='/Account/User/Money'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '用户资金', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>用户资金变动</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">操作类型</span>
                        <select class="span1 select2-simple" style="float:left;"
                                data-placeholder="操作类型" name="change_type"
                                data-initValue="{{$change_type|default}}">
                            <option value=""></option>
                            <option value="0">帐户冲值</option>
                            <option value="1">帐户提款</option>
                            <option value="2">调节帐户</option>
                            <option value="99">其他类型</option>
                        </select>
                        <span class="input-label">用户ID</span>
                        <input class="span1" type="text" name="user_id" value="{{$user_id|default}}"
                               pattern="[0-9]+" data-validation-pattern-message="用户ID必须是数字"/>
                        <span class="input-label">管理员</span>
                        <select class="span2 select2-simple" name="admin_user_id" data-placeholder="管理员列表"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/AdminUser/ListUserIdName"}}"
                                data-option-value-key="user_id" data-option-text-key="user_name"
                                data-initValue="{{$admin_user_id|default}}">
                            <option value=""></option>
                        </select>
                        <span class="input-label">备注</span>
                        <input class="span2" type="text" name="change_desc" value="{{$change_desc|default}}"/>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-success">查询</button>
                    </div>
                </div>
            </form>
        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 资金变动列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr class="well well-small">
                <th>ID</th>
                <th>发生时间</th>
                <th>用户ID</th>
                <th>用户名</th>
                <th>操作类型</th>
                <th>金额</th>
                <th>备注</th>
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
                    <td>{{$accountLogItem['log_id']}}</td>
                    <td>{{$accountLogItem['change_time']|bzf_localtime}}</td>
                    <td>{{$accountLogItem['user_id']}}</td>
                    <td>
                        <a href="#"
                           onclick="bZF.Account_User_ajaxDetail({{$accountLogItem['user_id']}});return false;">{{$accountLogItem['user_name']}}</a>
                    </td>
                    <td>{{$accountLogItem['change_type_desc']}}</td>
                    <td>{{$accountLogItem['user_money']|bzf_money_display}}</td>
                    <td>{{$accountLogItem['change_desc']|nl2br nofilter}}</td>
                    </tr>
                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /资金变动列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:20 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->

    <!-- 用户详情对话框 -->
    <div id="user_detail_dialog" class="modal hide fade">
    </div>
    <!-- 用户详情对话框 -->

{{/block}}