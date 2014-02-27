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
    if (!window.bZF) {
        window.bZF = bZF;
    } else {
        bZF = window.bZF;
    }

    bZF.isWindowUnload = false;
    $(window).bind('beforeunload', function () {
        bZF.isWindowUnload = true;
    });

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
            error: function (jqXHR, textStatus, errorThrown) {

                if (failFunc) {
                    failFunc(jqXHR, textStatus, errorThrown);
                    return;
                }

                if (bZF.isWindowUnload) {
                    // 不是错误
                    return;
                }

                bZF.showMessage('网络错误[' + textStatus + ']');
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
            error: function (jqXHR, textStatus, errorThrown) {

                if (failFunc) {
                    failFunc(jqXHR, textStatus, errorThrown);
                    return;
                }

                if (bZF.isWindowUnload) {
                    // 不是错误
                    return;
                }

                bZF.showMessage('网络错误[' + textStatus + ']');
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
        $(itemId).html('<a href="#" onclick="bZF.loadCaptchaImage(\'' + itemId + '\')"><img style="width:150px;height:50px;" '
            + 'width="150" height="50" src="' + bZF.makeUrl('/Image/Captcha') + '?rand=' + time + '" /></a>');
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

    /************ 为 select2 控件加载 ajax 调用并且取得数据 **************/
    bZF.select2AjaxLoad = function ($elem) {

        var callUrl = $elem.attr('data-ajaxCallUrl');
        var valueKey = $elem.attr('data-option-value-key');
        var textKey = $elem.attr('data-option-text-key');

        bZF.ajaxCallGet(callUrl, function (data) {
            // 没有数据
            if (!data) {
                // 初始化，并且设置初始值
                $elem.select2({
                    minimumResultsForSearch: 10, // 10 个选项以上才执行搜索功能
                    allowClear: true
                }).select2('val', $elem.attr('data-initValue'));
                return;
            }

            // 清空 select2 里面已有的 html内容
            $elem.html('');

            // 有 placeholder 需要在一开始加入一个空的 option
            if ($elem.attr('data-placeholder')) {
                $elem.append('<option value=""></option>');
            }

            // 加入各个数据
            $.each(data, function (index, dataItem) {
                $elem.append($('<option value="' + dataItem[valueKey] + '">' + dataItem[textKey] + '</option>'));
            });

            // 获取初始值
            var dataInitValue = $elem.attr('data-initValue');

            // 如果是多项选择，必然是 tag 显示
            if (dataInitValue && '' != $.trim(dataInitValue) && $elem.attr('multiple')) {
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

            if ($elem.attr('data-option-value-image')) {
                formatResultFunc = function (item) {
                    return '<img src="' + item.text + '"/>';
                };
                formatSelectionFunc = function (item) {
                    return '图片 ' + item.id;
                };
            }

            // 初始化 select2 控件
            $elem.select2({
                minimumResultsForSearch: 10, // 10 个选项以上才执行搜索功能
                allowClear: true,
                formatResult: formatResultFunc,
                formatSelection: formatSelectionFunc
            });

            // 设置控件的初始值
            if (dataInitValue && '' != $.trim(dataInitValue)) {
                $elem.select2('val', dataInitValue);
            }

        });
    }

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
            var $elem = $(elem);

            // 自动做 ajax 调用加载数据
            if ($elem.attr('data-ajaxCallUrl')) {

                bZF.select2AjaxLoad($elem);

            } else {
                // 初始化，并且设置初始值
                $elem.select2({
                    minimumResultsForSearch: 10, // 10 个选项以上才执行搜索功能
                    allowClear: true
                }).select2('val', $elem.attr('data-initValue'));
            }

        });

        // input 用于 select2 的标签自动生成
        $('input.select2-simple', node).each(function (index, elem) {
            $(elem).select2({
                tags: []
            });
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
            bZF.isKindEditorThemeLoad = true;
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


    // 有些时候我们只需要使用 KindEditor 的某个插件而已，这种情况下我们需要激发 KindEditor 加载 Theme，否则无法正常工作
    bZF.loadKindEditorTheme = function () {
        if (bZF.isKindEditorThemeLoad) {
            return;
        }
        /** 激发 KindEditor 加载主题，否则 KindEditor 的插件无法正常使用 **/
        KindEditor.create();
        bZF.isKindEditorThemeLoad = true;
    };

    /**
     * 点击上传图片
     *
     * @param clickSelector
     * @param callback
     * @param dirName
     */
    bZF.uploadImage = function (clickSelector, callback, dirName) {

        bZF.loadKindEditorTheme();

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

    /***************** ajax 显示用户详细信息 **************************/
    bZF.Account_User_ajaxDetail = function (user_id) {
        var ajaxCallUrl = bZF.makeUrl('/Account/User/ajaxDetail');
        $('#user_detail_dialog').load(ajaxCallUrl + '?user_id=' + user_id, function () {
            bZF.enhanceHtml($('#user_detail_dialog'));
            $('#user_detail_dialog').modal({dynamic: true});
        });
    };

})(window, jQuery));
