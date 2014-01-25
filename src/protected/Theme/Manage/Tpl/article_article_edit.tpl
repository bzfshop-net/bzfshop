{{extends file='article_article_layout.tpl'}}
{{block name=article_edit_main_body}}

    <!-- 用 JS 设置文章编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#article_edit_tab_left li:has(a[href='{{bzf_make_url controller='/Article/Article/Edit' article_id=$article_id }}'])").addClass("active");
        });

        window.bz_set_breadcrumb_status.push({index: 2, text: '编辑文章', link: window.location.href});
    </script>
    <form class="form-horizontal form-horizontal-inline" method="POST"
          style="margin: 0px 0px 0px 0px;">

        <!-- 左侧每个标签的具体内容 -->
        <div class="tab-content">

            <!-- 文章的基本信息 -->
            <div id="article_edit_basic_info" class="tab-pane well active">

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">创建者</span>
                        <input class="span1" value="{{$article['admin_user_name']|default}}"
                               type="text" disabled="disabled"/>
                        <span class="input-label">创建时间</span>
                        <input class="span2" value="{{$article['add_time']|bzf_localtime}}"
                               type="text" disabled="disabled"/>
                        <span class="input-label">最后更新</span>
                        <input class="span1" value="{{$article['update_user_name']|default}}"
                               type="text" disabled="disabled"/>
                        <span class="input-label">更新时间</span>
                        <input class="span2" value="{{$article['update_time']|bzf_localtime}}"
                               type="text" disabled="disabled"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">文章标题</span>
                        <input class="span9" name="article[title]" value="{{$article['title']|default}}"
                               type="text" data-validation-required-message="文章标题不能为空"/>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">SEO关键词</span>
                        <input class="span7" type="text" name="article[seo_keyword]"
                               value="{{$article['seo_keyword']}}"/>
                        <span class="comments">用英文逗号分开，不要用中文逗号</span>
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

{{block name=page_js_block append}}
    <script type="text/javascript">
        /**
         * 这里的代码等 document.ready 才执行
         */
        jQuery((function (window, $) {

            /******************* article_article_edit.tpl 网站文章内容编辑，商品品牌页面编辑 ******************/
            KindEditor.create('#article_article_edit_content_textarea', {
                filterMode: true,
                themeType: 'default',
                cssData: "body {font-family: '微软雅黑', 'Microsoft Yahei', '宋体', 'songti', STHeiti, Helmet, Freesans, sans-serif;font-size: 15px; }",
                uploadJson: bZF.makeUrl('/File/KindEditor?action=upload&dirname=image_article'), // '/File/Upload'
                fileManagerJson: bZF.makeUrl('/File/KindEditor?action=manage&dirname=image_article'),
                extraFileUploadParams: {
                    bzfshop_auth_cookie_key: $.cookie(WEB_COOKIE_AUTH_KEY)
                },
                formatUploadUrl: false,
                allowFileManager: true,
                width: $('#article_article_edit_content_textarea').outerWidth(false)
            });

        })(window, jQuery));
    </script>
{{/block}}