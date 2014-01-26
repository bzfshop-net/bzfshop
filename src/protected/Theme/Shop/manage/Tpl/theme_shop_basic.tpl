{{extends file='theme_shop_layout.tpl'}}
{{block name=theme_shop_main_body}}
    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#theme_shop_tabbar li:has(a[href='{{bzf_make_url controller='/Theme/Shop/Basic'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 1, text: '基本信息', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>商城基本信息</h4>
        <br/>
        <!-- 更新表单  -->
        <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
              style="margin: 0px 0px 0px 0px;">

            <div class="well">

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">网站名</span>
                        <input class="span9" type="text" name="site_name" value="{{$site_name|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">SEO标题</span>
                        <input class="span9" type="text" name="seo_title" value="{{$seo_title|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">SEO关键词</span>
                        <input class="span9" type="text" name="seo_keywords" value="{{$seo_keywords|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">SEO描述</span>
                        <textarea class="span9" type="text" name="seo_description"
                                  data-validation-required="data-validation-required">{{$seo_description|default}}</textarea>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商户名</span>
                        <input class="span2" type="text" name="merchant_name"
                               value="{{$merchant_name|default}}"
                               data-validation-required="data-validation-required"/>
                        <span class="input-label">商户所在地</span>
                        <input class="span2" type="text" name="merchant_address"
                               value="{{$merchant_address|default}}"
                               data-validation-required="data-validation-required"/>
                        <span class="input-label">ICP备案号</span>
                        <input class="span2" type="text" name="icp"
                               value="{{$icp|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">统计代码</span>
                        <textarea class="span9" type="text" name="statistics_code"
                                  data-validation-required="data-validation-required">{{$statistics_code|default nofilter}}</textarea>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">客服电话</span>
                        <input class="span2" type="text" name="kefu_telephone"
                               value="{{$kefu_telephone|default}}"
                               data-validation-required="data-validation-required"/>
                        <span class="input-label">客服QQ</span>
                        <input class="span2" type="text" name="kefu_qq"
                               value="{{$kefu_qq|default}}"
                               data-validation-required="data-validation-required"/>
                        <span class="input-label">商务QQ</span>
                        <input class="span2" type="text" name="business_qq"
                               value="{{$business_qq|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group" style="margin-top: 10px;">
                    <div class="controls">
                        <span class="input-label">首页公告</span>
                        <textarea class="span6 editor-html-simple" rows="5" cols="20"
                                  name="shop_index_notice">{{$shop_index_notice nofilter}}</textarea>
                    </div>
                </div>
                <div class="control-group" style="margin-top: 10px;">
                    <div class="controls">
                        <span class="input-label">商品详情公告</span>
                        <textarea class="span6 editor-html-simple" rows="5" cols="20"
                                  name="goods_view_detail_notice">{{$goods_view_detail_notice nofilter}}</textarea>
                    </div>
                </div>
                <div class="control-group" style="margin-top: 10px;">
                    <div class="controls">
                        <span class="input-label">售后说明</span>
                        <textarea class="span6 editor-html-simple" rows="5" cols="20"
                                  name="goods_after_service">{{$goods_after_service nofilter}}</textarea>
                    </div>
                </div>

                <!-- 分割条 -->
                <div class="row inline-divider">
                    <div class="divider"></div>
                    <label class="label label-info">页面缓存时间</label>
                </div>
                <!-- /分割条 -->

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">网站首页</span>
                        <input class="span2" type="text" name="smarty_cache_time_shop_index"
                               value="{{$smarty_cache_time_shop_index|default}}"
                               pattern="[0-9]+" data-validation-pattern-message="必须是整数"
                               data-validation-required="data-validation-required"/>
                        <span class="input-label">商品详情页面</span>
                        <input class="span2" type="text" name="smarty_cache_time_goods_view"
                               value="{{$smarty_cache_time_goods_view|default}}"
                               pattern="[0-9]+" data-validation-pattern-message="必须是整数"
                               data-validation-required="data-validation-required"/>
                        <span class="input-label">文章显示页面</span>
                        <input class="span2" type="text" name="smarty_cache_time_article_view"
                               value="{{$smarty_cache_time_article_view|default}}"
                               pattern="[0-9]+" data-validation-pattern-message="必须是整数"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品分类列表</span>
                        <input class="span2" type="text" name="smarty_cache_time_ajax_category"
                               value="{{$smarty_cache_time_ajax_category|default}}"
                               pattern="[0-9]+" data-validation-pattern-message="必须是整数"
                               data-validation-required="data-validation-required"/>
                        <span class="input-label">商品搜索页面</span>
                        <input class="span2" type="text" name="smarty_cache_time_goods_search"
                               value="{{$smarty_cache_time_goods_search|default}}"
                               pattern="[0-9]+" data-validation-pattern-message="必须是整数"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="comments">注意：缓存时间单位为秒，1800 代表 30分钟</span>
                    </div>
                </div>

            </div>

            <!-- 提交按钮 -->
            <div class="row" style="text-align: center;">
                <button type="submit" class="btn btn-success">保存设置</button>
            </div>
            <!-- /提交按钮 -->

        </form>
        <!-- /更新表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}
