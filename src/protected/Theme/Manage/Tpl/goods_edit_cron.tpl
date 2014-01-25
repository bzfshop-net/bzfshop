{{extends file='goods_edit_layout.tpl'}}
{{block name=goods_edit_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_edit_tab_left li:has(a[href='{{bzf_make_url controller='/Goods/Edit/Cron' goods_id=$goods_id }}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '定时任务', link: window.location.href});
    </script>
    <!-- 左侧每个标签的具体内容 -->
    <div class="tab-content">
        <div class="tab-pane active">

            <!-- 这里是条件筛选区 -->
            <div class="row well well-small">
                <button type="button" class="btn btn-info"
                        onclick="$('#goods_edit_cron_set_onsale_modal').modal({dynamic: true});">定时上下架
                </button>
                <button type="button" class="btn btn-info"
                        onclick="$('#goods_edit_cron_set_price_modal').modal({dynamic: true});">定时改价
                </button>
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
        <!-- /左侧每个标签的具体内容 -->

    </div>
    <!-- /商品编辑页面主体内容 -->

    <!-- 商品定时上下架 modal -->
    <div id="goods_edit_cron_set_onsale_modal" class="modal hide fade" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4>商品定时上下架</h4>
        </div>
        <form class="form-horizontal form-horizontal-inline" method="POST">

            <div class="modal-body">

                <div class="control-group">
                    <div class="controls">
                        <input type="hidden" name="goods_id" value="{{$goods_id}}"/>
                        <span class="input-label">任务时间</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2" type="text" data-validation-required-message="不能为空" name="task_time"/>
                                <span class="add-on">
                                    <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                                </span>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">任务操作</span>
                        <select class="span1 select2-simple" name="action">
                            <option value="Online">上架</option>
                            <option value="Offline">下架</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">添加任务</button>
                <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
            </div>
        </form>
    </div>
    <!-- /商品定时上下架 modal -->

    <!-- 商品定时改价格 modal -->
    <div id="goods_edit_cron_set_price_modal" class="modal hide fade" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4>商品定时改价</h4>
        </div>
        <form class="form-horizontal form-horizontal-inline" method="POST">

            <div class="modal-body">

                <div class="control-group">
                    <div class="controls">
                        <input type="hidden" name="goods_id" value="{{$goods_id}}"/>
                        <input type="hidden" name="action" value="setPrice"/>
                        <span class="input-label">任务时间</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2" type="text" data-validation-required-message="不能为空" name="task_time"/>
                                <span class="add-on">
                                    <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                                </span>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品标题</span>
                        <input type="text" class="span4" name="goods[goods_name]" value="{{$goods['goods_name']}}"/>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品价格</span>
                        <input type="text" class="span2" name="goods[shop_price]" pattern="^\d+(\.\d+)?$"
                               data-validation-required-message="销售价不能为空"
                               data-validation-pattern-message="销售价无效"
                               value="{{$goods['shop_price']|bzf_money_display}}"/>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">价格提示</span>
                        <input type="text" class="span2" name="goods[shop_price_notice]"
                               value="{{$goods['shop_price_notice']}}"/>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">添加任务</button>
                <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
            </div>
        </form>
    </div>
    <!-- /商品定时改价格 modal -->

{{/block}}
