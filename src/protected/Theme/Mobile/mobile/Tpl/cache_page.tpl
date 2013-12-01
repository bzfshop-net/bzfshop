<!-- 缓存页面，保存全局都会使用的一些组件 -->
<!-- div id="cache_page" data-role="page" class="ui-responsive-panel" -->

<input type="hidden" id="cache_page_exist"/>

<!-- 商品分类选择面板 -->
<div data-role="panel" id="goods_category_panel" data-position="left" data-theme="a">
    {{include file='block_category_list.tpl'}}
</div>
<!-- 商品分类选择面板 -->

<!-- 我的账户面板 -->
<div data-role="panel" id="my_account_panel" data-position="right" data-theme="a">
    {{include file='block_my_account.tpl'}}
</div>
<!-- 我的账户面板 -->

<!-- 用户登录对话框 -->
<div data-role="popup" id="user_login_popup" data-theme="a" class="ui-corner-all">
    <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext"
       class="ui-btn-right">关闭</a>
    {{include file='block_user_login.tpl'}}
</div>
<!-- /用户登录对话框 -->

<!-- 用户注册对话框 -->
<div data-role="popup" id="user_register_popup" data-theme="a" class="ui-corner-all">
    <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext"
       class="ui-btn-right">关闭</a>
    {{include file='block_user_register.tpl'}}
</div>
<!-- /用户注册对话框 -->

<!-- flash message 显示-->
<div data-role="popup" id="flash_message_popup" data-theme="e" data-tolerance="15,15" class="ui-content">
    <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext"
       class="ui-btn-right">关闭</a>
    <ul>
        <li>这里是出错消息</li>
    </ul>
</div>
<!-- /flash message 显示-->

<!-- 图片放大显示 popup -->
<div data-role="popup" id="zoom_image_popup" class="photopopup"
     data-overlay-theme="a" data-corners="false" data-tolerance="30,15">
    <a href="#" data-rel="back" data-role="button" data-theme="a"
       data-icon="delete" data-iconpos="notext" class="ui-btn-right">关闭</a>
    <img class="showloading" width="auto" height="auto"
         src="" data-original="" alt="大图显示"/>
</div>
<!-- 图片放大显示 popup -->

<!-- /div -->
<!-- 缓存页面，保存全局都会使用的一些组件 -->

