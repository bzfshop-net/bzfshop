{{extends file='article_layout.tpl'}}
{{block name=article_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#article_tabbar li:has(a[href='{{bzf_make_url controller='/Article/Category'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '文章分类', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>文章分类</h4>

        <!-- 这里是条件筛选区 -->
        <div class="row well well-small">
            <a href="{{bzf_make_url controller='/Article/Category/Edit'}}" class="btn btn-info">新建</a>
        </div>
        <!-- /这里是条件筛选区 -->

        <!-- 分类列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>分类ID</th>
                <th>分类名</th>
                <th>排序</th>
                <th>备注</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {{if isset($articleCateogryArray)}}
                {{foreach $articleCateogryArray as $articleCateogryItem}}
                    <!-- 一个文章分类 -->
                    <tr>
                        <td>{{$articleCateogryItem['meta_id']}}</td>
                        <td>{{$articleCateogryItem['meta_name']}}</td>
                        <td>{{$articleCateogryItem['meta_sort_order']}}</td>
                        <td>{{$articleCateogryItem['meta_desc']}}</td>
                        <td>
                            <a href="{{bzf_make_url controller='/Article/Category/Edit' meta_id=$articleCateogryItem['meta_id']}}"
                               class="btn btn-small">编辑</a>
                        </td>
                    </tr>
                    <!-- /一个文章分类 -->
                {{/foreach}}
            {{/if}}
            </tbody>
        </table>
        <!-- /分类列表 -->

    </div>
    <!-- /页面主体内容 -->

{{/block}}