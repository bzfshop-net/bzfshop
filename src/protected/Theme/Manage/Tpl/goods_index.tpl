{{extends file='goods_layout.tpl'}}
{{block name=goods_main_body}}
    <script type="text/javascript">
        window.bz_set_breadcrumb_status.push({index: 0, text: '商品管理', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row" style="height:500px;position: relative;">

        <style>
            .title {
                color: #555;
                font-size: 60px;
                line-height: 80px;
                text-shadow: 1px 1px 1px rgba(255, 255, 255, .8), 1px 1px 1px rgba(0, 0, 0, .8);
                position: absolute;
                top: 160px;
                left: 470px;
            }
        </style>

        <div class="popover bottom" style="display: block;">
            <div class="arrow"></div>
            <h3 class="popover-title" style="text-align: center;font-weight: bold;">提示</h3>

            <div class="popover-content">
                <p style="text-align: center;">请选择上面你需要的操作</p>
            </div>
        </div>

        <h1 class="title">商品管理</h1>

    </div>
    <!-- /页面主体内容 -->

{{/block}}