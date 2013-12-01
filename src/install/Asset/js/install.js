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

        /** 开启 tooltip 功能显示 ***/
        $('*[rel="tooltip"]', node).tooltip({delay: 200});

        /** ***** 开启 popover 的效果 ******* */
        $("a[ref='popover']", node).popover();

    };

    // 对整个 document 做一次 enhance
    bZF.enhanceHtml($('body'));

    /** ------------------------------------- /后台系统通用的代码 -----------------------------------------**/


})(window, jQuery));
