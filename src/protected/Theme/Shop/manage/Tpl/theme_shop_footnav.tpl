{{extends file='theme_shop_layout.tpl'}}
{{block name=theme_shop_main_body}}
    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#theme_shop_tabbar li:has(a[href='{{bzf_make_url controller='/Theme/Shop/FootNav'}}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 1, text: '底部导航', link: window.location.href});
    </script>
    <div class="row">

    <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
          style="margin: 0px 0px 0px 0px;">

    <table class="table table-bordered table-hover table-condensed">
    <thead>
    <tr>
        <th width="33%">底部分类</th>
        <th width="33%">显示文字</th>
        <th>URL或者文章ID</th>
    </tr>
    </thead>
    <tbody>

    <tr class="well">
        <td colspan="3">底部导航</td>
    </tr>

    <!-- 一列底部导航 -->
    <tr>
        <td>
            <input name="footNav[0][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[0]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[0][item][0][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[0]['item'][0]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[0][item][0][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[0]['item'][0]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[0][item][1][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[0]['item'][1]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[0][item][1][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[0]['item'][1]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[0][item][2][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[0]['item'][2]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[0][item][2][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[0]['item'][2]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[0][item][3][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[0]['item'][3]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[0][item][3][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[0]['item'][3]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[0][item][4][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[0]['item'][4]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[0][item][4][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[0]['item'][4]['url']|default}}"/>
        </td>
    </tr>
    <!-- /一列底部导航 -->

    <!-- 一列底部导航 -->
    <tr>
        <td>
            <input name="footNav[1][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[1]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[1][item][0][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[1]['item'][0]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[1][item][0][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[1]['item'][0]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[1][item][1][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[1]['item'][1]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[1][item][1][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[1]['item'][1]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[1][item][2][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[1]['item'][2]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[1][item][2][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[1]['item'][2]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[1][item][3][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[1]['item'][3]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[1][item][3][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[1]['item'][3]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[1][item][4][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[1]['item'][4]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[1][item][4][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[1]['item'][4]['url']|default}}"/>
        </td>
    </tr>
    <!-- /一列底部导航 -->

    <!-- 一列底部导航 -->
    <tr>
        <td>
            <input name="footNav[2][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[2]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[2][item][0][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[2]['item'][0]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[2][item][0][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[2]['item'][0]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[2][item][1][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[2]['item'][1]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[2][item][1][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[2]['item'][1]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[2][item][2][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[2]['item'][2]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[2][item][2][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[2]['item'][2]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[2][item][3][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[2]['item'][3]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[2][item][3][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[2]['item'][3]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[2][item][4][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[2]['item'][4]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[2][item][4][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[2]['item'][4]['url']|default}}"/>
        </td>
    </tr>
    <!-- /一列底部导航 -->

    <!-- 一列底部导航 -->
    <tr>
        <td>
            <input name="footNav[3][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[3]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[3][item][0][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[3]['item'][0]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[3][item][0][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[3]['item'][0]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[3][item][1][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[3]['item'][1]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[3][item][1][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[3]['item'][1]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[3][item][2][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[3]['item'][2]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[3][item][2][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[3]['item'][2]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[3][item][3][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[3]['item'][3]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[3][item][3][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[3]['item'][3]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[3][item][4][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[3]['item'][4]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[3][item][4][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[3]['item'][4]['url']|default}}"/>
        </td>
    </tr>
    <!-- /一列底部导航 -->

    <!-- 一列底部导航 -->
    <tr>
        <td>
            <input name="footNav[4][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[4]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[4][item][0][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[4]['item'][0]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[4][item][0][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[4]['item'][0]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[4][item][1][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[4]['item'][1]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[4][item][1][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[4]['item'][1]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[4][item][2][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[4]['item'][2]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[4][item][2][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[4]['item'][2]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[4][item][3][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[4]['item'][3]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[4][item][3][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[4]['item'][3]['url']|default}}"/>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="footNav[4][item][4][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[4]['item'][4]['title']|default}}"/>
        </td>
        <td>
            <input name="footNav[4][item][4][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$footNav[4]['item'][4]['url']|default}}"/>
        </td>
    </tr>
    <!-- /一列底部导航 -->

    <tr class="well">
        <td colspan="3">友情链接</td>
    </tr>

    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="friendLink[0][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$friendLink[0]['title']|default}}"/>
        </td>
        <td>
            <input name="friendLink[0][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$friendLink[0]['url']|default}}"/>
        </td>
    </tr>

    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="friendLink[1][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$friendLink[1]['title']|default}}"/>
        </td>
        <td>
            <input name="friendLink[1][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$friendLink[1]['url']|default}}"/>
        </td>
    </tr>

    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="friendLink[2][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$friendLink[2]['title']|default}}"/>
        </td>
        <td>
            <input name="friendLink[2][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$friendLink[2]['url']|default}}"/>
        </td>
    </tr>

    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="friendLink[3][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$friendLink[3]['title']|default}}"/>
        </td>
        <td>
            <input name="friendLink[3][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$friendLink[3]['url']|default}}"/>
        </td>
    </tr>

    <tr>
        <td>&nbsp;</td>
        <td>
            <input name="friendLink[4][title]" class="span2" type="text" data-no-validation="true"
                   value="{{$friendLink[4]['title']|default}}"/>
        </td>
        <td>
            <input name="friendLink[4][url]" class="span2" type="text" data-no-validation="true"
                   value="{{$friendLink[4]['url']|default}}"/>
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
