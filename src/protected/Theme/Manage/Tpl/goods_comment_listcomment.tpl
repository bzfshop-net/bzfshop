{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Comment/ListComment'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '用户评价', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>用户评价列表</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品ID</span>
                        <input class="span2" type="text" name="goods_id"
                               pattern="[0-9]*" data-validation-pattern-message="商品ID应该是全数字"
                               value="{{$goods_id|default}}"/>
                        <span class="input-label">是否显示</span>
                        <select class="span2 select2-simple" name="is_show"
                                data-placeholder="全部" data-initValue="{{$is_show|default}}">
                            <option value=""></option>
                            <option value="0">不显示</option>
                            <option value="1">显示</option>
                        </select>
                        <span class="input-label">管理员</span>
                        <select class="span2 select2-simple" name="admin_user_id" data-placeholder="管理员列表"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/AdminUser/ListUserIdName"}}"
                                data-option-value-key="user_id" data-option-text-key="user_name"
                                data-initValue="{{$admin_user_id|default}}">
                            <option value=""></option>
                        </select>
                        &nbsp;&nbsp;
                        <button type="submit" class="btn btn-success">查询</button>
                        &nbsp;&nbsp;
                        <a href="{{bzf_make_url controller='/Goods/Comment/Create'}}" class="btn btn-info">新建</a>
                    </div>
                </div>
            </form>
        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 管理员列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>评论ID</th>
                <th>子订单</th>
                <th>创建时间</th>
                <th>商品ID</th>
                <th>用户</th>
                <th>评分</th>
                <th>评价</th>
                <th>管理员</th>
                <th>回复</th>
                <th>显示</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($goodsCommentArray)}}
                {{foreach $goodsCommentArray as $goodsCommentItem}}
                    <!-- 一个管理员 -->
                    <tr>
                        <td>{{$goodsCommentItem['comment_id']}}</td>
                        <td>
                            {{if $goodsCommentItem['rec_id'] > 0}}
                                <a href="{{bzf_make_url controller='/Order/Goods/Detail' rec_id=$goodsCommentItem['rec_id']}}">
                                    {{$goodsCommentItem['rec_id']}}</a>
                            {{else}}
                                {{$goodsCommentItem['rec_id']}}
                            {{/if}}
                        </td>
                        <td>{{$goodsCommentItem['create_time']|bzf_localtime}}</td>
                        <td>
                            <a rel="clickover" data-placement="top" href="#"
                               data-content="{{bzf_goods_view_toolbar goods_id=$goodsCommentItem['goods_id']}}">
                                {{$goodsCommentItem['goods_id']}}
                            </a>
                        </td>
                        <td>{{$goodsCommentItem['user_name']}}</td>
                        <td>{{$goodsCommentItem['comment_rate']}}</td>
                        <td>{{$goodsCommentItem['comment']}}</td>
                        <td>{{$goodsCommentItem['admin_user_name']}}</td>
                        <td>{{$goodsCommentItem['reply']}}</td>
                        <td>
                            {{if $goodsCommentItem['is_show'] > 0}}
                                <i class="icon-ok"></i>
                            {{else}}
                                <i class="icon-remove"></i>
                            {{/if}}
                        </td>
                        <td>
                            <a href="{{bzf_make_url controller='/Goods/Comment/Edit' comment_id=$goodsCommentItem['comment_id']}}"
                               class="btn btn-small">编辑</a>
                        </td>
                    </tr>
                    <!-- /一个管理员 -->
                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /管理员列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}