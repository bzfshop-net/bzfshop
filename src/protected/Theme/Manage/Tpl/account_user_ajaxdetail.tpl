<!-- 订单详情对话框 -->
<!-- div id="order_detail_dialog" class="modal hide fade" -->
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
        &times;
    </button>
    <h3>用户详情</h3>
</div>
<div class="modal-body">

    {{if !empty($errorMessage)}}
        <!-- 错误警告 -->
        <div id="goods_statistics_error" class="row" style="text-align: center;font-weight: bold;">
            <label class="label label-important">错误：</label>{{$errorMessage}}
        </div>
        <!-- /错误警告 -->
    {{/if}}

    {{if isset($userInfo) }}

        <!-- 订单详情 -->
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
                    <td class="labelkey">用户ID</td>
                    <td class="labelvalue">{{$userInfo['user_id']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">用户名</td>
                    <td class="labelvalue">{{$userInfo['user_name']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">Email</td>
                    <td class="labelvalue">{{$userInfo['email']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">第三方登录</td>
                    <td class="labelvalue">{{$userInfo['sns_login']}}</td>
                </tr>
                <tr>
                    <td class="labelkey">注册时间</td>
                    <td class="labelvalue">{{$userInfo['reg_time']|bzf_localtime}}</td>
                </tr>
                <tr>
                    <td class="labelkey">注册IP</td>
                    <td class="labelvalue">
                        {{$userInfo['reg_ip']}}&nbsp;&nbsp;
                        <a href="{{bzf_ip_query_url ip=$userInfo['reg_ip']}}" class="btn btn-small"
                           target="_blank">
                            查询
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="labelkey">上次登录时间</td>
                    <td class="labelvalue">{{$userInfo['last_login']|bzf_localtime}}</td>
                </tr>
                <tr>
                    <td class="labelkey">上次登录IP</td>
                    <td class="labelvalue">
                        {{$userInfo['last_ip']}}&nbsp;&nbsp;
                        <a href="{{bzf_ip_query_url ip=$userInfo['last_ip']}}" class="btn btn-small"
                           target="_blank">
                            查询
                        </a>
                    </td>
                </tr>
                <!-- 分隔条 -->
                <tr class="well">
                    <td colspan="2">&nbsp;</td>
                </tr>
                <!-- /分隔条 -->
                <tr>
                    <td class="labelkey">余额</td>
                    <td class="labelvalue">
                        {{$userInfo['user_money']|bzf_money_display}}&nbsp;&nbsp;&nbsp;&nbsp;
                        <a class="btn btn-small"
                           href="{{bzf_make_url controller='/Account/User/Money' user_id=$userInfo['user_id']}}">资金明细</a>
                    </td>
                </tr>
                <tr>
                    <td class="labelkey">余额充值</td>
                    <td class="labelvalue">
                        <form action="{{bzf_make_url controller='/Account/User/Charge'}}" method="POST" style="margin: 0px;">
                            <div class="control-group">
                                <div class="controls">
                                    <input type="hidden" name="user_id" value="{{$userInfo['user_id']}}"/>
                                    <input type="text" class="span1" name="chargeMoney" value="0"
                                           pattern="^(-)?\d+(\.\d+)?$"
                                           data-validation-pattern-message="充值金额无效"
                                           rel="tooltip" data-placement="top"
                                           data-title="充值金额可以为负数，用于减去用户的余额"/>
                                    <button type="submit" class="btn btn-small btn-danger">确认充值</button>
                                    <br/><br/>
                                    <textarea name="chargeMoneyDesc" rel="tooltip" data-placement="top"
                                              data-title="充值说明"></textarea>
                                </div>
                            </div>
                        </form>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <!-- /订单详情 -->

    {{/if}}

</div>
<div class="modal-footer">
    <a href="#" class="btn btn-success" data-dismiss="modal">关闭</a>
</div>

<!-- /div --><!-- 订单详情对话框 -->
