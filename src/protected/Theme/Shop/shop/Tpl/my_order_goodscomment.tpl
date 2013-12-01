<!-- 商品评价对话框 -->
<!-- div id="bzf_my_order_detail_goods_comment" class="modal hide fade" -->

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
        &times;
    </button>
    <h3>商品评价</h3>
</div>

<!-- 更新提交表单 -->
<form class="form-horizontal form-horizontal-inline" method="POST"
      action="{{bzf_make_url controller='/My/Order/GoodsComment' rec_id=$rec_id  static=false}}">

    <div class="modal-body">

        {{if !empty($errorMessage)}}
            <!-- 错误警告 -->
            <div id="goods_statistics_error" class="row" style="text-align: center;font-weight: bold;">
                <label class="label label-important">错误：</label>{{$errorMessage}}
            </div>
            <!-- /错误警告 -->
        {{else}}

            <!-- 商品评价 -->
            <div class="row">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th width="20%">&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="labelkey">商品评分</td>
                        <td class="labelvalue">
                            <input id="bzf_my_order_goodscomment_input" type="hidden" name="comment_rate"
                                   value="{{$goodsComment['comment_rate']}}"/>

                            <div class="bzf_rate_star"
                                 targetInputSelector="#bzf_my_order_goodscomment_input"
                                 rateValue="{{$goodsComment['comment_rate']}}"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="labelkey">商品评价</td>
                        <td class="labelvalue">
                            <textarea name="comment" rows="3" cols="20"
                                      maxlength="64"
                                      class="span4">{{$goodsComment['comment']}}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="labelkey">管理员回复</td>
                        <td class="labelvalue">{{$goodsComment['reply']}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <!-- /商品评价 -->

        {{/if}}

    </div>
    <div class="modal-footer">
        {{if empty($errorMessage) }}
            <button type="submit" class="btn btn-success">提交</button>
            <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">关闭</button>
        {{else}}
            <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">关闭</button>
        {{/if}}
    </div>

</form>
<!-- /更新提交表单 -->

<!-- /div --><!-- /商品评价对话框 -->

