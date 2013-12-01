{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 主 Page -->
    <div data-role="page" class="ui-responsive-panel"
         data-url="{{$CURRENT_PAGE_URL}}"
         data-title="{{$seo_title|default}}">

        {{nocache}}
            <script>
                var FLASH_MESSAGE_STR = '{{bzf_flash_message_str}}'; // 输出 flash message
                var USER_NAME_DISPLAY = '{{$USER_NAME_DISPLAY|default}}';
                var SESSION_ID = '{{session_id()}}';
            </script>
        {{/nocache}}

        <!-- Page Header -->
        <div data-role="header" data-theme="f">

            <div data-role="controlgroup" class="ui-btn-left" data-type="horizontal"
                 data-mini="true">
                <a id="page_header_goods_category" href="{{bzf_make_url controller='/Goods/CategoryList'}}"
                   data-role="button"
                   data-icon="bars">商品分类</a>
            </div>

            <h1>{{bzf_get_option_value optionKey='site_name'}}</h1>

            {{nocache}}
                {{if $IS_USER_AUTH}}
                    <!-- 我的账号 按钮 -->
                    <div data-role="controlgroup" class="ui-btn-right"
                         data-type="horizontal"
                         data-mini="true">
                        <a id="page_header_my_account" href="{{bzf_make_url controller='/My/Account'}}"
                           data-role="button"
                           data-icon="bars">我的账户</a>
                    </div>
                    <!-- /我的账号 按钮 -->
                {{else}}
                    <!-- 登陆注册 按钮 -->
                    <div data-role="controlgroup" class="ui-btn-right"
                         data-type="horizontal"
                         data-mini="true">
                        <a id="page_header_user_register" href="{{bzf_make_url controller='/User/Register'}}"
                           data-transition="flip"
                           data-role="button" data-icon="plus">注册</a>
                        <a id="page_header_user_login" href="{{bzf_make_url controller='/User/Login'}}"
                           data-transition="flip"
                           data-role="button" data-icon="check">登陆</a>
                    </div>
                    <!-- /登陆注册 按钮 -->
                {{/if}}
            {{/nocache}}

        </div>
        <!-- /Page Header -->

        <!-- =============================  page content 内容  =========================================== -->

        <!-- Page 主体内容 -->
        <div data-role="content">
            {{block name=main_page_content}}{{/block}}
        </div>
        <!-- /Page 主体内容 -->

        <!-- =============================  /page content 内容 ========================================== -->

        <!-- 输出调试信息 -->
        {{bzf_debug_log_web}}
        <!-- /输出调试信息 -->

        <div data-role="footer" data-theme="f">
            <h1>©Copyright 棒主妇商城</h1>
        </div>

    </div>
    <!-- /主 Page -->

{{/block}}
