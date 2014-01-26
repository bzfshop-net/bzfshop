{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Brand/ListBrand'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '商品品牌', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>商品品牌</h4>
        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">
                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">品牌名称</span>
                        <input class="span2" type="text" name="brand_name"
                               value="{{$brand_name|default}}"/>
                        <span class="input-label">品牌描述</span>
                        <input class="span2" type="text" name="brand_desc"
                               value="{{$brand_desc|default}}"/>
                        <span class="input-label">自定义页面</span>
                        <select class="span1 select2-simple" name="is_custom"
                                data-placeholder="全部" data-initValue="{{$is_custom|default}}">
                            <option value=""></option>
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                        &nbsp;&nbsp;
                        <button type="submit" class="btn btn-success">查询</button>
                        &nbsp;&nbsp;
                        <a href="{{bzf_make_url controller='/Goods/Brand/Create'}}" class="btn btn-info">新建</a>
                    </div>
                </div>
            </form>
        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 管理员列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>品牌ID</th>
                <th>品牌Logo</th>
                <th>品牌名称</th>
                <th>品牌描述</th>
                <th>自定义页面</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($goodsBrandArray)}}
                {{foreach $goodsBrandArray as $goodsBrand}}
                    <!-- 一个品牌 -->
                    <tr>
                        <td>{{$goodsBrand['brand_id']}}</td>
                        <td>
                            {{if !empty($goodsBrand['brand_logo'])}}
                                <img class="lazyload" width="{{bzf_get_sysconfig key='brand_logo_width'}}"
                                     style="width:{{bzf_get_sysconfig key='brand_logo_width'}}px;height:{{bzf_get_sysconfig key='brand_logo_height'}}px;"
                                     src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                     data-original="{{$goodsBrand['brand_logo']}}"/>
                            {{/if}}
                        </td>
                        <td>{{$goodsBrand['brand_name']}}</td>
                        <td>{{$goodsBrand['brand_desc']|nl2br}}</td>
                        <td>
                            {{if $goodsBrand['is_custom'] > 0}}
                                <i class="icon-ok"></i>
                            {{else}}
                                <i class="icon-remove"></i>
                            {{/if}}
                        </td>
                        <td>
                            <a href="{{bzf_make_url controller='/Goods/Brand/Edit' brand_id=$goodsBrand['brand_id']}}"
                               class="btn btn-small">编辑</a>
                        </td>
                    </tr>
                    <!-- /一个品牌 -->
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