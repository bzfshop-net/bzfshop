{{extends file='layout.tpl'}}
{{block name=main_body}}
    <div class="row bz_basic_content_block bz_box_shadow" style="padding:10px 10px 10px 10px;">

        <!-- 页面主体内容 -->
        <div class="row" style="height:500px;position: relative;">

            <style>
                .title {
                    color: #555;
                    font-size: 40px;
                    line-height: 80px;
                    text-shadow: 1px 1px 1px rgba(255, 255, 255, .8), 1px 1px 1px rgba(0, 0, 0, .8);
                    position: absolute;
                    top: 160px;
                    left: 280px;
                }
            </style>

            <div class="popover bottom" style="display: block;">
                <div class="arrow"></div>
                <h3 class="popover-title" style="text-align: center;font-weight: bold;">提示</h3>

                <div class="popover-content">
                    <p style="text-align: center;">
                        需要权限：
                        <span rel="tooltip" data-placement="top"
                              data-title="{{$privilegeItem['meta_desc']|default}}"
                              style="font-weight: bold;color:red;">
                                        {{$privilegeItem['meta_name']|default}}
                                    </span>
                    </p>
                </div>
            </div>

            <h1 class="title">对不起，您没有权限 <a href="{{$refer_url|default}}">点击这里返回</a></h1>

        </div>
        <!-- /页面主体内容 -->

    </div>
{{/block}}