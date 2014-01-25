{{extends file='goods_edit_layout.tpl'}}
{{block name=goods_edit_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_edit_tab_left li:has(a[href='{{bzf_make_url controller='/Goods/Edit/Team' goods_id=$goods_id }}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '团购信息', link: window.location.href});
    </script>
    <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
          style="margin: 0px 0px 0px 0px;">

        <!-- 左侧每个标签的具体内容 -->
        <div class="tab-content">

            <!-- 商品的团购设置 -->
            <div id="goods_edit_goods_team" class="tab-pane well active">

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">开启团购</span>
                        <select id="goods_edit_goods_team_enable" class="span2 select2-simple"
                                name="goods_team[team_enable]"
                                data-initValue="{{$goods_team['team_enable']|default}}">
                            <option value="0">关闭团购</option>
                            <option value="1">开启团购</option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label" rel="tooltip" data-placement="top" data-title="团购可以有独立的标题">团购标题</span>
                        <input class="span9" name="goods_team[team_title]" type="text"
                               value="{{$goods_team['team_title']|default}}"
                               data-validation-required-message="团购标题不能为空"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">SEO关键词</span>
                        <input class="span5" name="goods_team[team_seo_keyword]"
                               value="{{$goods_team['team_seo_keyword']|default}}" type="text"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">SEO描述</span>
                        <textarea class="span5" rows="3"
                                  name="goods_team[team_seo_description]">{{$goods_team['team_seo_description']|default}}</textarea>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">团购价</span>
                        <input class="span1" name="goods_team[team_price]" type="text" pattern="^\d+(\.\d+)?$"
                               value="{{$goods_team['team_price']|bzf_money_display}}"
                               data-validation-required-message="团购价不能为空"
                               data-validation-pattern-message="团购价无效"/>
                                <span class="input-label" rel="tooltip" data-placement="top"
                                      data-title="输出团购商品的排序">团购排序</span>
                        <input class="span1" name="goods_team[team_sort_order]" type="text" pattern="[0-9]+"
                               value="{{$goods_team['team_sort_order']|default:0}}"
                               data-validation-required-message="团购排序不能为空"
                               data-validation-pattern-message="团购排序无效"/>
                        <span class="comments">数字越大排序越前</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="每个账号最多可以买多少个">每人限购数量</span>
                        <input class="span1" name="goods_team[team_per_number]" type="text" pattern="[0-9]+"
                               value="{{$goods_team['team_per_number']|default:0}}"
                               data-validation-required-message="每人限购数量不能为空"
                               data-validation-pattern-message="每人限购数量无效"/>

                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="每个账号每次最少可以买多少个">每次限购下限</span>
                        <input class="span1" name="goods_team[team_min_number]" type="text" pattern="[0-9]+"
                               value="{{$goods_team['team_min_number']|default:0}}"
                               data-validation-required-message="每次限购下限不能为空"
                               data-validation-pattern-message="每次限购下限无效"/>

                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="每个账号每次最多可以买多少个">每次限购上限</span>
                        <input class="span1" name="goods_team[team_max_number]" type="text" pattern="[0-9]+"
                               value="{{$goods_team['team_max_number']|default:0}}"
                               data-validation-required-message="每次限购上限不能为空"
                               data-validation-pattern-message="每次限购上限无效"/>

                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="用于虚增购买数字">虚拟购买</span>
                        <input class="span1" name="goods_team[team_pre_number]" type="text" pattern="[0-9]+"
                               value="{{$goods_team['team_pre_number']|default:0}}"
                               data-validation-required-message="虚拟购买不能为空"
                               data-validation-pattern-message="虚拟购买无效"/>

                    <span class="input-label" rel="tooltip" data-placement="top"
                          data-title="虚拟+真实购买的总数">总购买数</span>
                        <input class="span1" type="text" disabled="disabled"
                               value="{{$goods_team['team_now_number']|default}}"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="comments">说明：数字 0 表示不限购</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">

                        <span class="input-label">团购开始时间</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2" type="text" name="goods_team[team_begin_time_str]"
                                   value="{{$goods_team['team_begin_time']|default|bzf_localtime}}"
                                   data-validation-required-message="团购开始时间不能为空"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>

                        <span class="input-label">团购结束时间</span>

                        <div class="input-append date datetimepicker">
                            <input class="span2" type="text" name="goods_team[team_end_time_str]"
                                   value="{{$goods_team['team_end_time']|default|bzf_localtime}}"
                                   data-validation-required-message="团购结束时间不能为空"/>
                        <span class="add-on">
                            <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                        </span>
                        </div>
                    </div>
                </div>


            </div>
            <!-- /商品的团购设置 -->

            <!-- 提交按钮 -->
            <div class="row" style="text-align: center;">
                <button type="submit" class="btn btn-success">确认提交</button>
            </div>
            <!-- /提交按钮 -->

        </div>
        <!-- /左侧每个标签的具体内容 -->

    </form>
{{/block}}