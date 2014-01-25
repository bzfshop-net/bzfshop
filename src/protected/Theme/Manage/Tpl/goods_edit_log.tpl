{{extends file='goods_edit_layout.tpl'}}
{{block name=goods_edit_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_edit_tab_left li:has(a[href='{{bzf_make_url controller='/Goods/Edit/Log' goods_id=$goods_id }}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '编辑日志', link: window.location.href});
    </script>
    <!-- 左侧每个标签的具体内容 -->
    <div class="tab-content">
        <div class="tab-pane active">

            <!-- 商品编辑日志 -->
            <div class="row">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>操作时间</th>
                        <th>操作人</th>
                        <th>操作内容</th>
                        <th width="60%">重要数据监控</th>
                    </tr>
                    </thead>
                    <tbody>

                    {{if isset($goodsLogArray)}}
                        {{foreach $goodsLogArray as $goodsLog }}
                            <tr>
                                <td>{{$goodsLog['log_time']|bzf_localtime}}</td>
                                <td>{{$goodsLog['admin_user_name']|default}}</td>
                                <td>{{$goodsLog['desc']|default}}</td>
                                <td>{{$goodsLog['content'] nofilter}}</td>
                            </tr>
                        {{/foreach}}
                    {{/if}}

                    </tbody>
                </table>
            </div>
            <!-- /商品编辑日志 -->

            <!-- 分页 -->
            <div class="pagination pagination-right">
                {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
            </div>
            <!-- 分页 -->

        </div>
        <!-- /左侧每个标签的具体内容 -->

    </div>
    <!-- /商品编辑页面主体内容 -->

{{/block}}
