{{extends file='layout.tpl'}}
{{block name=main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#system_top_navbar li:has(a[href='{{bzf_make_url controller='/Goods/Index'}}'])").addClass("active");
        });
    </script>
    <div class="row bz_basic_content_block bz_box_shadow" style="padding:10px 10px 10px 10px;">

        <!-- 页面上方导航条 -->
        <div class="row">
            <ul id="goods_tabbar" class="nav nav-tabs">
                <li>
                    <a href="{{bzf_make_url controller='/Goods/Search'}}">商品列表</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Goods/Create'}}">新建商品</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Goods/Category'}}">商品分类</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Goods/Brand/ListBrand'}}">商品品牌</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Goods/Type/ListType'}}">商品类型</a>
                </li>
                <li>
                    <a href="{{bzf_make_url controller='/Goods/Comment/ListComment'}}">用户评价</a>
                </li>
            </ul>
        </div>
        <!-- /页面上方导航条 -->

        <!-- 商品管理主体内容 -->
        {{block name=goods_main_body}}{{/block}}
        <!-- /商品管理主体内容 -->

    </div>
{{/block}}