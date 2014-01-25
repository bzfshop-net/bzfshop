{{extends file='misc_layout.tpl'}}
{{block name=misc_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#misc_tabbar li:has(a[href='{{bzf_make_url controller='/Misc/Express'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '快递公司', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>快递公司列表</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <a href="{{bzf_make_url controller='/Misc/Express/Edit'}}" class="btn btn-info">添加快递</a>
        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 快递公司列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>ID</th>
                <th>快递公司</th>
                <th>拼音名</th>
                <th>排序</th>
                <th>可用</th>
                <th>备注</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($expressArray)}}
                {{foreach $expressArray as $expressItem}}
                    <!-- 一个快递公司 -->
                    <tr>
                        <td>{{$expressItem['meta_id']}}</td>
                        <td>{{$expressItem['meta_name']}}</td>
                        <td>{{$expressItem['meta_ename']}}</td>
                        <td>{{$expressItem['meta_sort_order']}}</td>
                        <td>
                            {{if $expressItem['meta_status'] > 0}}
                                <i class="icon-ok"></i>
                            {{else}}
                                <i class="icon-remove"></i>
                            {{/if}}
                        </td>
                        <td>{{$expressItem['meta_desc']|nl2br nofilter}}</td>
                        <td>
                            <a href="{{bzf_make_url controller='/Misc/Express/Edit' meta_id=$expressItem['meta_id']}}"
                               class="btn btn-small">编辑</a>
                        </td>
                    </tr>
                    <!-- /一个快递公司 -->
                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /快递公司列表 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}