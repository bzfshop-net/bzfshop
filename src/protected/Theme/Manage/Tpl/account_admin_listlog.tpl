{{extends file='account_layout.tpl'}}
{{block name=account_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#account_tabbar li:has(a[href='{{bzf_make_url controller='/Account/Admin/ListLog'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '管理员日志', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>管理员日志</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">管理员</span>
                        <select class="span2 select2-simple" name="user_id" data-placeholder="管理员列表"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/AdminUser/ListUserIdName"}}"
                                data-option-value-key="user_id" data-option-text-key="user_name"
                                data-initValue="{{$user_id|default}}">
                            <option value=""></option>
                        </select>
                        <span class="input-label">操作时间</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2" type="text" name="operate_time_start"
                                   value="{{$operate_time_start|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                        <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2" type="text" name="operate_time_end"
                                   value="{{$operate_time_end|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">操作</span>
                        <input class="span2" type="text" name="operate" value="{{$operate|default}}"/>
                        <span class="input-label">描述</span>
                        <input class="span2" type="text" name="operate_desc" value="{{$operate_desc|default}}"/>
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

        <!-- 管理员列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>日志ID</th>
                <th>操作时间</th>
                <th>管理员</th>
                <th>操作</th>
                <th>描述</th>
                <th>数据</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($adminLogArray)}}
                {{foreach $adminLogArray as $adminLog}}
                    <!-- 一条日志 -->
                    <tr>
                        <td>{{$adminLog['log_id']}}</td>
                        <td>{{$adminLog['operate_time']|bzf_localtime}}</td>
                        <td>[{{{{$adminLog['user_id']}}}}]{{$adminLog['user_name']}}</td>
                        <td>{{$adminLog['operate']}}</td>
                        <td>{{$adminLog['operate_desc']}}</td>
                        <td>{{$adminLog['operate_data']|nl2br}}</td>
                    </tr>
                    <!-- /一条日志 -->
                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /管理员列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:20 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}