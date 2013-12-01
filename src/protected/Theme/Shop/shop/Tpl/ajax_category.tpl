<!-- 商品分类 -->
<!-- div class="row navsort" -->

<div class='row allsort'>

    <div class='mc'>

        {{if isset($goodsCategoryTreeArray)}}

            {{foreach $goodsCategoryTreeArray as $goodsCategoryFirstItem}}
                <!-- 一级分类 -->
                <div class='item'>
                        <span><h3>
                                <a href="{{bzf_make_url controller="/Goods/Category" category_id=$goodsCategoryFirstItem['meta_id']}}">{{$goodsCategoryFirstItem['meta_name']}}</a>
                            </h3><s></s></span>

                    <div class='i-mc'>
                        <div class='close'
                             onclick="$(this).parent().parent().removeClass('hover')"></div>
                        <div class='subitem'>

                            {{if isset($goodsCategoryFirstItem['child_list'])}}
                                {{foreach $goodsCategoryFirstItem['child_list'] as $goodsCategorySecondItem}}
                                    <!-- 二级分类 -->
                                    <dl>
                                        <dt>
                                            <a href="{{bzf_make_url controller="/Goods/Category" category_id=$goodsCategorySecondItem['meta_id']}}">{{$goodsCategorySecondItem['meta_name']}}</a>
                                        </dt>
                                        <dd>

                                            {{if isset($goodsCategorySecondItem['child_list'])}}
                                                {{foreach $goodsCategorySecondItem['child_list'] as $goodsCategoryThirdItem}}
                                                    <!-- 三级分类 -->
                                                    <em><a href='{{bzf_make_url controller="/Goods/Category" category_id=$goodsCategoryThirdItem['meta_id']}}'>{{$goodsCategoryThirdItem['meta_name']}}</a></em>
                                                    <!-- /三级分类 -->
                                                {{/foreach}}
                                            {{/if}}
                                        </dd>
                                    </dl>
                                    <!-- /二级分类 -->
                                {{/foreach}}
                            {{/if}}

                        </div>

                        <!-- image -->
                        <div class='fr' style="padding-top: 30px;">
                            <a href="#">
                                <img src="{{bzf_get_asset_url asset='img/category_adv.jpg'}}"/>
                            </a>
                        </div>
                        <!-- /image -->

                    </div>
                </div>
                <!-- /一级分类 -->
            {{/foreach}}

        {{/if}}

        <div class='extra'><a href='{{bzf_make_url controller="/"}}'>全部商品分类</a></div>

    </div>

</div>

<!-- /div -->
<!-- /商品分类 -->
