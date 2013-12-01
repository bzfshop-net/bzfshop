jQuery((function ($) {

    /**
     * 我们定义自己的命名空间
     */
    var themeShop = {};
    bZF.themeShop = themeShop;

    /**
     * 上传 slider image
     */
    bZF.uploadAdvImage('#theme_shop_slider_image_upload_button',
        function (clickObject, url, title, width, height, border, align) {
            var $cloneNode = $('#theme_shop_slider_image_container_clone').clone();
            $cloneNode.removeAttr('id');
            $('img', $cloneNode).attr('src', url);
            $('input[name="image[]"]', $cloneNode).val(url);
            $('#theme_shop_slider_image_list').prepend($cloneNode);
            // 激发一次设置对话框
            $('.theme_shop_slide_image_property_button', $cloneNode).trigger('click');
        });

    /**
     * 打开 slide image 属性对话框
     *
     * @param node slide image 的 container
     */
    themeShop.open_slider_image_property_modal = function (node) {
        var $modal = $('#theme_shop_slider_image_property_modal');
        $modal.data('slide_image_container', node);

        // 设置对话框的值
        $('input[name="image"]', $modal).val($('input[name="image[]"]', node).val());
        $('input[name="url"]', $modal).val($('input[name="url[]"]', node).val());

        var target = $('input[name="target[]"]', node).val();
        if (target == '_blank') {
            $('input[name="target"]', $modal).attr('checked', 'checked');
        } else {
            $('input[name="target"]', $modal).removeAttr('checked');
        }

        $modal.modal();
    };

    /**
     * 确认关闭 slide image 属性对话框
     */
    themeShop.confirm_slider_image_property_modal = function () {
        var $modal = $('#theme_shop_slider_image_property_modal');
        var slideImageContainer = $('#theme_shop_slider_image_property_modal').data('slide_image_container');

        if (slideImageContainer) {

            // 同步值
            $('input[name="image[]"]', slideImageContainer).val($('input[name="image"]', $modal).val());
            $('input[name="url[]"]', slideImageContainer).val($('input[name="url"]', $modal).val());

            var targetChecked = $('input[name="target"]', $modal).attr('checked');
            if (targetChecked) {
                $('input[name="target[]"]', slideImageContainer).val('_blank');
            } else {
                $('input[name="target[]"]', slideImageContainer).val('_self');
            }

            // 设置显示属性
            $('img', slideImageContainer).attr('src', $('input[name="image"]', $modal).val());
            $('a', slideImageContainer).attr('href', $('input[name="url"]', $modal).val());

        }
        // 关闭对话框
        $modal.modal('hide');
    }

    /**
     * theme_shop_advshop_block.tpl 广告标签 hover 显示 toolbar
     */
    themeShop.enhanceAdvTabBlockTab = function (tabLiNode) {

        // 有 button 的不 enhance
        if ($('button', tabLiNode).size() > 0) {
            return;
        }

        // 标题栏不要 enhance
        if ($('a.bzf_caption', tabLiNode).size() > 0) {
            return;
        }

        var tabText = $.trim($('a', tabLiNode).text());

        var htmlContent = '<input type="text" class="span1" value="' + tabText + '" />&nbsp;<button type="button" class="btn btn-info" onclick="bZF.themeShop.renameAdvBlockTab(null,this, this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode); return false;"><i class="icon-ok"></i></button><button type="button" class="btn  btn-success" onclick="bZF.themeShop.movePrevAdvBlockTab(this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode); return false;;"><i class="icon-arrow-left"></i></button><button type="button" class="btn btn-danger" onclick="bZF.removeTab(this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode); return false;"><i class="icon-remove"></i></button><button type="button" class="btn btn-success" onclick="bZF.themeShop.moveNextAdvBlockTab(this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode); return false;"><i class="icon-arrow-right"></i></button><a style="margin-left:15px;" href="#" onclick="jQuery(\'span.badge\',this.parentNode.parentNode.parentNode.parentNode.parentNode).popover(\'hide\');">X</a>';

        $('a span.badge', tabLiNode).popover({
            content: htmlContent,
            trigger: 'click',
            html: true,
            placement: 'top',
            template: '<div class="popover" style="width:340px;"><div class="arrow"></div><div class="popover-inner"><div class="popover-content"><p></p></div></div></div>'
        });
    };

    $('#theme_shop_adv_block_tabbar li,.bzf_shop_index_adv_block .nav-tabs li').each(function () {
        themeShop.enhanceAdvTabBlockTab(this);
    });

    /**
     * Tab 改名字
     *
     * @param btnObject
     * @param currentTabLiNode
     */
    themeShop.renameAdvBlockTab = function (newName, btnObject, currentTabLiNode) {
        if (!newName) {
            newName = $.trim($('input', btnObject.parentNode).val());
        }
        if ('' == newName) {
            bZF.showMessage('标题不能为空');
            return;
        }

        if ($('a', currentTabLiNode).hasClass('bzf_caption')) {
            // 整个广告块的大标题，不要破坏了 span 结构
            var children = $('a', currentTabLiNode).children();
            $('a', currentTabLiNode).text(newName);
            $('a', currentTabLiNode).prepend(children);

            // 修改顶部大 Tab 标题
            $('#theme_shop_adv_block_tabbar li.active a').html(newName + '<span class="badge badge-warning"><i class="icon-info-sign"></i></span>');
            // 重新 enhance 这个节点
            themeShop.enhanceAdvTabBlockTab('#theme_shop_adv_block_tabbar li.active');
            return;
        }

        $('a', currentTabLiNode).html(newName + '<span class="badge badge-warning"><i class="icon-info-sign"></i></span>');

        // 重新 enhance 这个节点
        themeShop.enhanceAdvTabBlockTab(currentTabLiNode);

        // 如果下面有子 tab，一起改名
        var $currentPanelNode = $($('a', currentTabLiNode).attr('href'));
        if ($('.bzf_caption', $currentPanelNode).size() > 0) {
            $('li:has(a.bzf_caption)', $currentPanelNode).each(function () {
                themeShop.renameAdvBlockTab(newName, null, this);
            });
        }
    };

    /**
     * Tab 前移
     *
     * @param currentTabLiNode
     */
    themeShop.movePrevAdvBlockTab = function (currentTabLiNode) {
        // 跳过 popover
        if ($(currentTabLiNode).prev().hasClass('popover')) {
            bZF.moveNodePrev(currentTabLiNode);
        }

        var $prevNode = $(currentTabLiNode).prev();
        if ($('a', $prevNode).hasClass('bzf_caption')) {
            // 前面是标题了，不能跑标题前面去
            return;
        }
        bZF.moveTabPrev(currentTabLiNode);
    };

    /**
     * Tab 后移
     *
     * @param currentTabLiNode
     */
    themeShop.moveNextAdvBlockTab = function (currentTabLiNode) {
        // 跳过 popover
        if ($(currentTabLiNode).next().hasClass('popover')) {
            bZF.moveNodeNext(currentTabLiNode);
        }

        var nextNode = $(currentTabLiNode).next();
        if ($('button', nextNode).size() > 0) {
            // 后面是添加 button，不能再后移了
            return;
        }

        // 移动 tab 和它的内容
        bZF.moveTabNext(currentTabLiNode);
    };

    /**
     * theme_shop_advshop_block.tpl 选择不同的主题
     */
    themeShop.enhanceAdvBlockThemeSelect = function (containerNode) {

        $('.bzf_shop_index_adv_block_theme_select', containerNode).each(function () {
            // 监听 change 事件
            $(this).change(function () {

                var themeClass = $(this).find('option:selected').val();
                //更新主题显示
                $('.bzf_shop_index_adv_block', this.parentNode.parentNode).attr('class', 'bzf_shop_index_adv_block ' + themeClass);
            });

            // 设置初始值
            var initValue = $(this).attr('data-initValue');
            if (initValue) {
                $(this).attr('data-initValue', null);
                $(this).prop("selectedIndex", 1);

                $('option', this).filter(function () {
                    return $(this).val() == initValue;
                }).prop('selected', true);
            }

            $(this).trigger('change');

        });
    };
    // 对整个 html 做一次 enhance
    themeShop.enhanceAdvBlockThemeSelect(document);

    /**
     * 点击 a 打开 advblock image 属性对话框
     */
    themeShop.enhanceAdvBlockAClick = function (panel) {
        $('.image_left, .image_center, .image_right', panel).click(function () {

            var $modal = $('#theme_shop_advblock_image_property_modal');
            $modal.data('advblock_image_container', this);

            // 设置对话框的值
            $('input[name="image"]', $modal).val($(this).attr('data-image'));
            $('input[name="url"]', $modal).val($(this).attr('data-url'));

            var target = $(this).attr('data-target');
            if (!target) {
                target = '_blank';
            }
            if (target == '_blank') {
                $('input[name="target"]', $modal).attr('checked', 'checked');
            } else {
                $('input[name="target"]', $modal).removeAttr('checked');
            }

            $modal.modal();

            return false;
        });
    };
    themeShop.enhanceAdvBlockAClick(document);

    /**
     * 复制一个 adv block tab
     */
    themeShop.cloneAdvBlockTab = function (targetTabLiNode) {
        var newTargetId = bZF.cloneTabPanel(targetTabLiNode);
        $('.nav-tabs a[href="#' + newTargetId + '"]').tab('show');
        // 增强 html
        var newPanel = $('#' + newTargetId);
        themeShop.enhanceAdvTabBlockTab($('li:has(a[href="#' + newTargetId + '"])'));
        themeShop.enhanceAdvTabBlockTab();
        $('.nav-tabs li', newPanel).each(function () {
            themeShop.enhanceAdvTabBlockTab(this);
        });
        themeShop.enhanceAdvBlockThemeSelect(newPanel);
        themeShop.enhanceAdvBlockAClick(newPanel);
    };


    /**
     * 确认关闭 advblock image 属性对话框
     */
    themeShop.confirm_advblock_image_property_modal = function () {
        var $modal = $('#theme_shop_advblock_image_property_modal');
        var advblockImageContainer = $('#theme_shop_advblock_image_property_modal').data('advblock_image_container');

        if (advblockImageContainer) {

            // 同步值
            $(advblockImageContainer).attr('data-image', $('input[name="image"]', $modal).val());
            $(advblockImageContainer).attr('data-url', $('input[name="url"]', $modal).val());

            var targetChecked = $('input[name="target"]', $modal).attr('checked');
            if (targetChecked) {
                $(advblockImageContainer).attr('data-target', '_blank');
            } else {
                $(advblockImageContainer).attr('data-target', '_self');
            }

            // 设置显示属性
            $('img', advblockImageContainer).attr('src', $('input[name="image"]', $modal).val());
        }
        // 关闭对话框
        $modal.modal('hide');
    }

    /**
     * 上传 advblock image
     */
    bZF.uploadAdvImage('#theme_shop_advblock_image_upload_button',
        function (clickObject, url, title, width, height, border, align) {
            var $modal = $('#theme_shop_advblock_image_property_modal');
            $('input[name="image"]', $modal).val(url);
        });

    /**
     * adv block 的数据层级结构，数据结构见例子 advblockdata.json
     *
     * bzf_shop_index_adv_block ---> li a 标题 ---> bzf_shop_index_adv_image_block 图片集
     *
     */
    themeShop.encode_advblock_data = function () {
        var advBlockArray = [];

        // 取得总的 tab 标题
        $('#theme_shop_adv_block_tabbar a').not('a:has(button)').each(function () {
            var advBlockObject = {};
            advBlockArray.push(advBlockObject);
            advBlockObject['title'] = $(this).text();
            advBlockObject['advBlockImageArray'] = [];
        });

        var advBlockIndex = 0;
        $('.bzf_shop_index_adv_block').each(function () {
            if (!advBlockArray[advBlockIndex]) {
                return;
            }
            // 记录 advBlock 的主题设置
            advBlockArray[advBlockIndex]['theme_class'] = $('.bzf_shop_index_adv_block_theme_select', this.parentNode).find('option:selected').val();

            var advBlockImageArray = advBlockArray[advBlockIndex]['advBlockImageArray'];
            advBlockIndex++;

            // 先取得对应的 title
            $('.nav-tabs a', this).not('a.bzf_caption', this).not('a:has(button)', this).each(function () {
                var advBlockImageObject = {};
                advBlockImageObject['title'] = $(this).text();
                advBlockImageArray.push(advBlockImageObject);
            });

            var advBlockImageIndex = 0;
            // 取得对应的图片设置
            $('.bzf_shop_index_adv_image_block', this).each(function () {
                // 对象不存在，不应该出现这种情况
                if (!advBlockImageArray[advBlockImageIndex]) {
                    console.log('advBlockImageIndex ' + advBlockImageIndex + ' does not exist');
                    return;
                }
                // 取得对应的 object
                var advBlockImageObject = advBlockImageArray[advBlockImageIndex];
                advBlockImageIndex++;

                // 填充左边的 image
                var imageLeftArray = [];
                advBlockImageObject['image_left'] = imageLeftArray;
                $('.image_left', this).each(function () {
                    var imageLeftObject = {};
                    imageLeftArray.push(imageLeftObject);
                    imageLeftObject['image'] = $(this).attr('data-image');
                    imageLeftObject['url'] = $(this).attr('data-url');
                    imageLeftObject['target'] = $(this).attr('data-target');
                });

                // 填充中间的 image
                var imageCenterArray = [];
                advBlockImageObject['image_center'] = imageCenterArray;
                $('.image_center', this).each(function () {
                    var imageCenterObject = {};
                    imageCenterArray.push(imageCenterObject);
                    imageCenterObject['image'] = $(this).attr('data-image');
                    imageCenterObject['url'] = $(this).attr('data-url');
                    imageCenterObject['target'] = $(this).attr('data-target');
                });

                // 填充右边的 image
                var imageRightArray = [];
                advBlockImageObject['image_right'] = imageRightArray;
                $('.image_right', this).each(function () {
                    var imageRightObject = {};
                    imageRightArray.push(imageRightObject);
                    imageRightObject['image'] = $(this).attr('data-image');
                    imageRightObject['url'] = $(this).attr('data-url');
                    imageRightObject['target'] = $(this).attr('data-target');
                });

            });

        });

        return JSON.stringify(advBlockArray);
    };

    themeShop.advblock_data_submit = function () {
        var advJsonData = themeShop.encode_advblock_data();
        $('#theme_shop_advblock_json_data').val(advJsonData);
        return true;
    };

})(jQuery));