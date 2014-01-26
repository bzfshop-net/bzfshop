{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_tabbar li:has(a[href='{{bzf_make_url controller='/Goods/Brand/ListBrand'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '品牌详情', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>品牌详情</h4>

        <!-- 更新商品品牌的表单  -->
        <form class="form-horizontal" method="POST" action="Edit?brand_id={{$brand_id|default}}">

            <!-- 商品品牌详细信息 -->
            <div class="row">

                <div class="control-group">
                    <label class="control-label">品牌名称</label>

                    <div class="controls">
                        <input class="span3" type="text" name="brand_name" value="{{$brand_name|default}}"
                               data-validation-required-message="不能为空"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">品牌描述</label>

                    <div class="controls">
                        <textarea class="span5" rows="3" cols="20"
                                  data-validation-required-message="不能为空"
                                  name="brand_desc">{{$brand_desc|default}}</textarea>
                    </div>

                </div>

                <div class="control-group">
                    <label class="control-label">品牌Logo</label>

                    <div class="controls">
                        <!-- 一张图片 -->
                        <div class="thumbnail gallery-item" style="float:left;width:100px;">
                            <!-- 图片 -->
                            <div class="image-container">
                                <img id="goods_brand_edit_upload_brand_logo"
                                     class="lazyload" width="{{bzf_get_sysconfig key='brand_logo_width'}}"
                                     style="width:{{bzf_get_sysconfig key='brand_logo_width'}}px;height:{{bzf_get_sysconfig key='brand_logo_height'}}px;"
                                     src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                     data-original="{{$brand_logo|default}}"/>
                            </div>
                        </div>
                        <!-- /一张图片 -->
                        <input id="goods_brand_edit_upload_brand_logo_input"
                               name="brand_logo" type="hidden"
                               value="{{$brand_logo|default}}"/>
                        &nbsp;&nbsp;
                        <button id="goods_brand_edit_upload_brand_logo_button" type="button"
                                class="btn btn-small btn-success">上传图片
                        </button>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">自定义页面</label>

                    <div class="controls">
                        <select class="span2 select2-simple" name="is_custom"
                                data-initValue="{{$is_custom|default}}">
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                    </div>
                </div>

                <div class="control-group" style="margin-top: 15px;">
                    <label class="control-label">页面内容</label>

                    <div class="controls">
                        <textarea id="goods_brand_edit_custom_page_textarea" class="span9" style="height:600px;"
                                  rows="5" cols="20" data-no-validation="data-no-validation"
                                  name="custom_page">{{$custom_page|default nofilter}}</textarea>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">&nbsp; </label>

                    <div class="controls">
                        <button type="submit" class="btn btn-success">
                            提交
                        </button>
                    </div>
                </div>

            </div>
            <!-- /商品品牌详细信息 -->

        </form>
        <!-- /更新商品品牌的表单  -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {
            /******************* goods_brand_edit.tpl 商品品牌页面编辑 ******************/
            KindEditor.create('#goods_brand_edit_custom_page_textarea', {
                filterMode: true,
                themeType: 'default',
                cssData: "body {font-family: '微软雅黑', 'Microsoft Yahei', '宋体', 'songti', STHeiti, Helmet, Freesans, sans-serif;font-size: 15px; }",
                uploadJson: bZF.makeUrl('/File/KindEditor?action=upload&dirname=image_other'), // '/File/Upload'
                fileManagerJson: bZF.makeUrl('/File/KindEditor?action=manage&dirname=image_other'),
                extraFileUploadParams: {
                    bzfshop_auth_cookie_key: $.cookie(WEB_COOKIE_AUTH_KEY)
                },
                formatUploadUrl: false,
                allowFileManager: true,
                width: $('#goods_brand_edit_custom_page_textarea').outerWidth(false)
            });

            /*****************  goods_brand_edit.tpl 商品品牌 上传 logo 图片 *********************/
            bZF.uploadImage('#goods_brand_edit_upload_brand_logo_button',
                    function (clickObject, url, title, width, height, border, align) {
                        $('#goods_brand_edit_upload_brand_logo').attr('src', url);
                        $('#goods_brand_edit_upload_brand_logo_input').val(url);
                    }, 'image_other');
        })(window, jQuery));
    </script>
{{/block}}