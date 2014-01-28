<!-- 定义网站的起始路径，用于 JavaScript 的 Ajax 操作调用 -->
<script type="text/javascript">
    var WEB_ROOT_HOST = '{{$WEB_ROOT_HOST}}';
    var WEB_ROOT_BASE = '{{$WEB_ROOT_BASE}}';
    var WEB_ROOT_BASE_RES = '{{$WEB_ROOT_BASE_RES}}';
</script>

<!-- 合并所有的 JS 文件, 使用 merge=false 参数关闭合并，这样可以对单个文件做调试 -->
{{bzf_dump_merged_asset_js_url
asset='bootstrap-custom/js/json2.js,
       bootstrap-custom/js/jquery-1.8.3.min.js,
       bootstrap-custom/js/jquery.cookie.js,
       bootstrap-custom/js/jquery.lazyload.js,
       bootstrap-custom/js/jstorage.min.js,
       bootstrap-custom/js/bootstrap.min.js,
       bootstrap-custom/js/bootstrap.ie.js,
       bootstrap-custom/js/validator.js,
       bootstrap-custom/plugin/datetimepicker/datetimepicker.min.js,
       bootstrap-custom/plugin/select2/select2.min.js,
       bootstrap-custom/plugin/select2/select2_locale_zh-CN.js,
       bootstrap-custom/plugin/dirtyform/jquery.are-you-sure.js,
       bootstrap-custom/plugin/pretty-photo/js/jquery.prettyPhoto.js,
       bootstrap-custom/plugin/bootbox/bootbox.min.js,
       bootstrap-custom/plugin/hover-dropdown/hover-dropdown.min.js,
       bootstrap-custom/plugin/pnotify/jquery.pnotify.min.js,
       bootstrap-custom/plugin/scroll-modal/scroll-modal.js,
       bootstrap-custom/plugin/fileupload/si.files.js,
       bootstrap-custom/plugin/clickover/bootstrapx-clickover.js,
       bootstrap-custom/plugin/pretty-loader/js/jquery.prettyLoader.js,
       js/supplier.js
'}}
<!-- /合并所有的 JS 文件, 使用 merge=false 参数关闭合并，这样可以对单个文件做调试 -->
