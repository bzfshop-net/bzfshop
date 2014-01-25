{{extends file='goods_edit_layout.tpl'}}
{{block name=goods_edit_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_edit_tab_left li:has(a[href='{{bzf_make_url controller='/Goods/Edit/Gallery' goods_id=$goods_id }}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '商品相册', link: window.location.href});
    </script>
    <!-- 左侧每个标签的具体内容 -->
    <div class="tab-content">
        <div class="tab-pane well active">

            <!-- 商品图片集展示 -->
            <div id="goods_edit_gallery_panel" class="row" data-toggle="modal-gallery"
                 data-target="#modal-gallery" data-selector="div.gallery-item">
                <h6>&nbsp;&nbsp;注：建议原始图片大小为 800x800 兼容淘宝、天猫的图片尺寸</h6>
                <h6>&nbsp;&nbsp;注：商品图片3个尺寸，原始图 800x800，头图 460x460，缩略图 300x300</h6>

                <!-- 图片上传 -->
                <div class="row">
                    <button id="goods_edit_gallery_upload_image_batch" class="btn btn-success"
                            style="margin-left: 10px;">批量上传图片
                    </button>

                    <form id="goods_edit_gallery_fetch_image_form" class="form-inline pull-right"
                          style="display:inline-block;" method="POST"
                          action="{{bzf_make_url controller='/Goods/Edit/Gallery/Fetch'}}">

                        <input type="hidden" id="goods_edit_gallery_upload_image_batch_goodsid" name="goods_id"
                               value="{{$goods_id}}"/>

                        <div class="control-group operate-panel" style="margin-top: 5px;">
                            <div class="controls">
                                <label class="label label-info">网络图片地址</label>
                                <input type="text" class="span5" name="imageUrl" rel="tooltip" data-placement="top"
                                       data-title="请输入以 http:// 开头的绝对地址"
                                       data-validation-required-message="网络地址不能为空"/>
                                <button type="button" class="btn btn-primary"
                                        onclick="this.disabled=true;jQuery('#goods_edit_gallery_fetch_image_form').submit();">
                                    从网络抓取
                                </button>
                            </div>
                        </div>

                    </form>

                </div>
                <!-- 图片上传 -->

                {{if isset($goodsGalleryArray)}}
                    {{foreach $goodsGalleryArray as $goodsGalleryItem}}

                        <!-- 一张图片 -->
                        <div class="span2 thumbnail gallery-item">
                            <!-- 图片 -->
                            <div class="image-container">
                                <a rel="prettyPhoto[gallery]"
                                   href="{{$goodsGalleryItem['img_original']}}">
                                    <img class="lazyload" width="196"
                                         src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                         alt="{{$goodsGalleryItem['img_desc']}}"
                                         data-original="{{$goodsGalleryItem['thumb_url']}}"/>
                                </a>
                            </div>

                            <!-- 操作区 -->
                            <form method="POST" action="Gallery/Update">
                                <div class="control-group operate-panel" style="margin-top: 5px;">
                                    <div class="controls">
                                        <input type="hidden" name="img_id" value="{{$goodsGalleryItem['img_id']}}"/>
                                        <input class="span2" type="text" rel="tooltip" data-placement="top"
                                               name="img_desc"
                                               value="{{$goodsGalleryItem['img_desc']}}"
                                               data-title="图片的描述信息"/>
                                        <input type="text" class="image-sort-order" rel="tooltip" data-placement="top"
                                               pattern="[0-9]+"
                                               data-validation-pattern-message="排序必须是数字"
                                               name="img_sort_order"
                                               value="{{$goodsGalleryItem['img_sort_order']}}"
                                               data-title="图片的排序，数字越大排序越前"/>
                                        <button type="submit" class="btn btn-mini btn-success">提交修改
                                        </button>
                                        <a href="{{bzf_make_url controller='/Goods/Edit/Gallery/Remove' img_id=$goodsGalleryItem['img_id'] }}"
                                           onclick="return confirm('你确定要删除图片吗？')"
                                           class="btn btn-mini btn-danger">删除图片</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- /一张图片 -->

                    {{/foreach}}
                {{/if}}

            </div>

        </div>
        <!-- /左侧每个标签的具体内容 -->

    </div>
    <!-- /商品编辑页面主体内容 -->

{{/block}}

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {

            /*********** goods_edit_gallery.tpl  商品编辑页面，商品相册批量上传图片 ***********/
            bZF.loadKindEditorTheme();

            $('#goods_edit_gallery_upload_image_batch').click(function () {
                var editor = KindEditor.editor({
                    allowFileManager: true,
                    formatUploadUrl: false,
                    uploadJson: bZF.makeUrl('/Goods/Edit/Gallery/Upload'),
                    extraFileUploadParams: {
                        bzfshop_auth_cookie_key: $.cookie(WEB_COOKIE_AUTH_KEY),
                        goods_id: $('#goods_edit_gallery_upload_image_batch_goodsid').val()
                    }
                });
                editor.loadPlugin('multiimage', function () {
                    editor.plugin.multiImageDialog({
                        clickFn: function (urlList) {
                            //刷新整个页面
                            document.location.reload();
                            editor.hideDialog();
                        }
                    });
                });
            });

        })(window, jQuery));
    </script>
{{/block}}
