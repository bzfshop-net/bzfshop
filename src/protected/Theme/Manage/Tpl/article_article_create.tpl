{{extends file='article_layout.tpl'}}
{{block name=article_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#article_tabbar li:has(a[href='{{bzf_make_url controller='/Article/Article/Search'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '新建文章', link: window.location.href});
    </script>
    <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
          action="Edit" style="margin: 0px 0px 0px 0px;">

        <!-- 左侧每个标签的具体内容 -->
        <div class="tab-content">

            <!-- 文章的基本信息 -->
            <div id="article_edit_basic_info" class="tab-pane well active">

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">文章标题</span>
                        <input class="span9" name="article[title]" value="{{$article['title']|default}}"
                               type="text" data-validation-required-message="文章标题不能为空"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">文章Tag</span>
                        <input class="span9 select2-simple" name="article[tag_list]"
                               value="{{$article['tag_list']|default}}" type="text"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">文章分类</span>
                        <select class="span2 select2-simple" name="article[cat_id]" data-placeholder="文章分类选择"
                                data-ajaxCallUrl="{{bzf_make_url controller="/Ajax/Article/ListCategory"}}"
                                data-option-value-key="meta_id" data-option-text-key="meta_name"
                                data-initValue="{{$article['cat_id']|default}}">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">是否显示</span>
                        <select class="span1 select2-simple" name="article[is_open]"
                                data-initValue="{{$article['is_open']|default:'0'}}">
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                        <span class="comments">(设置为不显示，则前台无法查看这篇文章)</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">文章描述</span>
                        <textarea class="span5" rows="3" cols="40"
                                  name="article[description]">{{$article['description']}}</textarea>
                    </div>
                </div>

                <div class="control-group" style="margin-top: 15px;">
                    <div class="controls">
                        <span class="input-label">文章内容</span>
                        <textarea id="article_article_edit_content_textarea" class="span9" style="height:600px;"
                                  rows="5" cols="20" data-no-validation="data-no-validation"
                                  name="article[content]">{{$article['content'] nofilter}}</textarea>
                    </div>
                </div>

            </div>
            <!-- /文章的基本信息 -->

        </div>
        <!-- /左侧每个标签的具体内容 -->


        <!-- 提交按钮 -->
        <div class="row" style="text-align: center;">
            <button type="submit" class="btn btn-success">保存修改</button>
        </div>
        <!-- /提交按钮 -->

    </form>
{{/block}}
