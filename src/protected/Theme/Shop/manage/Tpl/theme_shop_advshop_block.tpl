{{extends file='theme_shop_advshop_layout.tpl'}}
{{block name=theme_shop_advindex_body}}
    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#theme_shop_advindex_left_tabbar li:has(a[href='{{bzf_make_url controller='/Theme/Shop/AdvShopBlock'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 2, text: '区块广告', link: window.location.href});
    </script>
    <div class="row">


    <form class="form-horizontal form-horizontal-inline" method="POST" style="margin: 0px 0px 0px 0px;">

    <!-- 左侧每个标签的具体内容 -->
    <div class="tab-content">

    {{if !isset($shop_index_advblock_json_data)}}
    <!-- 一个缺省的广告设置 -->
    <div class="tab-pane active">

        <!-- 不同的广告块切换 -->
        <ul id="theme_shop_adv_block_tabbar" class="nav nav-tabs">
            <li class="active">
                <a href="#theme_shop_adv_block_1" data-toggle="tab">
                    女装系列<span class="badge badge-warning"><i class="icon-info-sign"></i></span>
                </a>
            </li>
            <li>
                <a>
                    <button type="button" class="btn btn-mini btn-info"
                            onclick="bZF.themeShop.cloneAdvBlockTab(jQuery('li',this.parentNode.parentNode.parentNode).first());">
                        <i class="icon-plus"></i>
                    </button>
                </a>
            </li>
        </ul>
        <!-- /不同的广告块切换 -->

        <div class="tab-content">

            <!-- 一个广告块内容 -->
            <div id="theme_shop_adv_block_1" class="tab-pane well active">

                <div class="row" style="padding: 0px 0px 20px 0px;">
                    <span style="float:left;font-size:14px;font-weight: bold;padding-right: 10px;">选择主题：</span>
                    <select data-initValue='bzf_shop_index_adv_block_theme_red'
                            class="span2 bzf_shop_index_adv_block_theme_select">
                        <option value="bzf_shop_index_adv_block_theme_red">红色主题</option>
                        <option value="bzf_shop_index_adv_block_theme_yellow">黄色主题</option>
                        <option value="bzf_shop_index_adv_block_theme_blue">蓝色主题</option>
                        <option value="bzf_shop_index_adv_block_theme_pink">粉红主题</option>
                    </select>
                </div>

                <!-- 一个分类展示区 -->
                <div class="row bzf_shop_index_adv_block">

                    <!-- 头部标签切换 -->
                    <ul class="nav nav-tabs">
                        <li><a class="bzf_caption" href="#" onclick="return false;"><span>1F</span>女装系列</a>
                        </li>
                        <li class="active">
                            <a href="#bzf_shop_index_adv_block_1_2" data-toggle="tab">
                                上衣裙子<span class="badge badge-warning"><i class="icon-info-sign"></i></span>
                            </a>

                        </li>
                        <li>
                            <a>
                                <button type="button" class="btn btn-mini btn-info"
                                        onclick="bZF.themeShop.cloneAdvBlockTab(jQuery('li',this.parentNode.parentNode.parentNode).first().next());">
                                    <i class="icon-plus"></i>
                                </button>
                            </a>
                        </li>
                    </ul>
                    <!-- /头部标签切换 -->

                    <!-- 标签对应内容 -->
                    <div class="tab-content">
                        <!-- 一个广告 block 的图片区 -->
                        <div id="bzf_shop_index_adv_block_1_2"
                             class="tab-pane active bzf_shop_index_adv_image_block">

                            <!-- 左侧小图片 -->
                            <div class="span3">
                                <a class="image_left" href="#"
                                   data-target="_blank" data-url="#"
                                   data-image="{{theme_shop_get_asset_url asset='img/placeholder_238x490.gif'}}">
                                    <img src="{{theme_shop_get_asset_url asset='img/placeholder_238x490.gif'}}"/>
                                </a>
                            </div>
                            <!-- /左侧小图片 -->

                            <!-- 中间大图 -->
                            <div class="span6">
                                <a class="image_center" href="#"
                                   data-target="_blank" data-url="#"
                                   data-image="{{theme_shop_get_asset_url asset='img/placeholder_490x490.gif'}}">
                                    <img src="{{theme_shop_get_asset_url asset='img/placeholder_490x490.gif'}}"/>
                                </a>
                            </div>
                            <!-- /中间大图 -->

                            <!-- 右侧小图片 -->
                            <div class="span3">
                                <a class="image_right" href="#"
                                   data-target="_blank" data-url="#"
                                   data-image="{{theme_shop_get_asset_url asset='img/placeholder_238x158.gif'}}">
                                    <img src="{{theme_shop_get_asset_url asset='img/placeholder_238x158.gif'}}"/>
                                </a>
                                <a class="image_right" href="#"
                                   data-target="_blank" data-url="#"
                                   data-image="{{theme_shop_get_asset_url asset='img/placeholder_238x158.gif'}}">
                                    <img src="{{theme_shop_get_asset_url asset='img/placeholder_238x158.gif'}}"/>
                                </a>
                                <a class="image_right" href="#"
                                   data-target="_blank" data-url="#"
                                   data-image="{{theme_shop_get_asset_url asset='img/placeholder_238x158.gif'}}">
                                    <img src="{{theme_shop_get_asset_url asset='img/placeholder_238x158.gif'}}"/>
                                </a>
                            </div>
                            <!-- /右侧小图片 -->

                        </div>
                        <!-- /一个广告 block 的图片区 -->

                    </div>
                    <!-- /标签对应内容 -->

                </div>
                <!-- /一个分类展示区 -->


            </div>
            <!-- /一个广告块内容 -->

        </div>


    </div>
    <!-- /一个缺省的广告设置 -->
    {{else}}

    <!-- 广告设置 -->
    <div class="tab-pane active">

        <!-- 不同的广告块切换 -->
        <ul id="theme_shop_adv_block_tabbar" class="nav nav-tabs">
            {{assign var="advBlockObjectIndex" value=0}}
            {{foreach $shop_index_advblock_json_data as $advBlockObject}}
                {{if 0 == $advBlockObjectIndex}}
                    <li class="active">
                        {{else}}
                    <li>
                {{/if}}
                <a href="#{{$advBlockObject['id']}}" data-toggle="tab">
                    {{$advBlockObject['title']}}<span class="badge badge-warning"><i class="icon-info-sign"></i></span>
                </a>
                </li>
                {{assign var="advBlockObjectIndex" value=$advBlockObjectIndex+1 }}
            {{/foreach}}
            <li>
                <a>
                    <button type="button" class="btn btn-mini btn-info"
                            onclick="bZF.themeShop.cloneAdvBlockTab(jQuery('li',this.parentNode.parentNode.parentNode).first());">
                        <i class="icon-plus"></i>
                    </button>
                </a>
            </li>
        </ul>
        <!-- /不同的广告块切换 -->

        <div class="tab-content">

            {{assign var="advBlockObjectIndex" value=0}}
            {{foreach $shop_index_advblock_json_data as $advBlockObject}}

            <!-- 一个广告块内容 -->
            {{if 0 == $advBlockObjectIndex}}
            <div id="{{$advBlockObject['id']}}" class="tab-pane well active">
                {{else}}
                <div id="{{$advBlockObject['id']}}" class="tab-pane well">
                    {{/if}}

                    <div class="row" style="padding: 0px 0px 20px 0px;">
                        <span style="float:left;font-size:14px;font-weight: bold;padding-right: 10px;">选择主题：</span>
                        <select data-initValue="{{$advBlockObject['theme_class']}}"
                                class="span2 bzf_shop_index_adv_block_theme_select">
                            <option value="bzf_shop_index_adv_block_theme_red">红色主题</option>
                            <option value="bzf_shop_index_adv_block_theme_yellow">黄色主题</option>
                            <option value="bzf_shop_index_adv_block_theme_blue">蓝色主题</option>
                            <option value="bzf_shop_index_adv_block_theme_pink">粉红主题</option>
                        </select>
                    </div>

                    <!-- 一个分类展示区 -->
                    <div class="row bzf_shop_index_adv_block">

                        <!-- 头部标签切换 -->
                        <ul class="nav nav-tabs">
                            <li><a class="bzf_caption" href="#"
                                   onclick="return false;"><span>1F</span>{{$advBlockObject['title']}}</a>
                            </li>

                            {{assign var="advBlockImageArray" value=$advBlockObject['advBlockImageArray'] }}
                            {{assign var="advBlockImageArrayIndex" value=0}}
                            {{foreach $advBlockImageArray as $advBlockImageItem}}
                                {{if 0 == $advBlockImageArrayIndex}}
                                    <li class="active">
                                        {{else}}
                                    <li>
                                {{/if}}
                                <a href="#{{$advBlockImageItem['id']}}" data-toggle="tab">
                                    {{$advBlockImageItem['title']}}<span class="badge badge-warning"><i
                                                class="icon-info-sign"></i></span>
                                </a>
                                </li>
                                {{assign var="advBlockImageArrayIndex" value=$advBlockImageArrayIndex+1}}
                            {{/foreach}}

                            <li>
                                <a>
                                    <button type="button" class="btn btn-mini btn-info"
                                            onclick="bZF.themeShop.cloneAdvBlockTab(jQuery('li',this.parentNode.parentNode.parentNode).first().next());">
                                        <i class="icon-plus"></i>
                                    </button>
                                </a>
                            </li>
                        </ul>
                        <!-- /头部标签切换 -->

                        <!-- 标签对应内容 -->
                        <div class="tab-content">

                            {{assign var="advBlockImageArray" value=$advBlockObject['advBlockImageArray'] }}
                            {{assign var="advBlockImageArrayIndex" value=0}}
                            {{foreach $advBlockImageArray as $advBlockImageItem}}

                            <!-- 一个广告 block 的图片区 -->

                            {{if 0 == $advBlockImageArrayIndex}}
                            <div id="{{$advBlockImageItem['id']}}"
                                 class="tab-pane active bzf_shop_index_adv_image_block">
                                {{else}}
                                <div id="{{$advBlockImageItem['id']}}" class="tab-pane bzf_shop_index_adv_image_block">
                                    {{/if}}

                                    <!-- 左侧小图片 -->
                                    <div class="span3">
                                        {{foreach $advBlockImageItem['image_left'] as $imageItem}}
                                            <a class="image_left" href="#"
                                               data-target="{{$imageItem['target']}}" data-url="{{$imageItem['url']}}"
                                               data-image="{{$imageItem['image']}}">
                                                <img src="{{$imageItem['image']}}"/>
                                            </a>
                                        {{/foreach}}
                                    </div>
                                    <!-- /左侧小图片 -->

                                    <!-- 中间大图 -->
                                    <div class="span6">
                                        {{foreach $advBlockImageItem['image_center'] as $imageItem}}
                                            <a class="image_center" href="#"
                                               data-target="{{$imageItem['target']}}" data-url="{{$imageItem['url']}}"
                                               data-image="{{$imageItem['image']}}">
                                                <img src="{{$imageItem['image']}}"/>
                                            </a>
                                        {{/foreach}}
                                    </div>
                                    <!-- /中间大图 -->

                                    <!-- 右侧小图片 -->
                                    <div class="span3">
                                        {{foreach $advBlockImageItem['image_right'] as $imageItem}}
                                            <a class="image_right" href="#"
                                               data-target="{{$imageItem['target']}}" data-url="{{$imageItem['url']}}"
                                               data-image="{{$imageItem['image']}}">
                                                <img src="{{$imageItem['image']}}"/>
                                            </a>
                                        {{/foreach}}
                                    </div>
                                    <!-- /右侧小图片 -->

                                </div>
                                <!-- /一个广告 block 的图片区 -->

                                {{assign var="advBlockImageArrayIndex" value=$advBlockImageArrayIndex+1}}
                                {{/foreach}}

                            </div>
                            <!-- /标签对应内容 -->

                        </div>
                        <!-- /一个分类展示区 -->


                    </div>
                    <!-- /一个广告块内容 -->

                    {{assign var="advBlockObjectIndex" value=$advBlockObjectIndex+1 }}
                    {{/foreach}}

                </div>


            </div>
            <!-- /广告设置 -->

            {{/if}}

        </div>
        <!-- /左侧每个标签的具体内容 -->

        <!-- 隐藏的编码值 -->
        <input id="theme_shop_advblock_json_data" type="hidden" name="shop_index_advblock_json_data" value=""/>
        <!-- /隐藏的编码值 -->

        <!-- 提交按钮 -->
        <div class="row" style="text-align: center;">
            <button type="submit" class="btn btn-success" onclick="bZF.themeShop.advblock_data_submit();">确认提交
            </button>
        </div>
        <!-- /提交按钮 -->

    </form>


    <!-- image 属性设置 -->
    <div id="theme_shop_advblock_image_property_modal"
         class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">广告属性</h3>
        </div>
        <div class="modal-body">

            <button id="theme_shop_advblock_image_upload_button"
                    class="btn btn-small btn-success" type="button">上传图片
            </button>

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
                    onclick="bZF.themeShop.confirm_advblock_image_property_modal();return false;">
                保存
            </button>
        </div>
    </div>
    <!-- /image 属性设置 -->


    </div>
{{/block}}
