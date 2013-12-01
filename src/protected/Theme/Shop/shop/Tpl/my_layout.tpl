{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 我的...页面 -->
    <div class="row" style="background-color: white;padding:10px 10px;">

        <!-- 页面上方导航条 -->
        <div class="row">
            <ul id="my_nav_tabbar" class="nav nav-tabs">

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">个人信息<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{bzf_make_url controller='/My/Profile'}}">我的资料</a>
                        </li>
                        <li>
                            <a href="{{bzf_make_url controller='/My/Address'}}">我的地址</a>
                        </li>
                        <li>
                            <a href="{{bzf_make_url controller='/My/Money'}}">我的资金</a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">我的订单<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{bzf_make_url controller='/My/Order'}}">全部订单</a>
                        </li>
                        <li>
                            <a href="{{bzf_make_url controller='/My/Order' orderStatus=1}}">已付款订单</a>
                        </li>
                        <li>
                            <a href="{{bzf_make_url controller='/My/Order' orderStatus=0}}">未付款订单</a>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
        <!-- /页面上方导航条 -->

        <!--  My 主体内容  -->

        {{block name=main_body_my}}{{/block}}

        <!-- /My 主体内容  -->

    </div>
    <!-- /我的...页面 -->

{{/block}}