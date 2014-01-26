{{extends file='theme_shop_layout.tpl'}}
{{block name=theme_shop_main_body}}
    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#theme_shop_tabbar li:has(a[href='{{bzf_make_url controller='/Theme/Shop/HeadNav'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 1, text: '头部导航', link: window.location.href});
    </script>
    <div class="row">

        <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
              style="margin: 0px 0px 0px 0px;">

            <table class="table table-bordered table-hover table-condensed">
                <thead>
                <tr>
                    <th width="30%">显示文字</th>
                    <th>URL</th>
                </tr>
                </thead>
                <tbody>

                <tr>
                    <td>
                        <input name="headNav[0][title]" class="span2" type="text" data-no-validation="true"
                               value="{{$headNav[0]['title']|default}}"/>
                    </td>
                    <td>
                        <input name="headNav[0][url]" class="span6" type="text" data-no-validation="true"
                               value="{{$headNav[0]['url']|default}}"/>
                    </td>
                </tr>

                <tr>
                    <td>
                        <input name="headNav[1][title]" class="span2" type="text" data-no-validation="true"
                               value="{{$headNav[1]['title']|default}}"/>
                    </td>
                    <td>
                        <input name="headNav[1][url]" class="span6" type="text" data-no-validation="true"
                               value="{{$headNav[1]['url']|default}}"/>
                    </td>
                </tr>

                <tr>
                    <td>
                        <input name="headNav[2][title]" class="span2" type="text" data-no-validation="true"
                               value="{{$headNav[2]['title']|default}}"/>
                    </td>
                    <td>
                        <input name="headNav[2][url]" class="span6" type="text" data-no-validation="true"
                               value="{{$headNav[2]['url']|default}}"/>
                    </td>
                </tr>

                <tr>
                    <td>
                        <input name="headNav[3][title]" class="span2" type="text" data-no-validation="true"
                               value="{{$headNav[3]['title']|default}}"/>
                    </td>
                    <td>
                        <input name="headNav[3][url]" class="span6" type="text" data-no-validation="true"
                               value="{{$headNav[3]['url']|default}}"/>
                    </td>
                </tr>

                <tr>
                    <td>
                        <input name="headNav[4][title]" class="span2" type="text" data-no-validation="true"
                               value="{{$headNav[4]['title']|default}}"/>
                    </td>
                    <td>
                        <input name="headNav[4][url]" class="span6" type="text" data-no-validation="true"
                               value="{{$headNav[4]['url']|default}}"/>
                    </td>
                </tr>

                <tr>
                    <td>
                        <input name="headNav[5][title]" class="span2" type="text" data-no-validation="true"
                               value="{{$headNav[5]['title']|default}}"/>
                    </td>
                    <td>
                        <input name="headNav[5][url]" class="span6" type="text" data-no-validation="true"
                               value="{{$headNav[5]['url']|default}}"/>
                    </td>
                </tr>

                </tbody>
            </table>

            <div class="row" style="text-align: center;">
                <button type="submit" class="btn btn-success">确认提交</button>
            </div>

        </form>
    </div>
{{/block}}
