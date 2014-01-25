{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/AttrGroup/ListAttrGroup'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '商品类型', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>商品类型列表</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品类型</span>
                        <input class="span2" type="text" name="meta_name" value="{{$meta_name|default}}"/>
                        <span class="input-label">类型描述</span>
                        <input class="span2" type="text" name="meta_desc" value="{{$meta_desc|default}}"/>
                        &nbsp;&nbsp;
                        <button type="submit" class="btn btn-success">查询</button>
                        &nbsp;&nbsp;
                        <a href="{{bzf_make_url controller='/Goods/AttrGroup/Create'}}" class="btn btn-info">添加类型</a>
                    </div>
                </div>
            </form>
        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 管理员列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>类型ID</th>
                <th>商品类型</th>
                <th>类型描述</th>
                <th>类型分组</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($goodsAttrGroupArray)}}
                {{foreach $goodsAttrGroupArray as $goodsAttrGroupItem}}
                    <!-- 一个管理员 -->
                    <tr>
                        <td>{{$goodsAttrGroupItem['meta_id']}}</td>
                        <td>{{$goodsAttrGroupItem['meta_name']}}</td>
                        <td>{{$goodsAttrGroupItem['meta_desc']}}</td>
                        <td>{{$goodsAttrGroupItem['meta_data']|nl2br nofilter}}</td>
                        <td>
                            <a href="{{bzf_make_url controller='/Goods/AttrGroup/Edit' meta_id=$goodsAttrGroupItem['meta_id']}}"
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