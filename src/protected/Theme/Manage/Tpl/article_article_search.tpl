{{extends file='article_layout.tpl'}}
{{block name=article_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#article_tabbar li:has(a[href='{{bzf_make_url controller='/Article/Article/Search'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '文章列表', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>文章列表</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">文章ID</span>
                        <input class="span2" type="text" name="article_id"
                               pattern="[0-9]*" data-validation-pattern-message="文章ID应该是全数字"
                               value="{{$article_id|default}}"/>
                        <span class="input-label">文章标题</span>
                        <input class="span2" type="text" name="title" value="{{$title|default}}"/>
                        <span class="input-label">文章描述</span>
                        <input class="span4" type="text" name="description" value="{{$description|default}}"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">文章分类</span>
                        <select class="span2 select2-simple" name="cat_id" data-placeholder="文章分类列表"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Article/ListCategory"}}"
                                data-option-value-key="meta_id" data-option-text-key="meta_name"
                                data-initValue="{{$cat_id|default}}">
                            <option value=""></option>
                        </select>
                        <span class="input-label">管理员</span>
                        <select class="span2 select2-simple" name="admin_user_id" data-placeholder="管理员列表"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/AdminUser/ListUserIdName"}}"
                                data-option-value-key="user_id" data-option-text-key="user_name"
                                data-initValue="{{$admin_user_id|default}}">
                            <option value=""></option>
                        </select>
                        <span class="input-label">是否显示</span>
                        <select class="span2 select2-simple" name="is_open"
                                data-placeholder="过滤显示"
                                data-initValue="{{$is_open|default}}">
                            <option value=""></option>
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-success">查询</button>
                        &nbsp;&nbsp;
                        <a href="{{bzf_make_url controller='/Article/Article/Create'}}" class="btn btn-info">新建</a>
                    </div>
                </div>
            </form>
        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 管理员列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>文章ID</th>
                <th>文章分类</th>
                <th width="20%">标题</th>
                <th width="30%">描述</th>
                <th>是否显示</th>
                <th>管理员</th>
                <th>最后更新</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($articleArray)}}
                {{foreach $articleArray as $articleItem}}
                    <!-- 一个文章 -->
                    <tr>
                        <td>{{$articleItem['article_id']}}</td>
                        <td>{{$articleItem['cat_name']|default}}</td>
                        <td>{{$articleItem['title']}}</td>
                        <td>{{$articleItem['description']}}</td>
                        <td>
                            {{if $articleItem['is_open'] > 0}}
                                <i class="icon-ok"></i>
                            {{else}}
                                <i class="icon-remove"></i>
                            {{/if}}
                        </td>
                        <td>{{$articleItem['admin_user_name']}}</td>
                        <td>{{$articleItem['update_time']|bzf_localtime}}</td>
                        <td>
                            <a class="btn btn-small"
                               href="{{bzf_make_url controller='/Article/Article/Edit' article_id=$articleItem['article_id']}}">编辑</a>
                        </td>
                    </tr>
                    <!-- /一个文章 -->
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