/**
 * @author QiangYu
 *
 * 棒主妇商户后台 的 JS 程序
 *
 */

/**
 * 这里的代码立即执行
 */
(function (window, $) {

    /************** fix ajax call getJSON with no response in IE **************/
    jQuery.support.cors = true;

    if (!window.console) {
        window.console = { log: function () {
        } };
    }

    /******* 建立自己的命名空间 ******** */
    var bZF = {};
    window.bZF = bZF;

    /** ** 判读浏览器是否为 IE6 *** */
    (function (targetObj) {
        window.bzFWETEWXX_isIE6 = false;
        document.write("<!--[if lt IE 7]><script>bzFWETEWXX_isIE6=true;</script><![endif]-->");
        targetObj.isIE6 = bzFWETEWXX_isIE6;

        window.bzFWETEWXX_isIE7 = false;
        document.write("<!--[if lt IE 8]><script>bzFWETEWXX_isIE7=true;</script><![endif]-->");
        targetObj.isIE7 = bzFWETEWXX_isIE7;

    })(bZF);

})(window, jQuery);


/**
 * 这里的代码等 document.ready 才执行
 */
jQuery((function (window, $) {

    /** ------------------------------------- 后台系统通用的代码 -----------------------------------------**/

    if ($.pnotify) {
        // pnotify 不要显示历史记录
        $.pnotify.defaults.history = false;
        // 缺省显示 4 秒就退出
        $.pnotify.defaults.delay = 4000;
    }

    Date.prototype.pattern = function (fmt) {
        var o = {
            "M+": this.getMonth() + 1, //月份
            "d+": this.getDate(), //日
            "h+": this.getHours() % 12 == 0 ? 12 : this.getHours() % 12, //小时
            "H+": this.getHours(), //小时
            "m+": this.getMinutes(), //分
            "s+": this.getSeconds(), //秒
            "q+": Math.floor((this.getMonth() + 3) / 3), //季度
            "S": this.getMilliseconds() //毫秒
        };
        var week = {
            "0": "/u65e5",
            "1": "/u4e00",
            "2": "/u4e8c",
            "3": "/u4e09",
            "4": "/u56db",
            "5": "/u4e94",
            "6": "/u516d"
        };
        if (/(y+)/.test(fmt)) {
            fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
        }
        if (/(E+)/.test(fmt)) {
            fmt = fmt.replace(RegExp.$1, ((RegExp.$1.length > 1) ? (RegExp.$1.length > 2 ? "/u661f/u671f" : "/u5468") : "") + week[this.getDay() + ""]);
        }
        for (var k in o) {
            if (new RegExp("(" + k + ")").test(fmt)) {
                fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
            }
        }
        return fmt;
    };

    bZF.slideToggle = function (itemId) {
        $(itemId).slideToggle();
    };

    /**
     * 商品价格我们使用 千分位，这里转化成显示格式
     *
     * @param int price
     */
    bZF.moneyToSmartDisplay = function (price) {
        var floatValue = parseFloat(price / 1000);
        var intValue = parseInt(floatValue);

        var absIntValue = Math.abs(intValue);
        var absFloatValue = Math.abs(floatValue);

        if (absIntValue < absFloatValue) {
            // 去掉右边多余的 0
            return (floatValue.toFixed(2)).replace(/0+$/, '');
        }

        return intValue;
    }

    /**
     * 删除一个 html 节点
     * @param node
     */
    bZF.removeNode = function (node) {
        $(node).remove();
    }

    /**
     * 前移一个节点
     *
     * @param node
     */
    bZF.moveNodePrev = function (node) {
        var $prevNode = $(node).prev();
        if ($prevNode) {
            $prevNode.before(node);
        }
    };

    /**
     * 后移一个节点
     *
     * @param node
     */
    bZF.moveNodeNext = function (node) {
        var $nextNode = $(node).next();
        if ($nextNode) {
            $nextNode.after(node);
        }
    };

    /**
     * 针对 Tab 操作，删除一个 tab
     *
     * @param url
     * @returns {*}
     */
    bZF.removeTab = function (tabLiNode) {
        // 删除 ul, li
        $(tabLiNode).remove();
        // 删除对应的 target panel
        var $targetPanel = $($('a', tabLiNode).attr('href'));
        $targetPanel.remove();
    };

    bZF.moveTabPrev = function (tabLiNode) {
        bZF.moveNodePrev(tabLiNode);
        var $targetPanel = $($('a', tabLiNode).attr('href'));
        bZF.moveNodePrev($targetPanel);
    };

    bZF.moveTabNext = function (tabLiNode) {
        bZF.moveNodeNext(tabLiNode);
        var $targetPanel = $($('a', tabLiNode).attr('href'));
        bZF.moveNodeNext($targetPanel);
    };

    bZF.cloneTabPanel = function (tabLiNode) {

        var targetHref = $('a', tabLiNode).attr('href');
        var newTargetHref = '#new_id_' + new Date().getTime() + '_' + Math.floor((Math.random() * 10000) + 1);

        // 增加一个 liNode
        var newLiNode = $(tabLiNode).clone();
        $(newLiNode).removeClass('active');
        $('a', newLiNode).attr('href', newTargetHref);
        tabLiNode.after(newLiNode);

        // 增加对应的 panel
        var panelNode = $(targetHref);
        var newPanelNode = panelNode.clone();
        // 去除 id 前面开头的 #
        $(newPanelNode).attr('id', newTargetHref.substr(1));
        $(newPanelNode).removeClass('active');
        bZF.regenerateAttrId(newPanelNode);
        panelNode.after(newPanelNode);

        // 返回 ID 号
        return newTargetHref.substr(1);
    };

    /**
     * html 里面不允许有重复的 ID，我们这里 替换所有的 ID
     * @param node
     */
    bZF.regenerateAttrId = function (node) {
        var idHashMap = {};

        $('*[id]', node).each(function () {
            var idAttr = $(this).attr('id');
            if (idAttr) {
                if (!idHashMap[idAttr]) {
                    idHashMap[idAttr] = 'fix_id_' + new Date().getTime() + '_' + Math.floor((Math.random() * 10000) + 1);
                }
                $(this).attr('id', idHashMap[idAttr]);
            }
        });

        $('*[href]', node).each(function () {
            var hrefAttr = $(this).attr('href');
            if (hrefAttr && hrefAttr.substr(0, 1) == '#') {
                // 指向 id ，需要 fix
                var idValue = hrefAttr.substr(1);
                if (idHashMap[idValue]) {
                    $(this).attr('href', '#' + idHashMap[idValue]);
                }
            }
        });

    };

    /**
     * 构建完整的 URL，有一些跨域请求需要完整的 URL 才能执行
     *
     * @param url
     * @returns {*}
     */
    bZF.makeUrl = function (url) {
        return WEB_ROOT_HOST + WEB_ROOT_BASE + url;
    };

    /**
     * 做 ajax 调用
     *
     * @param callUrl
     * @param successFunc  成功回调
     * @param failFunc  失败回调
     */
    bZF.ajaxCallGet = function (callUrl, successFunc, failFunc) {
        // ajax  调用
        $.ajax({
            type: "get",
            url: callUrl,
            dataType: "json",
            success: function (result) {
                if (result.error) {

                    if (result.error.message) {
                        bZF.showMessage(result.error.message);
                    } else {
                        bZF.showMessage('调用失败');
                    }
                    return;
                }

                if (null == result.data) {
                    console.log('没有返回数据[' + callUrl + ']');
                }

                // 调用回调函数
                successFunc(result.data);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {

                if (failFunc) {
                    failFunc(XMLHttpRequest, textStatus, errorThrown);
                    return;
                }

                bZF.showMessage('网络错误');
            }
        });
    };

    /**
     * ajax Post 调用
     *
     * @param callUrl
     * @param paramObject
     * @param successFunc
     * @param failFunc
     */
    bZF.ajaxCallPost = function (callUrl, data, successFunc, failFunc) {
        // ajax  调用
        $.ajax({
            type: "post",
            url: callUrl,
            data: data,
            dataType: "json",
            success: function (result) {
                if (result.error) {

                    if (result.error.message) {
                        bZF.showMessage(result.error.message);
                    } else {
                        bZF.showMessage('调用失败');
                    }
                    return;
                }

                if (null == result.data) {
                    console.log('没有返回数据[' + callUrl + ']');
                }

                // 调用回调函数
                successFunc(result.data);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {

                if (failFunc) {
                    failFunc(XMLHttpRequest, textStatus, errorThrown);
                    return;
                }

                bZF.showMessage('网络错误');
            }
        });
    };


    /******** 打印出错消息，方便检查错误 *********/
    bZF.showMessage = function (message, type) {
        if (!type) {
            type = 'success';
        }
        // 显示消息
        $.pnotify({
            text: message,
            type: type,
            delay: 3000,
            before_open: function (pnotify) {
                // Position this notice in the center of the screen.
                pnotify.css({
                    "top": ($(window).height() / 2) - (pnotify.height() / 2),
                    "left": ($(window).width() / 2) - (pnotify.width() / 2)
                });
            }
        });

        // 显示在 console ，方便调试
        console.log('[' + new Date().pattern("yyyy-MM-dd HH:mm:ss") + ']' + message);
    };

    /****************** 设置 breadcrumb 导航，我们采用 jStorage 做本地存储 *******************/
    (function ($) {
        var breadCrumbKey = 'bzf_breadCrumbKey';
        bZF.setBreadCrumb = function (index, text, link) {
            var breadCrumbArray = $.jStorage.get(breadCrumbKey);
            if (!breadCrumbArray) {
                breadCrumbArray = [];
            }

            //清除掉从 index 往后所有的记录
            breadCrumbArray = breadCrumbArray.slice(0, index);
            breadCrumbArray.push({index: index, text: text, link: link});
            $.jStorage.set(breadCrumbKey, breadCrumbArray);
        };

        // 执行网页的 breadcrumb 设置
        if (window.bz_set_breadcrumb_status) {
            for (var i = 0; i < window.bz_set_breadcrumb_status.length; i++) {
                var item = window.bz_set_breadcrumb_status[i];
                bZF.setBreadCrumb(item.index, item.text, item.link);
            }
        }

        // 根据 breadCrumb 中保存的数据显示 breadCrumb 栏目
        var breadCrumbArray = $.jStorage.get(breadCrumbKey);

        if (!breadCrumbArray || breadCrumbArray.length <= 1) {
            // 只有一条数据则不显示
            return;
        }

        var htmlStr = '';
        for (var i = 0; i < breadCrumbArray.length; i++) {
            var item = breadCrumbArray[i];
            htmlStr += '<li><a href="' + item.link + '">' + item.text + '</a><span class="divider">/</span></li>';
        }
        htmlStr = '<ul class="breadcrumb">' + htmlStr + '</ul>';
        $('#main_body_breadcrumb').html(htmlStr);
        $('#main_body_breadcrumb').show();
    })(jQuery);


    /************  加载验证码图片 ****************/
    bZF.loadCaptchaImage = function (itemId) {
        var time = new Date().getTime();
        //取得验证码图片
        $(itemId).html('<a href="#" onclick="bZF.loadCaptchaImage(\'' + itemId + '\')"><img width="150" height="50" src="'
            + bZF.makeUrl('/Image/Captcha') + '?rand=' + time + '" /></a>');
        // IE6 不会自动缩放图片
        if (bZF.isIE6 || bZF.isIE7) {
            $('img', $(itemId)).width(150);
            $('img', $(itemId)).height(50);
        }

    };

    /*********** 如果 Cookie 中有 flash message 则显示它 *************/
    (function ($) {
        var oldConfig = $.cookie.json;
        $.cookie.json = true;
        var flashMessageStr = $.cookie("flash_message");
        $.cookie.json = oldConfig;
        if (!flashMessageStr) {
            return;
        }

        var msgHtml = '';
        // 解析 json, flash message 只是简单的一维数组
        $.each(flashMessageStr, function (i, item) {
            msgHtml += '<li>' + item + '</li>';
        });

        msgHtml = '<ul>' + msgHtml + '</ul>';

        // 显示消息
        bZF.showMessage(msgHtml);

        // 删除对应 Cookie，flash message 只显示一次
        $.removeCookie("flash_message", {path: WEB_ROOT_BASE + '/'});
    })(jQuery);

    /**
     * 这里设置各个页面的导航栏 “选中/未选中” 状态
     * window.bz_set_nav_status 是一个数组，里面包含了一串用于设置导航栏状态的 javascript 方法
     * */
    (function ($) {
        if (window.bz_set_nav_status) {
            $.each(window.bz_set_nav_status, function (i, value) {
                value($);
            });
        }
    })(jQuery);

    /*********  开启 pretty-loader 功能 ************/
    if ($.prettyLoader) {
        $.prettyLoader();
    }

    /********* 检查表单是否发生过修改，如果修改过并且用户要离开我们需要提醒用户是否保存 **********/
    $('form.form-dirty-check').areYouSure({
        'message': '内容发生修改，你确定不保存就离开？'
    });

    /**
     *  增强 html 的显示效果，包括增强一些 JavaScript 的验证
     *
     *  注意：在调用这个函数之前你必须已经把 node 插入到整个网页中了，而不是一个悬空的 node
     *
     * @param node
     */
    bZF.enhanceHtml = function (node) {

        /********** validator 验证 **********/
        $("input,select,textarea", node).not('[type="image"],[type="submit"],[data-no-validation="true"],.select2-input,.select2-simple,.select2-simple-tag,.ke-edit-textarea,.editor-html-simple').jqBootstrapValidation({
            filter: function () {
                //如果元素有 data-no-validation 属性我们就不做任何 validation
                if ($(this).attr("data-no-validation")
                    || $(this).hasClass('select2-input')
                    || $(this).hasClass('select2-simple')
                    || $(this).hasClass('ke-edit-textarea')
                    || $(this).hasClass('editor-html-simple')) {
                    return false;
                }
                return true;
            }
        });

        /************ 对 select2 的扩展，使用起来更加方便 *************/
            // 对 select 2 的扩展
            // context 是上下文环境，比如你用 ajax load 了一个网页进来，然后你希望使用里面的 select 元素，
            // 这里你就可以指定 context 来执行
        $('select.select2-simple', node).each(function (index, elem) {

            // 自动做 ajax 调用加载数据
            if ($(elem).attr('data-ajaxCallUrl')) {
                var callUrl = $(elem).attr('data-ajaxCallUrl');
                var valueKey = $(elem).attr('data-option-value-key');
                var textKey = $(elem).attr('data-option-text-key');

                bZF.ajaxCallGet(callUrl, function (data) {
                    // 没有数据
                    if (!data) {
                        // 初始化，并且设置初始值
                        $(elem).select2({
                            minimumResultsForSearch: 10, // 10 个选项以上才执行搜索功能
                            allowClear: true
                        }).select2('val', $(elem).attr('data-initValue'));
                        return;
                    }

                    // 加入各个数据
                    $.each(data, function (index, dataItem) {
                        $(elem).append($('<option value="' + dataItem[valueKey] + '">' + dataItem[textKey] + '</option>'));
                    });

                    // 获取初始值
                    var dataInitValue = $(elem).attr('data-initValue');

                    // 如果是多项选择，必然是 tag 显示
                    if (dataInitValue && '' != $.trim(dataInitValue) && $(elem).attr('multiple')) {
                        var length = dataInitValue.length;

                        // 去掉尾部的 ','
                        if (',' == dataInitValue[length - 1]) {
                            length--;
                        }
                        // 去除头尾的 ','
                        var startIndex = (',' == dataInitValue[0]) ? 1 : 0;
                        if (startIndex > 0) {
                            length--;
                        }

                        // 截取子串
                        dataInitValue = dataInitValue.substr(startIndex, length);
                        dataInitValue = dataInitValue.split(',');
                    }

                    // 缺省格式化只是普通文本
                    var formatResultFunc = function (item) {
                        return item.text;
                    };
                    var formatSelectionFunc = formatResultFunc;

                    if ($(elem).attr('data-option-value-image')) {
                        formatResultFunc = function (item) {
                            return '<img src="' + item.text + '"/>';
                        };
                        formatSelectionFunc = function (item) {
                            return '图片 ' + item.id;
                        };
                    }

                    // 初始化 select2 控件
                    $(elem).select2({
                        minimumResultsForSearch: 10, // 10 个选项以上才执行搜索功能
                        allowClear: true,
                        formatResult: formatResultFunc,
                        formatSelection: formatSelectionFunc
                    });

                    // 设置控件的初始值
                    if (dataInitValue && '' != $.trim(dataInitValue)) {
                        $(elem).select2('val', dataInitValue);
                    }

                });
            } else {
                // 初始化，并且设置初始值
                $(elem).select2({
                    minimumResultsForSearch: 10, // 10 个选项以上才执行搜索功能
                    allowClear: true
                }).select2('val', $(elem).attr('data-initValue'));
            }

        });

        /************ 初始化日历插件，可以显示日历的选择 ***************/
        $('div.datetimepicker', node).datetimepicker({
            format: 'yyyy-MM-dd hh:mm:ss',
            todayHighlight: true,
            language: 'zh-cn'
        });

        /**************** 普通简单的文本编辑框的创建，缺省主题 *******************/
        $('textarea.editor-html-simple', node).each(function (index, elem) {
            KindEditor.create(elem, {
                filterMode: true,
                themeType: 'default',
                cssData: "body {font-family: '微软雅黑', 'Microsoft Yahei', '宋体', 'songti', STHeiti, Helmet, Freesans, sans-serif;font-size: 15px; }",
                width: $(elem).outerWidth(false),
                items: [
                    'removeformat', 'fontsize', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
                    'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
                    'insertunorderedlist', 'link', 'table', 'source']
            });
        });

        /** 开启 tooltip 功能显示 ***/
        $('*[rel="tooltip"]', node).tooltip({delay: 200});

        /** ***** 开启 popover 的效果 ******* */
        $("a[ref='popover']", node).popover();

        /********* 开启 clickover 效果 *********/
        $('a[rel="clickover"]', node).clickover({
            html: true,
            template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="popover-content"><p></p></div></div></div>'
        });

        /**
         * **** 开启图片懒加载，所有 <img class="lazyload" src="placehold.jpg" data-original="pic.jpg" /> ******
         */
        $("img.lazyload", node).show().lazyload({
            effect: "fadeIn"
        });

        /***** 开启图片相册功能 *****/
        $("a[rel^='prettyPhoto']", node).prettyPhoto({
            animation_speed: 'normal', /* fast/slow/normal */
            slideshow: 4000, /* false OR interval time in ms */
            autoplay_slideshow: false, /* true/false */
            opacity: 0.80, /* Value between 0 and 1 */
            show_title: true, /* true/false */
            allow_resize: true, /* Resize the photos bigger than viewport. true/false */
            default_width: 500,
            default_height: 344,
            counter_separator_label: '/', /* The separator for the gallery counter 1 "of" 2 */
            theme: 'facebook', /* light_rounded / dark_rounded / light_square / dark_square / facebook */
            horizontal_padding: 20, /* The padding on each side of the picture */
            hideflash: false, /* Hides all the flash object on a page, set to TRUE if flash appears over prettyPhoto */
            wmode: 'opaque', /* Set the flash wmode attribute */
            autoplay: true, /* Automatically start videos: True/False */
            modal: false, /* If set to true, only the close button will close the window */
            deeplinking: true, /* Allow prettyPhoto to update the url to enable deeplinking. */
            overlay_gallery: true, /* If set to true, a gallery will overlay the fullscreen image on mouse over */
            keyboard_shortcuts: true, /* Set to false if you open forms inside prettyPhoto */
            changepicturecallback: function () {
            }, /* Called everytime an item is shown/changed */
            callback: function () {
            }, /* Called when prettyPhoto is closed */
            ie6_fallback: true,
            social_tools: false /* html or false to disable */
        });
    };

    // 对整个 document 做一次 enhance
    bZF.enhanceHtml($('body'));


    /**
     * 点击上传图片
     *
     * @param clickSelector
     * @param callback
     * @param dirName
     */
    bZF.uploadImage = function (clickSelector, callback, dirName) {

        var uploadDirName = 'image';
        if (dirName) {
            uploadDirName = dirName;
        }

        $(clickSelector).click(function () {
            var clickObject = this;
            var editor = KindEditor.editor({
                allowFileManager: false,
                formatUploadUrl: false,
                uploadJson: bZF.makeUrl('/File/KindEditor?action=upload&dirname=' + uploadDirName),
                fileManagerJson: bZF.makeUrl('/File/KindEditor?action=manage&dirname=' + uploadDirName),
                extraFileUploadParams: {
                    bzfshop_auth_cookie_key: $.cookie(WEB_COOKIE_AUTH_KEY)
                }
            });
            editor.loadPlugin('image', function () {
                editor.plugin.imageDialog({
                    imageUrl: $('#goods_edit_promote_upload_360tuan_image').attr('src'),
                    clickFn: function (url, title, width, height, border, align) {
                        if (callback) {
                            callback(clickObject, url, title, width, height, border, align);
                        }
                        editor.hideDialog();
                    }
                });
            });
        });
    };

    /**
     * 上传商品推广图片，由于每个商品都有一个或者多个，所以图片量巨大
     *
     * @param clickSelector
     * @param callback
     */
    bZF.uploadGoodsPromoteImage = function (clickSelector, callback) {
        bZF.uploadImage(clickSelector, callback, 'image_goods_promote');
    };

    /**
     * 上传广告图片
     *
     * @param clickSelector
     * @param callback
     */
    bZF.uploadAdvImage = function (clickSelector, callback) {
        bZF.uploadImage(clickSelector, callback, 'image_other');
    };

    /** ------------------------------------- /后台系统通用的代码 -----------------------------------------**/


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

    /************ goods_edit_edit.tpl 商品编辑页面，商品详情编辑框的创建 ****************/

    /************ 注意，由于上传采用了 swfupload 插件，我们需要做 post 认证，否则无法上传“bzfshop_auth_cookie_key” *****/
    KindEditor.create('#goods_edit_goods_desc_textarea', {
        filterMode: true,
        themeType: 'default',
        cssData: "body {font-family: '微软雅黑', 'Microsoft Yahei', '宋体', 'songti', STHeiti, Helmet, Freesans, sans-serif;font-size: 15px; }",
        uploadJson: bZF.makeUrl('/File/KindEditor?action=upload'), // '/File/Upload'
        fileManagerJson: bZF.makeUrl('/File/KindEditor?action=manage'),
        extraFileUploadParams: {
            bzfshop_auth_cookie_key: $.cookie(WEB_COOKIE_AUTH_KEY)
        },
        formatUploadUrl: false,
        allowFileManager: true,
        width: $('#goods_edit_goods_desc_textarea').outerWidth(false)
    });

    /******************* article_article_edit.tpl 网站文章内容编辑 ******************/
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

    /*********** goods_edit_gallery.tpl  商品编辑页面，商品相册批量上传图片 ***********/
    $('#goods_edit_gallery_upload_image_batch').click(function () {
        var editor = KindEditor.editor({
            allowFileManager: true,
            formatUploadUrl: false,
            uploadJson: bZF.makeUrl('/Goods/Edit/Gallery/Upload'),
            extraFileUploadParams: {
                bzfshop_auth_cookie_key: $.cookie(WEB_COOKIE_AUTH_KEY),
                goods_id: $('#goods_edit_gallery_upload_image_batch_goodsid').val()
            }
        });
        editor.loadPlugin('multiimage', function () {
            editor.plugin.multiImageDialog({
                clickFn: function (urlList) {
                    //刷新整个页面
                    document.location.reload();
                    editor.hideDialog();
                }
            });
        });
    });

    /*********** goods_edit_promote.tpl  商品推广渠道编辑页面，360团购导航商品图片 ***********/
    bZF.uploadGoodsPromoteImage('#goods_edit_promote_upload_360tuan_image_button',
        function (clickObject, url, title, width, height, border, align) {
            $('#goods_edit_promote_upload_360tuan_image').attr('src', url);
            $('#goods_edit_promote_upload_360tuan_image_input').val(url);
        });

    /**
     *  order_excel.tpl    批量下载订单页面，美化上传文件按钮
     */
    if ($('#order_goods_excel_upload_file_input').size() > 0) {
        SI.Files.stylizeById('order_goods_excel_upload_file_input');
    }


    /**
     * order_settle.tpl
     *
     * 订单结算页面，根据用户选择的结算时间段取得这个时间段里面有销售的供货商
     */
    bZF.order_settle_supplier_select = function () {
        var pay_time_start = $('#order_settle_pay_time_start').val();
        if (!pay_time_start) {
            bZF.showMessage('付款开始时间不能为空');
            return;
        }

        var pay_time_end = $('#order_settle_pay_time_end').val();
        if (!pay_time_end) {
            bZF.showMessage('付款结束时间不能为空');
            return;
        }

        var callUrl = bZF.makeUrl('/Ajax/Supplier/ListOrderGoodsSupplierIdName?pay_time_start=' +
            encodeURI(pay_time_start) + '&pay_time_end=' + encodeURI(pay_time_end) + '&supplier_for_settle=true');

        // ajax  调用
        bZF.ajaxCallGet(callUrl, function (data) {
            if (!data) {
                bZF.showMessage('没有需要结算的供货商');
                return;
            }

            supplierArray = data;
            // 设置 360tuan_cateogry_1 的数据
            var optionHtml = '<option value=""></option>';
            $.each(supplierArray, function (index, elem) {
                optionHtml += '<option value="' + elem.suppliers_id + '">' + elem.suppliers_name + '</option>';
            });
            $('#order_settle_supplier_select').html(optionHtml);
            //重新设置一次初始值
            $('#order_settle_supplier_select').select2('val', null);
            bZF.showMessage('取供货商列表成功');
        });
    };


    /**
     * order_excel.tpl
     *
     * 订单批量下载页面，根据用户选择的时间段取得这个时间段里面有销售的供货商
     */
    bZF.order_excel_supplier_select = function () {
        var pay_time_start = $('#order_excel_pay_time_start').val();
        var pay_time_end = $('#order_excel_pay_time_end').val();
        var extra_refund_time_start = $('#order_excel_extra_refund_time_start').val();
        var extra_refund_time_end = $('#order_excel_extra_refund_time_end').val();

        var callUrl = bZF.makeUrl('/Ajax/Supplier/ListOrderGoodsSupplierIdName?pay_time_start=' + encodeURI(pay_time_start)
            + '&pay_time_end=' + encodeURI(pay_time_end) + '&extra_refund_time_start=' + encodeURI(extra_refund_time_start)
            + '&extra_refund_time_end=' + encodeURI(extra_refund_time_end));

        // ajax  调用
        bZF.ajaxCallGet(callUrl, function (data) {
            if (!data) {
                bZF.showMessage('没有供货商');
                return;
            }
            var supplierArray = data;
            // 设置 360tuan_cateogry_1 的数据
            var optionHtml = '<option value=""></option>';
            $.each(supplierArray, function (index, elem) {
                optionHtml += '<option value="' + elem.suppliers_id + '">' + elem.suppliers_name + '</option>';
            });
            $('#order_settle_supplier_select').html(optionHtml);
            //重新设置一次初始值
            $('#order_settle_supplier_select').select2('val', null);
            bZF.showMessage('取供货商列表成功');
        });
    };

    /**
     *  order_settle.tpl
     *
     * 订单结算页面，用于计算结算金额
     */
    bZF.order_settle_calculate = function () {

        var orderSettleItemSize = $('#order_settle_item_size').val();
        if (!orderSettleItemSize || orderSettleItemSize <= 0) {
            return; // 没有需要结算的订单
        }

        var totalItemSelectCount = 0;
        var totalGoodsPrice = 0;
        var totalShippingFee = 0;
        var totalRefund = 0;

        // 遍历所有选择的订单
        for (var orderSettleItemIndex = 0; orderSettleItemIndex < orderSettleItemSize; orderSettleItemIndex++) {
            if (!$('#order_settle_item_' + orderSettleItemIndex + '_checkbox').attr('checked')) {
                continue; // 没有选 checkbox，不需要计算
            }

            // 计算
            totalItemSelectCount++;
            totalGoodsPrice += parseFloat($('#order_settle_item_' + orderSettleItemIndex + '_goods_price').text());
            totalShippingFee += parseFloat($('#order_settle_item_' + orderSettleItemIndex + '_shipping_fee').text());
            totalRefund += parseFloat($('#order_settle_item_' + orderSettleItemIndex + '_refund').text());
        }

        // 显示计算的结果
        $('#order_settle_order_goods_count').text(totalItemSelectCount);
        $('#order_settle_total_goods_price').text(totalGoodsPrice.toFixed(2));
        $('#order_settle_total_shipping_fee').text(totalShippingFee.toFixed(2));
        $('#order_settle_total_refund').text(totalRefund.toFixed(2));
        $('#order_settle_total_settle_money').text((totalGoodsPrice + totalShippingFee - totalRefund).toFixed(2));
    };
    //页面启动加载的时候执行一次
    bZF.order_settle_calculate();


    /***********  order_settle_listsettle.tpl  订单结算页面，结算详情 ***********/
    bZF.Order_Settle_ajaxDetail = function (settle_id) {
        var ajaxCallUrl = bZF.makeUrl('/Order/Settle/ajaxDetail');
        $('#order_settle_listsettle_modal_update').load(ajaxCallUrl + '?settle_id=' + settle_id, function () {
            // html enhance
            bZF.enhanceHtml($('#order_settle_listsettle_modal_update'));
            $('#order_settle_listsettle_modal_update').modal({dynamic: true});
        });
    };

    /**
     * user_login.tpl   user_register.tpl
     *
     * 验证码图片显示，当输入框第一次获得焦点的时候取得验证码
     * */
    $("#captcha_input").one('focus', function () {
        bZF.loadCaptchaImage("#captcha_image");
    });

    /**
     * account_admin_privilege.tpl 管理员权限页面，根据用户已有的权限设置对应的勾选项
     * */
    (function ($) {

        var actionListStr = $('#account_admin_privilege_action_list').val();
        if (!actionListStr) {
            // 没有权限设置，返回
            return;
        }

        // 一头一尾加上 ',' 好做字符串的比较
        actionListStr = ',' + actionListStr + ',';

        // 对每个 checkbox 检查，然后设置值
        $('div.admin-privilege input').each(function (index, elem) {
            if (actionListStr.indexOf(',' + $(elem).val() + ',') == -1) {
                // 没有设置这个权限
                return;
            }

            //有这个权限，让勾选勾上
            $(elem).attr('checked', 'checked');
        });

    })(jQuery);

    /**
     * account_admin_privilege.tpl
     *
     * 管理员权限页面，如果勾选了权限，我们让字体加粗
     *
     * */
    $('div.admin-privilege').each(function (index, elem) {

        var actionFunc = function (divElem) {
            var checkBoxAttrChecked = $('input', divElem).attr('checked');
            if (checkBoxAttrChecked) {
                $('span', divElem).css('font-weight', 'bold');
                $('span', divElem).css('color', 'blue');
            } else {
                $('span', divElem).css('font-weight', 'normal');
                $('span', divElem).css('color', 'black');
            }
        };

        // 第一次执行，检查选中状态
        actionFunc(elem);

        // 用户选择之后改变状态
        $('input', elem).on('click', function () {
            actionFunc(this.parentNode);
        });
    });

    /******************** account_admin_privilege.tpl 设置用户权限页面，查看角色权限 *********************/
    $('#account_admin_privilege_view_role_privilege_button').on('click', function () {
        var roleId = parseInt($('#account_admin_privilege_role_select').find('option:selected').val());
        roleId = isNaN(roleId) ? 0 : roleId;

        if (roleId <= 0) {
            bZF.showMessage('请先选择正确的角色');
            return;
        }

        var callUrl = bZF.makeUrl('/Account/Role/Privilege?meta_id=' + roleId);
        window.open(encodeURI(callUrl));
    });


    /************************** order_excel.tpl  订单下载 *****************************/
    $('#stat_order_refer_download_button').on('click', function () {

        var add_time_start = $('#stat_order_refer_add_time_start').val();
        var add_time_end = $('#stat_order_refer_add_time_end').val();
        var pay_time_start = $('#stat_order_refer_pay_time_start').val();
        var pay_time_end = $('#stat_order_refer_pay_time_end').val();
        var utm_source = $('#stat_order_refer_utm_source').find('option:selected').val();
        var utm_medium = $('#stat_order_refer_utm_medium').find('option:selected').val();
        var login_type = $('#stat_order_refer_login_type').find('option:selected').val();

        // 参数检查
        if (!add_time_start && !add_time_end && !pay_time_start && !pay_time_end) {
            bZF.showMessage('必须提供最少一个查询时间');
            return;
        }

        // 构造调用链接
        var callUrl = bZF.makeUrl('/Stat/Order/Refer/Download'
            + '?add_time_start=' + add_time_start
            + '&add_time_end=' + add_time_end
            + '&pay_time_start=' + pay_time_start
            + '&pay_time_end=' + pay_time_end
            + '&utm_source=' + utm_source
            + '&utm_medium=' + utm_medium
            + '&login_type=' + login_type);

        window.open(encodeURI(callUrl));
    });


    /********************** goods_edit_linkgoods.tpl 根据条件筛选商品列表 *************************/
    $('#goods_edit_linkgoods_filter_goods_button').on('click', function () {
        // 根据用户选择的条件筛选商品
        var goods_id = $('#goods_edit_linkgoods_goods_id').val();
        var goods_name = $('#goods_edit_linkgoods_goods_name').val();
        var is_on_sale = $('#goods_edit_linkgoods_is_on_sale').find('option:selected').val();
        var suppliers_id = $('#goods_edit_linkgoods_suppliers_id').find('option:selected').val();
        var cat_id = $('#goods_edit_linkgoods_cat_id').find('option:selected').val();

        // 构造调用链接
        var callUrl = bZF.makeUrl('/Ajax/Goods/Search'
            + '?goods_id=' + goods_id
            + '&goods_name=' + encodeURI(goods_name)
            + '&is_on_sale=' + is_on_sale
            + '&suppliers_id=' + suppliers_id
            + '&cat_id=' + cat_id);

        // ajax  调用
        bZF.ajaxCallGet(callUrl, function (data) {
            if (!data) {
                bZF.showMessage('没有商品列表');
                return;
            }

            var goodsArray = data;
            // 设置 goods_edit_linkgoods_filter_goods_list 的数据
            var optionHtml = '';
            $.each(goodsArray, function (index, elem) {
                optionHtml += '<option value="' + elem.goods_id + '">(' + elem.goods_id + ')'
                    + elem.goods_name + '</option>';
            });
            $('#goods_edit_linkgoods_filter_goods_list').html(optionHtml);
        });
    });

    /**
     * goods_edit_linkgoods.tpl
     *
     *  取得商品的关联商品并且展示
     *
     * @param goods_id
     */
    bZF.goods_edit_linkgoods_ajaxlistlinkgoods = function (goods_id) {
        // 构造调用链接
        var callUrl = bZF.makeUrl('/Goods/Edit/LinkGoods/ajaxListLinkGoods'
            + '?goods_id=' + goods_id);

        // ajax  调用
        bZF.ajaxCallGet(callUrl, function (data) {
            if (!data) {
                $('#goods_edit_linkgoods_link_goods_list').html('');
                return;
            }

            var goodsArray = data;
            // 设置 goods_edit_linkgoods_filter_goods_list 的数据
            var optionHtml = '';
            $.each(goodsArray, function (index, elem) {
                optionHtml += '<option value="' + elem.link_id + '">(' + elem.goods_id + ')' + elem.goods_name + '</option>';
            });
            $('#goods_edit_linkgoods_link_goods_list').html(optionHtml);
        });
    };

    // 页面加载的时候自动列出关联商品列表
    if ($('#goods_edit_linkgoods_link_goods_list').size() > 0) {
        bZF.goods_edit_linkgoods_ajaxlistlinkgoods($('#goods_edit_linkgoods_current_goods_id').val());
    }

    /**
     * goods_edit_linkgoods.tpl
     *
     *  取得商品被谁关联
     *
     * @param link_goods_id
     */
    bZF.goods_edit_linkgoods_ajaxlistlinkbygoods = function (link_goods_id) {
        // 构造调用链接
        var callUrl = bZF.makeUrl('/Goods/Edit/LinkGoods/ajaxListLinkByGoods'
            + '?link_goods_id=' + link_goods_id);

        // ajax  调用
        bZF.ajaxCallGet(callUrl, function (data) {
            if (!data) {
                $('#goods_edit_linkgoods_link_by_goods_list').html('');
                return;
            }

            var goodsArray = data;
            // 设置 goods_edit_linkgoods_filter_goods_list 的数据
            var optionHtml = '';
            $.each(goodsArray, function (index, elem) {
                optionHtml += '<option value="' + elem.link_id + '">(' + elem.goods_id + ')' + elem.goods_name + '</option>';
            });
            $('#goods_edit_linkgoods_link_by_goods_list').html(optionHtml);
        });
    };

    // 页面加载的时候自动列出被关联商品列表
    if ($('#goods_edit_linkgoods_link_by_goods_list').size() > 0) {
        bZF.goods_edit_linkgoods_ajaxlistlinkbygoods($('#goods_edit_linkgoods_current_goods_id').val());
    }

    /********* goods_edit_linkgoods.tpl 取消商品关联 *********/
    $('#goods_edit_linkgoods_remove_link_goods_button').on('click', function () {

        // 对每个选中的商品依次处理
        var totalCount = $('#goods_edit_linkgoods_link_goods_list').find('option:selected').size();

        $('#goods_edit_linkgoods_link_goods_list').find('option:selected').each(function (index, elem) {

            var linkId = parseInt($(elem).val());

            if (isNaN(linkId)) {
                bZF.showMessage('请先选择一个已经关联的商品');
                return;
            }

            var callUrl = bZF.makeUrl('/Goods/Edit/LinkGoods/ajaxRemoveLink'
                + '?link_id=' + linkId);

            // ajax  调用
            bZF.ajaxCallGet(callUrl, function (data) {
                // 最后一个商品了
                if (index == totalCount - 1) {
                    // 刷新商品关联列表
                    bZF.goods_edit_linkgoods_ajaxlistlinkgoods($('#goods_edit_linkgoods_current_goods_id').val());
                }
            });
        });

    });

    /********* goods_edit_linkgoods.tpl 取消商品 "被" 关联 *********/
    $('#goods_edit_linkgoods_remove_link_by_goods_button').on('click', function () {

        // 对每个选中的商品依次处理
        var totalCount = $('#goods_edit_linkgoods_link_by_goods_list').find('option:selected').size();

        $('#goods_edit_linkgoods_link_by_goods_list').find('option:selected').each(function (index, elem) {

            var linkId = parseInt($(elem).val());

            if (isNaN(linkId)) {
                bZF.showMessage('请先选择一个已经关联的商品');
                return;
            }

            var callUrl = bZF.makeUrl('/Goods/Edit/LinkGoods/ajaxRemoveLink'
                + '?link_id=' + linkId);

            // ajax  调用
            bZF.ajaxCallGet(callUrl, function (data) {
                // 最后一个商品了
                if (index == totalCount - 1) {
                    // 刷新商品关联列表
                    bZF.goods_edit_linkgoods_ajaxlistlinkbygoods($('#goods_edit_linkgoods_current_goods_id').val());
                }
            });

        });

    });

    /********* goods_edit_linkgoods.tpl 添加商品关联 *********/
    $('#goods_edit_linkgoods_add_link_goods_button').on('click', function () {

        // 对每个选中的商品依次处理
        var totalCount = $('#goods_edit_linkgoods_filter_goods_list').find('option:selected').size();
        var currentGoodsId = $('#goods_edit_linkgoods_current_goods_id').val();

        $('#goods_edit_linkgoods_filter_goods_list').find('option:selected').each(function (index, elem) {

            var linkGoodsId = parseInt($(elem).val());

            if (isNaN(linkGoodsId)) {
                bZF.showMessage('请先选择一个商品');
                return;
            }

            var callUrl = bZF.makeUrl('/Goods/Edit/LinkGoods/ajaxAddLink'
                + '?goods_id=' + currentGoodsId + '&link_goods_id=' + linkGoodsId);

            // ajax 调用
            bZF.ajaxCallGet(callUrl, function (data) {
                // 最后一个商品了
                if (index == totalCount - 1) {
                    // 刷新商品关联列表
                    bZF.goods_edit_linkgoods_ajaxlistlinkgoods(currentGoodsId);
                }
            });
        });

    });

    /*********************************** goods_edit_spec.tpl 编辑商品的 规格 ********************************************/

        // 添加一个 control group
    bZF.goods_edit_spec_add_control_group = function (elem) {
        // 取父节点 control group
        var controlGroupNode = elem.parentNode.parentNode;
        var cloneNode = controlGroupNode.cloneNode(true);
        // 删除多余的 help-block
        $('div.help-block', cloneNode).remove();
        // 把按钮替换成删除按钮
        $('button', cloneNode).remove();
        $('div.controls', cloneNode).append($('<button type="button" class="btn btn-mini btn-danger"  onclick="bZF.goods_edit_spec_remove_control_group(this);"><i class="icon-remove"></i></button>&nbsp;<button onclick="bZF.moveNodePrev(this.parentNode.parentNode);return false;" class="btn btn-mini btn-info" type="button"><i class="icon-arrow-up"></i></button>&nbsp;<button onclick="bZF.moveNodeNext(this.parentNode.parentNode);return false;"  class="btn btn-mini btn-info" type="button"><i class="icon-arrow-down"></i></button>'));
        // 插入节点
        $(cloneNode).insertAfter(controlGroupNode);
        // 做 html enhance
        //bZF.enhanceHtml(cloneNode);
    };

    // 删除一个 control group
    bZF.goods_edit_spec_remove_control_group = function (elem) {
        // 取父节点 control group
        var controlGroupNode = elem.parentNode.parentNode;
        $(controlGroupNode).remove();
    };

    // 打开对话框，选择规格关联的商品头图
    bZF.goods_edit_spec_select_image_modal = function (elem) {
        var $dialog = jQuery('#goods_edit_spec_select_goods_image_modal');
        $dialog.data('callObject', elem);
        $dialog.modal();
    };

    // 确认选择了某个头图
    bZF.goods_edit_spec_select_image_confirm = function () {
        var $option = $('#goods_edit_spec_select_goods_image_modal select').find('option:selected');
        var imgId = parseInt($option.val());
        var imgUrl = $option.text();
        imgId = isNaN(imgId) ? 0 : imgId;

        // 无效图片
        if (imgId <= 0) {
            return;
        }

        // 取得绑定的 callObject
        var $dialog = jQuery('#goods_edit_spec_select_goods_image_modal');
        var callObject = $dialog.data('callObject');
        if (!callObject) {
            console.log('goods_edit_spec_select_goods_image_modal has no callObject');
            return;
        }

        // 设置用户的选择
        $('img', callObject).attr('src', imgUrl);
        $('input[name="imgIdArray[]"]', callObject).val(imgId);

        // 关闭对话框
        $dialog.modal('hide');
    };

    /************* goods_category.tpl 页面，商品分类树形结构 *************/
    $("#bzf_goods_category_tree_table").treetable({ expandable: true, clickableNodeNames: true, initialState: 'expanded' });

    bZF.show_goods_category_edit_modal = function (categoryBlock) {
        if (categoryBlock) {
            // 编辑,给对话框赋值
            $('#goods_category_edit_modal input[name="meta_id"]').val($('input[name="meta_id"]', categoryBlock).val());
            $('#goods_category_edit_modal input[name="meta_name"]').val($('input[name="meta_name"]', categoryBlock).val());
            $('#goods_category_edit_modal input[name="meta_sort_order"]').val($('input[name="meta_sort_order"]', categoryBlock).val());
            $('#goods_category_edit_modal select[name="meta_status"]').select2('val', $('input[name="meta_status"]', categoryBlock).val());
            $('#goods_category_edit_modal select[name="parent_meta_id"]').select2('val', $('input[name="parent_meta_id"]', categoryBlock).val());
        } else {
            // 新建
            $('#goods_category_edit_modal input[name="meta_id"]').val(0);
            $('#goods_category_edit_modal input[name="meta_name"]').val('');
            $('#goods_category_edit_modal input[name="meta_sort_order"]').val(0);
            $('#goods_category_edit_modal select[name="meta_status"]').select2('val', 1);
            $('#goods_category_edit_modal select[name="parent_meta_id"]').select2('val', 0);
        }
        // 显示对话框
        $('#goods_category_edit_modal').modal({dynamic: true});
    };

    bZF.show_goods_category_transfer_goods_modal = function (categoryBlock) {
        $('#goods_category_transfer_goods_modal input[name="meta_id"]').val($('input[name="meta_id"]', categoryBlock).val());
        // 显示对话框
        $('#goods_category_transfer_goods_modal').modal({dynamic: true});
    };

    /***************** ajax 显示用户详细信息 **************************/
    bZF.Account_User_ajaxDetail = function (user_id) {
        var ajaxCallUrl = bZF.makeUrl('/Account/User/ajaxDetail');
        $('#user_detail_dialog').load(ajaxCallUrl + '?user_id=' + user_id, function () {
            bZF.enhanceHtml($('#user_detail_dialog'));
            $('#user_detail_dialog').modal({dynamic: true});
        });
    };

    /****************** community_article.tpl 页面显示 rss 文章 ********************/
    $('#bzf_community_article_rss_panel').rssfeed('http://www.bzfshop.net/feed', {
        limit: 20,
        linkcontent: true,
        linktarget: '_blank'
    });

})(window, jQuery));
