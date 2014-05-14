{{extends file='plugin_layout.tpl'}}
{{block name=plugin_main_body}}
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>Mobile 主题配置</h4>
        <br/>
        <!-- 更新管理员信息的表单  -->
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
                        <span class="input-label">GA UA</span>
                        <input class="span2" type="text" name="google_analytics_ua"
                               value="{{$google_analytics_ua|default}}"/>
                        <span class="comments">Google Analytics 的 UA 值，在这里开启 GA 的手机统计</span>
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
                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="系统设计的一个 Cache 页面">Cache页</span>
                        <input class="span2" type="text" name="smarty_cache_time_cache_page"
                               value="{{$smarty_cache_time_cache_page|default}}"
                               pattern="[0-9]+" data-validation-pattern-message="必须是整数"
                               data-validation-required="data-validation-required"/>
                        <span class="input-label">首页</span>
                        <input class="span2" type="text" name="smarty_cache_time_goods_index"
                               value="{{$smarty_cache_time_goods_index|default}}"
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
                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="系统设计的一个 Cache 页面">商品详情页面</span>
                        <input class="span2" type="text" name="smarty_cache_time_goods_view"
                               value="{{$smarty_cache_time_goods_view|default}}"
                               pattern="[0-9]+" data-validation-pattern-message="必须是整数"
                               data-validation-required="data-validation-required"/>
                        <span class="input-label">商品购买页面</span>
                        <input class="span2" type="text" name="smarty_cache_time_goods_buy"
                               value="{{$smarty_cache_time_goods_buy|default}}"
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
        <!-- /更新管理员信息的表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}
