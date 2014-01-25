{{extends file='goods_edit_layout.tpl'}}
{{block name=goods_edit_main_body}}

    <!-- 用 JS 设置商品编辑页面左侧不同的 Tab 选中状态 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#goods_edit_tab_left li:has(a[href='{{bzf_make_url controller='/Goods/Edit/Promote' goods_id=$goods_id }}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 2, text: '推广渠道', link: window.location.href});
    </script>
    <form class="form-horizontal form-horizontal-inline form-dirty-check" method="POST"
          style="margin: 0px 0px 0px 0px;">

        <!-- 左侧每个标签的具体内容 -->
        <div class="tab-content">

            <!-- 商品的推广设置 -->
            <div id="goods_edit_goods_promote" class="tab-pane well active">

                <!-- 分割条 -->
                <div class="row inline-divider">
                    <div class="divider"></div>
                    <label class="label label-info">360团购导航</label>
                </div>
                <!-- /分割条 -->

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品头图</span>
                        <!-- 一张图片 -->
                        <div class="thumbnail gallery-item" style="float:left;width:300px;">
                            <!-- 图片 -->
                            <div class="image-container">
                                <img id="goods_edit_promote_upload_360tuan_image"
                                     class="lazyload" width="300" height="180" style="width:300px;height:180px;"
                                     src="{{bzf_get_asset_url asset='img/blank.gif'}}"
                                     data-original="{{$goods_promote['360tuan_image']|default}}"/>
                            </div>
                        </div>
                        <!-- /一张图片 -->
                        <input id="goods_edit_promote_upload_360tuan_image_input"
                               name="goods_promote[360tuan_image]" type="hidden"
                               value="{{$goods_promote['360tuan_image']|default}}"/>
                        &nbsp;&nbsp;
                        <button id="goods_edit_promote_upload_360tuan_image_button" type="button"
                                class="btn btn-small btn-success">上传图片
                        </button>
                        <span class="comments">图片尺寸为 300x180</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="360团购导航API中的一级分类">一级分类</span>
                        <select id="goods_edit_360tuan_category_1" class="span2 select2-simple"
                                name="goods_promote[360tuan_category]"
                                data-initValue="{{$goods_promote['360tuan_category']|default:'网上购物'}}">
                        </select>

            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="360团购导航API中的子分类，尽量选择到最后一级分类">细分分类</span>
                        <select id="goods_edit_360tuan_category_2" class="span6 select2-simple"
                                name="goods_promote[360tuan_category_end]"
                                data-initValue="{{$goods_promote['360tuan_category_end']|default}}">
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">商品feature</span>
                        <input class="span6" name="goods_promote[360tuan_feature]" type="text"
                               value="{{$goods_promote['360tuan_feature']|default}}"/>
                        <span class="comments">关键词之间用 1 个空格分隔，空格不要多了</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
            <span class="input-label" rel="tooltip" data-placement="top"
                  data-title="输出商品的排序，数字越大排序越前">商品排序</span>
                        <input class="span1" name="goods_promote[360tuan_sort_order]" type="text" pattern="[0-9]+"
                               value="{{$goods_promote['360tuan_sort_order']|default}}"
                               data-validation-pattern-message="商品排序无效"/>
                        <span class="comments">数字越大排序越前</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <span class="input-label">Pin图列表</span>
                        <textarea class="span6" rows="5"
                                  name="goods_promote[360tuan_pin_images]">{{$goods_promote['360tuan_pin_images']|default}}</textarea>
                        <span class="comments">每行一个图片URL地址，请使用绝对地址</span>
                    </div>
                </div>

            </div>
            <!-- /商品的推广设置 -->

        </div>
        <!-- /左侧每个标签的具体内容 -->


        <!-- 提交按钮 -->
        <div class="row" style="text-align: center;">
            <button type="submit" class="btn btn-success">确认提交</button>
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

        /*********** goods_edit_promote.tpl  商品推广渠道编辑页面，360团购导航商品图片 ***********/
        bZF.uploadGoodsPromoteImage('#goods_edit_promote_upload_360tuan_image_button',
                function (clickObject, url, title, width, height, border, align) {
                    $('#goods_edit_promote_upload_360tuan_image').attr('src', url);
                    $('#goods_edit_promote_upload_360tuan_image_input').val(url);
                });

        /************* goods_edit_promote.tpl 商品编辑推广渠道页面，用户选择 360团购 的分类 ********************/
        $('#goods_edit_360tuan_category_1').each(function () {
            var _360tuanCategoryJson = '';

            // Ajax 调用取得 360 的分类数据
            var callUrl = bZF.makeUrl('/Ajax/Proxy/Json?cache=3600&url=' + encodeURI('http://api.tuan.360.cn/open_category.php?format=json'));
            bZF.ajaxCallGet(callUrl, function (data) {
                if (!data) {
                    bZF.showMessage('无法取得360团购商品分类');
                    return;
                }

                _360tuanCategoryJson = data;
                // 设置 360tuan_cateogry_1 的数据
                var category1Html = '';
                $.each(_360tuanCategoryJson, function (index, elem) {
                    category1Html += '<option value="' + elem.name + '">' + elem.name + '</option>';
                });
                $('#goods_edit_360tuan_category_1').html(category1Html);

                //处理初始值
                $('#goods_edit_360tuan_category_1').select2('val', $('#goods_edit_360tuan_category_1').attr('data-initValue'));
                //设置 Category 2
                goods_edit_360tuan_update_category_2($('#goods_edit_360tuan_category_1').find('option:selected').val());
                //设置 Category 2 的初始值
                $('#goods_edit_360tuan_category_2').select2('val', $('#goods_edit_360tuan_category_2').attr('data-initValue'));
            });

            function goods_edit_360tuan_update_category_2(category1) {

                function getCategoryHtml(optionsArray, prefix, elem) {
                    $.each(elem, function (elemIndex, elemItem) {
                        var optionItem = {};
                        optionItem.value = elemItem.name;
                        optionItem.text = prefix + elemItem.name;
                        optionsArray.push(optionItem);
                        if (elemItem.sons) {
                            getCategoryHtml(optionsArray, prefix + "---------->", elemItem.sons);
                        }
                    })
                };

                $.each(_360tuanCategoryJson, function (index, elem) {
                    if (elem.name != category1) {
                        return;
                    }
                    // 显示下面的分类数据
                    var optionsArray = [];
                    var category2Html = '';

                    getCategoryHtml(optionsArray, '', elem.sons);

                    $.each(optionsArray, function (optionIndex, optionItem) {
                        category2Html += '<option value="' + optionItem.value + '">' + optionItem.text + '</option>';
                    });
                    $('#goods_edit_360tuan_category_2').html(category2Html);
                });
            };

            // 消息处理
            $('#goods_edit_360tuan_category_1').change(function () {
                goods_edit_360tuan_update_category_2($('#goods_edit_360tuan_category_1').find('option:selected').val());
                $('#goods_edit_360tuan_category_2').select2('val', null);
            });

        });


        /************* goods_edit_promote.tpl 商品编辑推广渠道页面，用户选择 QQ彩贝分类列表 **************/
        (function ($) {

            function renderQqCaiBeiSubTagOptions(tag, subTag) {
                var subTagArray = new Array();
                subTagArray[0] = new Array('请选择二级分类');
                subTagArray[1] = new Array('请选择二级分类', '火锅烧烤', '地方菜', '日韩料理', '西餐', '蛋糕甜品', '其他');
                subTagArray[2] = new Array('请选择二级分类', '美容美发', '电影票务', 'KTV', '其他');
                subTagArray[3] = new Array('请选择二级分类', '旅游', '酒店');
                subTagArray[4] = new Array('请选择二级分类', '美容护肤', '绚丽彩妆', '美容工具');
                subTagArray[5] = new Array('请选择二级分类', '服饰', '鞋包', '数码家电', '食品百货', '家居母婴', '其他');

                var destArray = subTagArray[tag];

                var htmlString = '';
                for (var key in destArray) {
                    if (subTag == key) {
                        htmlString += '<option value="' + key + '" selected>' + destArray[key] + '</option>';
                    } else {
                        htmlString += '<option value="' + key + '">' + destArray[key] + '</option>';
                    }
                }
                $("#goods_edit_qqcaibei_subtag").html(htmlString);
                //重置选择
                $('#goods_edit_qqcaibei_subtag').select2('val', null);
            }

            $("#goods_edit_qqcaibei_tag").change(
                    function () {
                        var tag = $("#goods_edit_qqcaibei_tag").find('option:selected').val();
                        renderQqCaiBeiSubTagOptions(tag, 0);
                    }
            );

            // 页面刚加载执行一次
            if ($("#goods_edit_qqcaibei_tag").size() > 0) {
                //设置初始值
                $("#goods_edit_qqcaibei_tag").select2('val', $("#goods_edit_qqcaibei_tag").attr('data-initValue'));
                renderQqCaiBeiSubTagOptions($("#goods_edit_qqcaibei_tag").find('option:selected').val(), $("#goods_edit_qqcaibei_subtag").attr('data-initValue'));
                $("#goods_edit_qqcaibei_subtag").select2('val', $("#goods_edit_qqcaibei_subtag").attr('data-initValue'));
            }

        })(jQuery);


        /************* goods_edit_promote.tpl 商品编辑推广渠道页面，用户选择 搜狗团购导航分类 **************/
        (function ($) {

            function renderSogouCategory2(category1, category2) {
                var subTagArray = new Array();
                subTagArray[0] = new Array('请选择二级分类');
                subTagArray[1] = new Array('自助餐', '火锅', '香锅烤鱼', '双人套餐', '烧烤', '蛋糕', '地方菜', '海鲜', '日韩料理', '西餐', '甜点饮品', '快餐休闲', '其他');
                subTagArray[2] = new Array('电影票', 'KTV', '运动健身', '游乐电玩', '赛事演出', '景点郊游', '温泉洗浴', '其他');
                subTagArray[3] = new Array('摄影写真', '美发', '美容美体', '足疗按摩', '美甲', '体检', '口腔', '教育培训', '汽车养护', '其他');
                subTagArray[4] = new Array('服装', '配饰', '箱包', '鞋靴', '化妆品', '手表', '运动户外', '手机数码', '家用电器', '生活家居', '汽车配件', '图书音像', '食品饮料', '母婴玩具', '0元抽奖', '其他');
                subTagArray[5] = new Array('酒店住宿', '国内游', '周边游', '出境游', '港澳游', '景点门票', '其他');

                var destArray = subTagArray[category1];

                var htmlString = '';
                for (var key in destArray) {
                    if (category2 == destArray[key]) {
                        htmlString += '<option value="' + destArray[key] + '" selected>' + destArray[key] + '</option>';
                    } else {
                        htmlString += '<option value="' + destArray[key] + '">' + destArray[key] + '</option>';
                    }
                }
                $("#goods_edit_sogoutuan_category_2").html(htmlString);
                $("#goods_edit_sogoutuan_category_2").select2('val', null);
            }

            $("#goods_edit_sogoutuan_category_1").change(
                    function () {
                        var category1 = $("#goods_edit_sogoutuan_category_1").find('option:selected').val();
                        renderSogouCategory2(category1, null);

                        var category2 = $("#goods_edit_sogoutuan_category_2").find('option:selected').val();
                        renderSogouCategory3(category2, null);
                    }
            );

            function renderSogouCategory3(category2, category3) {
                var category2Array = new Array();
                var category3Array = new Array();

                category2Array[0] = '摄影写真';
                category3Array[0] = new Array('婚纱摄影', '艺术写真', '儿童摄影', '证件照');

                category2Array[1] = '服装';
                category3Array[1] = new Array('女装', '男装', 'T恤', '裙子', '衬衫', '长裤', '短裤', '针织衫/毛衣', '外套', '棉服/羽绒服', '打底裤/打底衫', '运动服', '内衣/家居服', '吊带/背心', '童装', '袜子', '其他');

                category2Array[2] = '配饰';
                category3Array[2] = new Array('眼镜', '首饰', '帽子', '腰带', '围巾', '手套', '烟具', '其他');

                category2Array[3] = '箱包';
                category3Array[3] = new Array('女包', '男包', '钱包', '功能箱包');

                category2Array[4] = '鞋靴';
                category3Array[4] = new Array('女鞋', '男鞋', '运动鞋', '单鞋', '凉鞋/凉拖', '帆布鞋', '雪地靴', '童鞋');

                category2Array[5] = '化妆品';
                category3Array[5] = new Array('面部保养', '眼唇保养', '身体护理', '彩妆', '香水', '美容工具', '男士护肤', '其他');

                category2Array[6] = '运动户外';
                category3Array[6] = new Array('运动装备', '户外用品', '其他');

                category2Array[7] = '手机数码';
                category3Array[7] = new Array('手机', '手机配件', '摄影摄像', '电脑数码', '时尚影音', '其他');

                category2Array[8] = '家用电器';
                category3Array[8] = new Array('生活电器', '厨房电器', '个人护理', '健康电器', '其他');

                category2Array[9] = '生活家居';
                category3Array[9] = new Array('床上用品', '厨卫用品', '清洁用品', '生活日用', '成人用品');

                category2Array[10] = '汽车配件';
                category3Array[10] = new Array('汽车保养', '汽车饰品', '其他');

                category2Array[11] = '图书音像';
                category3Array[11] = new Array('图书', 'DVD', '软件');

                category2Array[12] = '食品饮料';
                category3Array[12] = new Array('零食', '茶酒饮料', '粮油蔬果', '保健品', '其他');

                category2Array[13] = '母婴玩具';
                category3Array[13] = new Array('妈妈用品', '宝宝用品', '玩具', '其他');

                var category2Index = -1;
                for (var key in category2Array) {
                    if (category2 == category2Array[key]) {
                        category2Index = key;
                        break;
                    }
                }

                if (category2Index < 0) {
                    $("#goods_edit_sogoutuan_category_3").html("<option value=' '>没有三级分类</option>");
                    $("#goods_edit_sogoutuan_category_3").select2('val', null);
                    return;
                }

                var htmlString = '';
                var destArray = category3Array[category2Index];
                for (var key in destArray) {
                    if (category3 == destArray[key]) {
                        htmlString += '<option value="' + destArray[key] + '" selected>' + destArray[key] + '</option>';
                    } else {
                        htmlString += '<option value="' + destArray[key] + '">' + destArray[key] + '</option>';
                    }
                }
                $("#goods_edit_sogoutuan_category_3").html(htmlString);
                $("#goods_edit_sogoutuan_category_3").select2('val', null);
            }

            $("#goods_edit_sogoutuan_category_2").change(
                    function () {
                        var category2 = $("#goods_edit_sogoutuan_category_2").find('option:selected').val();
                        renderSogouCategory3(category2, null);
                    }
            );

            // 页面刚加载执行一次
            renderSogouCategory3($("#goods_edit_sogoutuan_category_2").find('option:selected').val(), $("#sogou_category_3_value").find('option:selected').val());

            // 页面刚加载执行一次
            if ($("#goods_edit_sogoutuan_category_1").size() > 0) {
                $("#goods_edit_sogoutuan_category_1").select2('val', $("#goods_edit_sogoutuan_category_1").attr('data-initValue'));

                renderSogouCategory2($("#goods_edit_sogoutuan_category_1").find('option:selected').val(), null);
                $("#goods_edit_sogoutuan_category_2").select2('val', $("#goods_edit_sogoutuan_category_2").attr('data-initValue'));

                renderSogouCategory3($("#goods_edit_sogoutuan_category_2").find('option:selected').val(), null);
                $("#goods_edit_sogoutuan_category_3").select2('val', $("#goods_edit_sogoutuan_category_3").attr('data-initValue'));
            }

        })(jQuery);


        /**************** goods_edit_promote.tpl  tuan2345 分类选择，级联选择 ***********************/
        $('#goods_edit_tuan2345_category').change(function () {
            var goods_edit_tuan2345_category = $('#goods_edit_tuan2345_category').find('option:selected').val();

            var callUrl = bZF.makeUrl('/Ajax/Tree/ListChildTreeNodeAllStr?treeKey=tuan2345_goods_category&treeNodeName='
                    + goods_edit_tuan2345_category);

            // ajax  调用
            bZF.ajaxCallGet(callUrl, function (data) {
                if (!data) {
                    bZF.showMessage('没有 2345 分类');
                    return;
                }
                var categoryArray = data;
                // 设置 goods_edit_tuan2345_category_end 的数据
                var optionHtml = '<option value=""></option>';
                $.each(categoryArray, function (index, elem) {
                    optionHtml += '<option value="' + elem.meta_name + '">' + elem.display_text + '</option>';
                });
                $('#goods_edit_tuan2345_category_end').html(optionHtml);
                //重新设置一次初始值
                $('#goods_edit_tuan2345_category_end').select2('val', null);
            });
        });


    })(window, jQuery));
    </script>
{{/block}}
