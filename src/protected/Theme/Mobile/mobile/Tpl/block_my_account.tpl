<ul data-role="listview" data-theme="a">
    <li id="my_account_user_name_display" class="ui-btn-icon-left" data-theme="e" data-icon="check">
        <a href="#">用户账号</a>
    </li>
    <li class="ui-btn-icon-left" data-theme="e" data-icon="delete" data-ajax="false">
        <a data-direction="reverse"
           data-transition="flow" href="{{bzf_make_url controller='/User/Logout'}}">退出登录</a>
    </li>
    <li class="ui-btn-icon-left" data-icon="arrow-l">
        <a data-direction="reverse" data-transition="flow"
           href="{{bzf_make_url controller='/My/Order'}}">我的订单</a>
    </li>
    <li class="ui-btn-icon-left" data-icon="arrow-l">
        <a data-direction="reverse" data-transition="flow"
           href="{{bzf_make_url controller='/Cart/Show'}}">查看购物车</a>
    </li>
</ul>
