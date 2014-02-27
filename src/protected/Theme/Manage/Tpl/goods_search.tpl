{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Search'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 1, text: '商品列表', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <form class="form-horizontal form-horizontal-inline" method="GET" style="margin: 0px 0px 0px 0px;">
                <div class="control-group">
                    <div class="controls">

                        <span class="input-label">商品ID</span>
                        <input class="span1" type="text" pattern="[0-9]*" data-validation-pattern-message="商品ID应该是全数字"
                               name="goods_id" value="{{$goods_id|default}}"/>
                        <span class="input-label">商品名称</span>
                        <input class="span2" type="text" name="goods_name" value="{{$goods_name|default}}"/>

                        <span class="input-label">选择供货商</span>
                        <select class="span2 select2-simple" name="suppliers_id" data-placeholder="供货商列表"
                                data-ajaxCallUrl="{{bzf_make_url controller='/Ajax/Supplier/ListSupplierIdName'}}"
                                data-option-value-key="suppliers_id" data-option-text-key="suppliers_name"
                                data-initValue="{{$suppliers_id|default}}">
                            <option value=""></option>
                        </select>

                        <span class="input-label">选择状态</span>
                        <select class="span2 select2-simple" name="is_on_sale" data-placeholder="商品状态"
                                data-initValue="{{$is_on_sale|default}}">
                            <option value=""></option>
                            <option value="1">销售中</option>
                            <option value="0">已下线</option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls" style="padding-top:8px;">
                        <span class="input-label">商品货号</span>
                        <input class="span1" type="text" name="goods_sn" value="{{$goods_sn|default}}"/>
                        <span class="input-label">管理员</span>
                        <select class="span2 select2-simple" name="admin_user_id" data-placeholder="管理员列表"
                                data-ajaxCallUrl="{{bzf_make_url controller='/Ajax/AdminUser/ListUserIdName'}}"
                                data-option-value-key="user_id" data-option-text-key="user_name"
                                data-initValue="{{$admin_user_id|default}}">
                            <option value=""></option>
                        </select>
                        <span class="input-label" rel="tooltip" data-placement="top"
                              data-title="商品发布到哪些系统里面去">系统Tag</span>
                        <!-- 商品发布到那些系统 -->
                        <select class="span2 select2-simple" name="system_tag"
                                data-placeholder="选择商品发布系统" data-initValue="{{$system_tag|default}}"
                                data-ajaxCallUrl="{{bzf_make_url controller='/Ajax/System/ListSystem'}}"
                                data-option-value-key="system_tag" data-option-text-key="system_name">
                            <option value=""></option>
                        </select>
                        <span class="input-label">商品类型</span>
                        <select class="span2 select2-simple"
                                name="type_id" data-placeholder="商品类型列表"
                                data-ajaxCallUrl="{{bzf_make_url controller='/Ajax/GoodsType/ListType'}}"
                                data-option-value-key="meta_id" data-option-text-key="meta_name"
                                data-initValue="{{$type_id|default}}">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品分类</span>
                        <!-- 商品分类有可能层级很长 -->
                        <select class="span4 select2-simple" name="cat_id"
                                data-placeholder="选择商品分类" data-initValue="{{$cat_id|default}}"
                                data-ajaxCallUrl="{{bzf_make_url controller='/Ajax/Goods/ListCategoryTree'}}"
                                data-option-value-key="meta_id" data-option-text-key="meta_name">
                            <option value=""></option>
                        </select>
                        <span class="input-label">商品品牌</span>
                        <select class="span2 select2-simple" name="brand_id" data-placeholder="选择商品品牌"
                                data-initValue="{{$brand_id|default}}"
                                data-ajaxCallUrl="{{bzf_make_url controller='/Ajax/Brand/ListBrand'}}"
                                data-option-value-key="brand_id" data-option-text-key="brand_name">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls" style="padding-top:8px;">
                        <span class="input-label">仓库</span>
                        <input class="span1" type="text" name="warehouse" value="{{$warehouse|default}}"/>
                        <span class="input-label">货架</span>
                        <input class="span2" type="text" name="shelf" value="{{$shelf|default}}"/>
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

        <!-- 商品列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th width="5%">商品ID</th>
                <th width="15%">商品图片</th>
                <th width="30%">商品名称</th>
                <th width="8%">商品分类</th>
                <th width="10%">供货商</th>
                <th>价格</th>
                <th>库存剩余</th>
                <th width="5%">状态</th>
                <th width="5%">操作</th>
            </tr>
            </thead>
            <tbody>

            {{if isset($goodsArray)}}
                {{foreach $goodsArray as $goodsItem}}

                    <!-- 一个商品 -->
                    {{if 0 == $goodsItem['is_on_sale']}}
                        <tr class="error">
                            {{else}}
                        <tr>
                    {{/if}}
                    <td>{{$goodsItem['goods_id']}}</td>
                    <td>
                        <a rel="clickover" data-placement="top" href="#"
                           data-content="{{bzf_goods_view_toolbar goods_id=$goodsItem['goods_id']  system_tag_list=$goodsItem['system_tag_list']}}">
                            <img class="lazyload" width="150" style="width:150px;height:auto;"
                                 src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                 data-original="{{bzf_goods_thumb_image goods_id=$goodsItem['goods_id']}}"/>
                        </a>
                    </td>
                    <td>
                        {{$goodsItem['goods_name']}}
                        {{if !empty($goodsItem['warehouse'])}}
                            <br/>
                            <br/>
                            {{$goodsItem['warehouse']}}&nbsp;|&nbsp;{{$goodsItem['shelf']}}
                        {{/if}}
                    </td>
                    <td>{{$goodsItem['cat_name']|default}}<br/><br/>[{{$goodsItem['type_name']|default}}]</td>
                    <td>{{$goodsItem['suppliers_name']|default}}</td>
                    <td>
                        销售价：{{$goodsItem['shop_price']|bzf_money_display}} 元<br/>
                        供货价：{{$goodsItem['suppliers_price']|bzf_money_display}} 元<br/>
                        <br/>
                        快递费：{{$goodsItem['shipping_fee']|bzf_money_display}} 元<br/>
                        供货快递：{{$goodsItem['suppliers_shipping_fee']|bzf_money_display}} 元<br/>
                        {{if $goodsItem['shipping_free_number'] > 0}}
                            {{$goodsItem['shipping_free_number']}} 件免邮
                            <br/>
                        {{/if}}
                    </td>
                    <td>
                        {{if empty($goodsItem['goods_spec'])}}
                            {{$goodsItem['goods_number']}}
                        {{else}}
                            {{foreach $goodsItem['goods_spec'] as $specStr => $specDataArray }}
                                {{$specStr}}&nbsp;:&nbsp;{{$specDataArray['goods_number']|default:'0'}}
                                <br/>
                            {{/foreach}}
                        {{/if}}
                    </td>
                    <td>
                        {{foreach $goodsItem['system_array'] as $goodsSystemName}}
                            <label class="label label-success">{{$goodsSystemName}}</label>
                        {{/foreach}}
                        <br/>{{if $goodsItem['is_on_sale'] >= 1}}销售中{{else}}已下线{{/if}}
                    </td>
                    <td>
                        {{$goodsItem['admin_user_name']}}<br/><br/>
                        <a class="btn btn-small"
                           href="{{bzf_make_url controller='/Goods/Edit/Edit' goods_id=$goodsItem['goods_id'] }}">编辑</a>
                        <a class="btn btn-small"
                           href="{{bzf_make_url controller='/Goods/Copy' goods_id=$goodsItem['goods_id'] }}"
                           onclick="return confirm('你确定要复制新建一个商品吗？');">复制</a>
                    </td>
                    </tr><!-- /一个商品 -->

                {{/foreach}}
            {{/if}}

            </tbody>
        </table>
        <!-- /商品列表 -->

        <!-- 分页 -->
        <div class="pagination pagination-right">
            {{bzf_paginator count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
        </div>
        <!-- 分页 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}
