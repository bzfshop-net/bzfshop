{{extends file='misc_layout.tpl'}}
{{block name=misc_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#misc_tabbar li:has(a[href='{{bzf_make_url controller='/Misc/Cron'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '定时任务', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>定时任务列表</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">任务名称</span>
                        <input class="span2" type="text" name="task_name" value="{{$task_name|default}}"/>

                        <span class="input-label">任务时间</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2" type="text" name="task_time_start"
                                   value="{{$task_time_start|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                        <span style="float:left;margin-left: 5px;margin-right: 5px;">--</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2" type="text" name="task_time_end" value="{{$task_time_end|default}}"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">任务描述</span>
                        <input class="span2" type="text" name="task_desc" value="{{$task_desc|default}}"/>

                        <span class="input-label">是否成功</span>
                        <select class="span1 select2-simple" name="return_code"
                                data-placeholder="请选择" data-initValue="{{$return_code|default}}">
                            <option value=""></option>
                            <option value="0">成功</option>
                            <option value="1">失败</option>
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

        <!-- 任务列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>ID</th>
                <th>时间</th>
                <th>用户</th>
                <th>名称</th>
                <th>描述</th>
                <th>成功</th>
                <th>消息</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($cronTaskArray)}}
                {{foreach $cronTaskArray as $cronTask}}
                    <!-- 一个任务 -->
                    {{if 0 == $cronTask['task_run_time']}}
                        <tr class="info">
                            {{elseif 0 == $cronTask['return_code'] }}
                        <tr>
                            {{else}}
                        <tr class="error">
                    {{/if}}
                    <td>{{$cronTask['task_id']}}</td>
                    <td>{{$cronTask['task_time']|bzf_localtime}}
                        <br/>{{$cronTask['task_run_time']|bzf_localtime}}
                    </td>
                    <td>{{$cronTask['user_name']}}</td>
                    <td><a rel="tooltip" data-placement="top"
                           data-title="{{$cronTask['task_class']}}" href="#">{{$cronTask['task_name']}}</a></td>
                    <td><a rel="tooltip" data-placement="top"
                           data-title="{{$cronTask['task_param']|replace:'"':'\''}}"
                           href="#">{{$cronTask['task_desc']}}</a></td>
                    <td>
                        {{if $cronTask['task_run_time'] > 0}}
                            {{if 0 == $cronTask['return_code'] }}
                                <i class="icon-ok"></i>
                            {{else}}
                                <i class="icon-remove"></i>
                            {{/if}}
                        {{/if}}
                    </td>
                    <td>{{$cronTask['return_message']}}</td>
                    <td>
                        {{if 0 == $cronTask['task_run_time']}}
                            <a class="btn btn-small"
                               href="{{bzf_make_url controller='/Misc/Cron/Remove' task_id=$cronTask['task_id']}}">删除</a>
                        {{/if}}
                    </td>
                    </tr>
                    <!-- /一个任务 -->
                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /任务列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:20 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}