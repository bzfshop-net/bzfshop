{{if isset($goodsCommentArray)}}
    <!-- 商品的用户评价列表 -->
    {{foreach $goodsCommentArray as $goodsCommentItem}}
        <div class="media">
            <a class="pull-left" href="#" onclick="return false;">
                <img class="media-object lazyload"
                     src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                     data-original="{{bzf_get_asset_url asset='img/user_avatar.gif'}}">
            </a>

            <div class="media-body bzf_border">

                <table class="table table-condensed">
                    <tbody>
                    <tr>
                        <td>
                            <div class="bzf_rate_star_readonly"
                                 rateValue="{{$goodsCommentItem['comment_rate']}}"></div>
                        </td>
                        <td>{{$goodsCommentItem['user_name']|bzf_mask_string}}</td>
                        <td>
                            {{if $goodsCommentItem['comment_time'] > 0}}
                                {{$goodsCommentItem['comment_time']|bzf_localtime}}
                            {{else}}
                                {{$goodsCommentItem['create_time']|bzf_localtime}}
                            {{/if}}
                        </td>
                    </tr>
                    <tr>
                        <td>商品选择：{{$goodsCommentItem['goods_attr']|default:'默认款式'}}</td>
                        <td>商品金额：￥{{$goodsCommentItem['goods_price']|bzf_money_display}}</td>
                        <td>购买数量：{{$goodsCommentItem['goods_number']}}</td>
                    </tr>
                    </tbody>
                </table>
                {{if empty($goodsCommentItem['comment'])}}
                    [用户没有评价，系统默认为好评]
                {{else}}
                    {{$goodsCommentItem['comment']}}
                {{/if}}

                {{if !empty($goodsCommentItem['reply'])}}
                    <div class="media">
                        <a class="pull-left" href="#" onclick="return false;">
                            <img class="media-object lazyload"
                                 src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                 data-original="{{bzf_get_asset_url asset='img/admin_avatar.gif'}}">
                        </a>

                        <div class="media-body">
                            <h6 class="media-heading">管理员</h6>
                            {{$goodsCommentItem['reply']}}
                        </div>
                    </div>
                {{/if}}
            </div>
        </div>
    {{/foreach}}
    <!-- /商品的用户评价列表 -->

    <!-- 分页 -->
    <div class="row pagination pagination-right">
        {{bzf_paginator currentUrl=$currentUrl|default:'' noPageCount='true' count=$totalCount|default:0  pageNo=$pageNo|default:0  pageSize=$pageSize|default:10 }}
    </div>
    <!-- 分页 -->

{{else}}
    <div class="media">
        <a class="pull-left" href="#">
            <img class="media-object lazyload"
                 src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                 data-original="{{bzf_get_asset_url asset='img/user_avatar.gif'}}">
        </a>

        <div class="media-body bzf_border" style="margin-top: 20px;">
            目前还没有用户评价，你来做第一个评价？
        </div>
    </div>
{{/if}}
