<link rel="stylesheet" type="text/css"
      href="{{bzf_get_asset_url asset='bootstrap-custom/css/bootstrap-1206.css'}}"/>

<!--[if lte IE 6]>
<link rel="stylesheet" type="text/css"
      href="{{bzf_get_asset_url asset='bootstrap-custom/css/bootstrap-1206.ie6.css'}}"/>
<![endif]-->
<!--[if lte IE 7]>
<link rel="stylesheet" type="text/css" href="{{bzf_get_asset_url asset='bootstrap-custom/css/ie.css'}}"/>
<![endif]-->

<!-- 合并所有的 Css 文件, 使用 merge=false 参数关闭合并，这样可以对单个文件做调试 -->
{{bzf_dump_merged_asset_css_url
asset='bootstrap-custom/plugin/datetimepicker/datetimepicker.min.css,
       bootstrap-custom/plugin/select2/select2.css,
       bootstrap-custom/plugin/pretty-photo/css/prettyPhoto.css,
       bootstrap-custom/plugin/pretty-loader/css/prettyLoader.css,
       bootstrap-custom/plugin/pnotify/jquery.pnotify.default.css,
       bootstrap-custom/plugin/scroll-modal/scroll-modal.css,
       bootstrap-custom/plugin/fileupload/fileupload.css,
       bootstrap-custom/plugin/sort-table/bootstrap-sortable.css,
       bootstrap-custom/plugin/treetable/css/jquery.treetable.css,
       bootstrap-custom/css/bootstrap-1206.fix.css,
       css/jquery.treetable.theme.bzfshop.css,
       css/manage.css
    '}}
<!-- /合并所有的 Css 文件, 使用 merge=false 参数关闭合并，这样可以对单个文件做调试 -->

<!-- dump 插件注册的 css 文件 -->
{{bzf_dump_register_css}}
<!-- /dump 插件注册的 css 文件 -->
