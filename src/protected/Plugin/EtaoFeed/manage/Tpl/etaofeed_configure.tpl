{{extends file='plugin_layout.tpl'}}
{{block name=plugin_main_body}}
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>EtaoFeed配置</h4>
        <br/>

        <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
              style="margin: 0px 0px 0px 0px;">

            <div class="well">

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商家ID</span>
                        <input class="span6" type="text" name="etaofeed_seller_id"
                               value="{{$etaofeed_seller_id|default}}"
                               data-validation-required="data-validation-required"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品链接参数</span>
                        <input class="span6" type="text" name="etaofeed_goods_url_extra_param"
                               value="{{$etaofeed_goods_url_extra_param|default}}"/>
                    </div>
                </div>

            </div>

            <div class="control-group">
                <label class="control-label">&nbsp; </label>

                <div class="controls">
                    <button type="submit" class="btn btn-success">
                        保存设置
                    </button>
                </div>
            </div>

        </form>
        <!-- /更新管理员信息的表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}
