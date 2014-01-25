{{extends file='account_layout.tpl'}}
{{block name=account_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#account_tabbar li:has(a[href='{{bzf_make_url controller='/Account/Supplier/ListUser'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '供货商列表', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>供货商列表</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">供货商名称</span>
                        <input class="span2" type="text" name="suppliers_name" value="{{$suppliers_name|default}}"/>
                        <span class="input-label">供货商描述</span>
                        <input class="span2" type="text" name="suppliers_desc" value="{{$suppliers_desc|default}}"/>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <button type="submit" class="btn btn-success">查询</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <a href="{{bzf_make_url controller='/Account/Supplier/Create'}}" class="btn btn-info">添加供货商</a>
                    </div>
                </div>
            </form>
        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 供货商列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>供货商ID</th>
                <th>供货商账号</th>
                <th>供货商名称</th>
                <th width="50%">供货商描述</th>
                <th width="5%">状态</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($supplierUserArray)}}
                {{foreach $supplierUserArray as $supplierUserItem}}
                    <!-- 一个供货商 -->
                    <tr>
                        <td>{{$supplierUserItem['suppliers_id']}}</td>
                        <td>{{$supplierUserItem['suppliers_account']}}</td>
                        <td>{{$supplierUserItem['suppliers_name']}}</td>
                        <td>{{$supplierUserItem['suppliers_desc']}}</td>
                        <td>{{if $supplierUserItem['is_check']}}正常{{else}}停用{{/if}}</td>
                        <td>
                            <a href="{{bzf_make_url controller='/Account/Supplier/Edit' suppliers_id=$supplierUserItem['suppliers_id']}}"
                               class="btn btn-small">编辑</a>
                        </td>
                    </tr>
                    <!-- /一个供货商 -->
                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /供货商列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}