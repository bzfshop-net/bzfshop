{{extends file='theme_shop_advshop_layout.tpl'}}
{{block name=theme_shop_advindex_body}}
    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#theme_shop_advindex_left_tabbar li:has(a[href='{{bzf_make_url controller='/Theme/Shop/AdvShopSlider'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 2, text: '滑动图片', link: window.location.href});
    </script>
    <div class="row">


        <form class="form-horizontal form-horizontal-inline" method="POST" style="margin: 0px 0px 0px 0px;">

            <!-- 左侧每个标签的具体内容 -->
            <div class="tab-content">

                <!-- 广告设置 -->
                <div class="tab-pane well active">

                    <!-- 分割条 -->
                    <div class="row inline-divider">
                        <div class="divider"></div>
                        <label class="label label-info">顶部轮换图片</label>
                    </div>
                    <!-- /分割条 -->

                    <button id="theme_shop_slider_image_upload_button"
                            class="btn btn-small btn-success" type="button">上传图片
                    </button>
                    <span>图片尺寸 780x350 </span>

                    <!-- 图片列表 -->
                    <div id="theme_shop_slider_image_list" class="row">

                        {{foreach $shop_index_adv_slider as $sliderItem}}
                            <div class="row theme_shop_slider_image_container">
                                <div class="span8 theme_shop_slider_image_wrapper">
                                    <a target="_blank" href="{{$sliderItem['url']}}">
                                        <img src="{{$sliderItem['image']}}"/>
                                    </a>
                                    <input type="hidden" name="image[]"
                                           value="{{$sliderItem['image']}}"/>
                                    <input type="hidden" name="url[]" value="{{$sliderItem['url']}}"/>
                                    <input type="hidden" name="target[]" value="{{$sliderItem['target']}}"/>
                                </div>
                                <div class="span1 theme_shop_slider_image_toolbar">
                                    <button type="button" class="btn btn-mini btn-info"
                                            onclick="bZF.moveNodePrev(this.parentNode.parentNode);return false;">
                                        <i class="icon-arrow-up"></i>
                                    </button>
                                    <button type="button" class="btn btn-mini btn-danger"
                                            onclick="bZF.removeNode(this.parentNode.parentNode);return false;">
                                        <i class="icon-remove"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-mini btn-success theme_shop_slide_image_property_button"
                                            onclick="bZF.themeShop.open_slider_image_property_modal(this.parentNode.parentNode);return false;">
                                        <i class="icon-info-sign"></i>
                                    </button>
                                    <button type="button" class="btn btn-mini btn-info"
                                            onclick="bZF.moveNodeNext(this.parentNode.parentNode);return false;">
                                        <i class="icon-arrow-down"></i>
                                    </button>
                                </div>
                            </div>
                        {{/foreach}}

                    </div>
                    <!-- 图片列表 -->

                </div>
                <!-- /广告设置 -->

            </div>
            <!-- /左侧每个标签的具体内容 -->

            <!-- 提交按钮 -->
            <div class="row" style="text-align: center;">
                <button type="submit" class="btn btn-success">确认提交</button>
            </div>
            <!-- /提交按钮 -->

        </form>

        <!-- 用于增加图片时候 clone -->
        <div id="theme_shop_slider_image_container_clone" class="row theme_shop_slider_image_container">
            <div class="span8 theme_shop_slider_image_wrapper">
                <a target="_blank" href="#">
                    <img src="{{theme_shop_get_asset_url asset='img/placeholder_780x350_gray.gif'}}"/>
                </a>
                <input type="hidden" name="image[]"
                       value="{{theme_shop_get_asset_url asset='img/placeholder_780x350_gray.gif'}}"/>
                <input type="hidden" name="url[]" value="#"/>
                <input type="hidden" name="target[]" value="_blank"/>
            </div>
            <div class="span1 theme_shop_slider_image_toolbar">
                <button type="button" class="btn btn-mini btn-info"
                        onclick="bZF.moveNodePrev(this.parentNode.parentNode);return false;">
                    <i class="icon-arrow-up"></i>
                </button>
                <button type="button" class="btn btn-mini btn-danger"
                        onclick="bZF.removeNode(this.parentNode.parentNode);return false;">
                    <i class="icon-remove"></i>
                </button>
                <button type="button"
                        class="btn btn-mini btn-success theme_shop_slide_image_property_button"
                        onclick="bZF.themeShop.open_slider_image_property_modal(this.parentNode.parentNode);return false;">
                    <i class="icon-info-sign"></i>
                </button>
                <button type="button" class="btn btn-mini btn-info"
                        onclick="bZF.moveNodeNext(this.parentNode.parentNode);return false;">
                    <i class="icon-arrow-down"></i>
                </button>
            </div>
        </div>
        <!-- /用于增加图片时候 clone -->

        <!-- slide image 属性设置 -->
        <div id="theme_shop_slider_image_property_modal"
             class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">广告属性</h3>
            </div>
            <div class="modal-body">

                <form class="form-horizontal form-horizontal-inline" method="POST">
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">图片URL</span>
                            <input type="text" class="span4" name="image" value="#"
                                   data-validation-required-message="URL不能为空"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">跳转链接</span>
                            <input type="text" class="span4" name="url" value="#"
                                   data-validation-required-message="URL不能为空"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <span class="input-label">新窗口打开</span>
                            <input type="checkbox" checked="checked" name="target" value="1"/>
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">关闭</button>
                <button class="btn btn-success"
                        onclick="bZF.themeShop.confirm_slider_image_property_modal();return false;">
                    保存
                </button>
            </div>
        </div>
        <!-- /slide image 属性设置 -->

    </div>
{{/block}}
