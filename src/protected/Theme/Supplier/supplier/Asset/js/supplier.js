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

            // 初始化，并且设置初始值
            $(elem).select2({
                minimumResultsForSearch: 10, // 10 个选项以上才执行搜索功能
                allowClear: true
            }).select2('val', $(elem).attr('data-initValue'));

            // 自动做 ajax 调用加载数据
            if ($(elem).attr('data-ajaxCallUrl')) {
                var callUrl = $(elem).attr('data-ajaxCallUrl');
                var valueKey = $(elem).attr('data-option-value-key');
                var textKey = $(elem).attr('data-option-text-key');

                bZF.ajaxCallGet(callUrl, function (data) {
                    // 没有数据
                    if (!data) {
                        return;
                    }
                    // 加入各个数据
                    $.each(data, function (index, dataItem) {
                        $(elem).append($('<option value="' + dataItem[valueKey] + '">' + dataItem[textKey] + '</option>'));
                    });

                    // 设置初始值
                    $(elem).select2('val', $(elem).attr('data-initValue'));
                });
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
                    'insertunorderedlist', 'link']
            });
        });

        /********* 检查表单是否发生过修改，如果修改过并且用户要离开我们需要提醒用户是否保存 **********/
        $('form.form-dirty-check', node).areYouSure({
            'message': '内容发生修改，你确定不保存就离开？'
        });

        /** 开启 tooltip 功能显示 ***/
        $('*[rel="tooltip"]', node).tooltip();

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

    };

    // 对整个 document 做一次 enhance
    bZF.enhanceHtml(document);

    /** ------------------------------------- /后台系统通用的代码 -----------------------------------------**/

    /******* 订单列表页面显示订单详情数据的调用 ***********/
    bZF.Order_ListOrder_Detail = function (rec_id) {
        var ajaxCallUrl = bZF.makeUrl('/Order/Goods/ajaxDetail');
        $('#order_detail_dialog').load(ajaxCallUrl + '?rec_id=' + rec_id, function () {
            bZF.enhanceHtml($('#order_detail_dialog'));
            $('#order_detail_dialog').modal({dynamic: true});
        });
    };

    /**************  订单详情页面设置快递信息 ***************/
    bZF.Order_ListOrder_ajaxUpdate = function () {
        var ajaxCallUrl = bZF.makeUrl('/Order/Goods/ajaxUpdate');
        var rec_id = $('#order_detail_rec_id').val();
        var shipping_id = $('#order_detail_shipping_select').find('option:selected').val();
        var shipping_no = $('#order_detail_shipping_no').val();

        bZF.ajaxCallPost(ajaxCallUrl,
            {rec_id: rec_id, shipping_id: shipping_id, shipping_no: shipping_no},
            function (data) {
                bZF.showMessage('快递信息设置成功');
            });
    };

})(window, jQuery));