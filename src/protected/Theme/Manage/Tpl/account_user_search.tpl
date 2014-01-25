{{extends file='account_layout.tpl'}}
{{block name=account_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#account_tabbar li:has(a[href='{{bzf_make_url controller='/Account/User/Search'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '用户列表', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>用户列表</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">用户ID</span>
                        <input class="span1" type="text" name="user_id" value="{{$user_id|default}}"
                               pattern="[0-9]+" data-validation-pattern-message="用户ID必须是数字"/>
                        <span class="input-label">用户账号</span>
                        <input class="span2" type="text" name="user_name" value="{{$user_name|default}}"/>
                        <span class="input-label">Email</span>
                        <input class="span2" type="text" name="email" value="{{$email|default}}"/>
                        <span class="input-label">余额排序</span>
                        <select class="span1 select2-simple" style="float:left;"
                                data-placeholder="余额排序" name="orderByUserMoney"
                                data-initValue="{{$orderByUserMoney|default}}">
                            <option value=""></option>
                            <option value="1">余额递减</option>
                            <option value="2">余额递增</option>
                        </select>
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

        <!-- 用户列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>用户ID</th>
                <th width="20%">用户账号</th>
                <th width="20%">Email</th>
                <th>余额</th>
                <th width="20%">第三方登陆</th>
                <th>最近登录</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($userInfoArray)}}
                {{foreach $userInfoArray as $userInfoItem}}
                    <!-- 一个用户 -->
                    <tr>
                        <td>{{$userInfoItem['user_id']}}</td>
                        <td>{{$userInfoItem['user_name']}}</td>
                        <td>{{$userInfoItem['email']}}</td>
                        <td>{{$userInfoItem['user_money']|bzf_money_display}}</td>
                        <td>{{$userInfoItem['sns_login']}}</td>
                        <td>{{$userInfoItem['last_login']|bzf_localtime}}</td>
                        <td>
                            <button class="btn btn-small"
                                    onclick="bZF.Account_User_ajaxDetail({{$userInfoItem['user_id']}});">详情
                            </button>
                            <a class="btn btn-small"
                               href="{{bzf_make_url controller='/Account/User/Money' user_id=$userInfoItem['user_id']}}">资金</a>
                        </td>
                    </tr>
                    <!-- /一个用户 -->
                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /用户列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->

    <!-- 用户详情对话框 -->
    <div id="user_detail_dialog" class="modal hide fade">
    </div>
    <!-- 用户详情对话框 -->

{{/block}}