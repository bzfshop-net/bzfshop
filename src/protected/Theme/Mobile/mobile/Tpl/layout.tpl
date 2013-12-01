<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{$seo_title|default}}</title>
    <meta name="description" content="{{$seo_description|default}}"/>
    <meta name="keywords" content="{{$seo_keywords|default}}"/>
    <meta name="author" content="棒主妇开源"/>

    <!-- 合并所有的 Css 文件, 使用 merge=false 参数关闭合并，这样可以对单个文件做调试 -->
    {{bzf_dump_merged_asset_css_url
    asset='jquery-mobile/theme/bzf-default.min.css,
    jquery-mobile/theme/bzf-default-asset.css
    '}}
    <!-- /合并所有的 Css 文件, 使用 merge=false 参数关闭合并，这样可以对单个文件做调试 -->
    <link rel="stylesheet" type="text/css"
          href="{{bzf_get_asset_url asset='jquery-mobile/css/jquery.mobile.structure-1.3.1.min.css'}}"/>
    <link rel="stylesheet" type="text/css" href="{{bzf_get_asset_url asset='css/mobile.css'}}"/>

    <script>
        var WEB_ROOT_HOST = '{{$WEB_ROOT_HOST}}';
        var WEB_ROOT_BASE = '{{$WEB_ROOT_BASE}}';
        var WEB_ROOT_BASE_RES = '{{$WEB_ROOT_BASE_RES}}';
        var FLASH_MESSAGE_STR = '';
        var USER_NAME_DISPLAY = '{{$USER_NAME_DISPLAY|default}}';
        var ZOOM_IMAGE_PLACEHOLDER = '{{bzf_get_asset_url asset='img/lazyload_placeholder_460_344.png'}}';
        var SESSION_NAME = '{{session_name()}}';
    </script>

    <!-- 合并所有的 JS 文件, 使用 merge=false 参数关闭合并，这样可以对单个文件做调试 -->
    {{bzf_dump_merged_asset_js_url
    asset='jquery-mobile/js/jquery-1.9.1.min.js,
    jquery-mobile/js/jquery.lazyload.fix.min.js,
    js/mobile.js
    '}}
    <!-- /合并所有的 JS 文件, 使用 merge=false 参数关闭合并，这样可以对单个文件做调试 -->

    <script src="{{bzf_get_asset_url asset='jquery-mobile/js/jquery.mobile-1.3.1.min.js'}}"></script>
    <script src="{{bzf_get_asset_url asset='jquery-mobile/plugin/route/jquery.mobile.router.min.js'}}"></script>
</head>
<body>

<!-- =============================  网页主体内容  =========================================================== -->

{{block name=main_body}}{{/block}}

<!-- =============================  /网页主体内容  =========================================================== -->

</body>
</html>
