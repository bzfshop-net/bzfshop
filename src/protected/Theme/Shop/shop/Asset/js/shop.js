/**
 * 棒主妇商城 JavaScript 程序
 *
 * @author QiangYu
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

    // raty 插件的全局设置
    $.fn.raty.defaults.space = false;
    $.fn.raty.defaults.hints = ['1星', '2星', '3星', '4星', '5星'];
    $.fn.raty.defaults.path = WEB_ROOT_BASE_RES + 'bootstrap-custom/plugin/raty/img/';

    /** ***** 建立自己的命名空间 ******** */
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

    bZF.themeShop = {};

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

    /** ------------------------------------- /系统通用的代码 -----------------------------------------**/

    if ($.pnotify) {
        // pnotify 不要显示历史记录
        $.pnotify.defaults.history = false;
        // 缺省显示 3 秒就退出
        $.pnotify.defaults.delay = 3000;
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

    /**
     * 千分位金额转化为显示格式
     *
     * @param money
     */
    bZF.money_to_display = function (money) {
        money = money / 1000;
        return money.toFixed(2);
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
     * 取得当前 URL 的参数
     *
     * @param param
     * @returns {*}
     */
    bZF.getCurrentUrlParam = function (param) {
        var queryParamUrl = window.location.href;
        if (-1 !== queryParamUrl.indexOf('.html')) {
            var lastSlashPos = queryParamUrl.lastIndexOf('/');
            var urlPrefix = queryParamUrl.substr(0, lastSlashPos);
            var urlParam = queryParamUrl.substr(lastSlashPos + 1);
            urlParam = urlParam.replace('.html', '');
            var urlParamArray = urlParam.match(/[~]?([^~]+)-([^~]*)/g);
            var paramArray = [];
            $.each(urlParamArray, function (index, param) {
                if ('~' == param.charAt(0)) {
                    param = param.substr(1);
                }
                var separatorPos = param.indexOf('-');
                paramArray.push(param.substr(0, separatorPos) + '=' + param.substr(separatorPos + 1));
            });
            queryParamUrl = urlPrefix + '?' + paramArray.join('&');
        }
        return $.url(queryParamUrl).param(param);
    };

    /**
     * 加载验证码图片
     * @param itemId
     */
    bZF.loadCaptchaImage = function (itemId) {
        var time = new Date().getTime();
        //取得验证码图片
        $(itemId).html('<a href="#" onclick="return bZF.loadCaptchaImage(\'' + itemId + '\')"><img style="width:150px;height:50px;" '
            + 'width="150" height="50" src="' + bZF.makeUrl('/Image/Captcha') + '?rand=' + time + '" /></a>');
        return false;
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

                    // 获取初始值
                    var dataInitValue = $(elem).attr('data-initValue');

                    // 没有数据
                    if (!data) {
                        // 设置初始值
                        setTimeout(function () {
                            if (dataInitValue) {
                                $(elem).val(dataInitValue);
                            }
                        }, 100);
                        return;
                    }

                    // 加入各个数据
                    $.each(data, function (index, dataItem) {
                        $(elem).append($('<option value="' + dataItem[valueKey] + '">' + dataItem[textKey] + '</option>'));
                    });

                    setTimeout(function () {
                        if (dataInitValue) {
                            $(elem).val(dataInitValue);
                        }
                    }, 100);

                });
            } else {
                // 获取初始值
                var dataInitValue = $(elem).attr('data-initValue');
                setTimeout(function () {
                    if (dataInitValue) {
                        $(elem).val(dataInitValue);
                    }
                }, 100);
            }

        });

        /** 开启 tooltip 功能显示 ***/
        $('*[rel="tooltip"]', node).tooltip();

        /** ***** 开启 popover 的效果 ******* */
        $("a[ref='popover']", node).popover();

        /**
         * **** 开启图片懒加载，所有 <img class="lazyload" src="placehold.jpg" data-original="pic.jpg" /> ******
         */
        $("img.lazyload", node).show().lazyload({
            effect: "fadeIn"
        });

        /**
         * tab 切换的时候，如果里面有 JS 动态生成的内容，比如 jcarousel 滑动，或者 lazyload ，可能
         * 会无法显示，激发一个 resize 事件可以解决这个问题
         */
        $('a[data-toggle="tab"]', node).on('shown', function (e) {
            var newPanelId = $(e.target, node).attr('href');
            $(newPanelId, node).trigger('resize');
            $(newPanelId, node).trigger('scroll');
        })

        /**
         * 显示5星评价，只读
         * */
        $('.bzf_rate_star_readonly').each(function (index, elem) {
            var rateValue = parseInt($(elem).attr('rateValue'));
            rateValue = isNaN(rateValue) ? 0 : rateValue;
            $(elem).raty({
                readOnly: true,
                score: rateValue
            });
        });

        /**
         * 点评5星评价
         * */
        $('.bzf_rate_star').each(function (index, elem) {
            var rateValue = parseInt($(elem).attr('rateValue'));
            rateValue = isNaN(rateValue) ? 0 : rateValue;
            $(elem).raty({
                score: rateValue,
                click: function (score, evt) {
                    var targetSelector = $(elem).attr('targetInputSelector');
                    if (targetSelector) {
                        $(targetSelector).val(score);
                    }
                }
            });
        });
    };

    // 对整个 document 做一次 enhance
    bZF.enhanceHtml($('body'));

    /**
     * 这里设置各个页面的导航栏 “选中/未选中” 状态
     * window.bzf_set_nav_status 是一个数组，里面包含了一串用于设置导航栏状态的 javascript 方法
     * */
    (function ($) {
        if (window.bzf_set_nav_status) {
            $.each(window.bzf_set_nav_status, function (i, value) {
                value($);
            });
        }
    })(jQuery);


    /**
     * 启动 quake-slider 幻灯图片切换
     */
    $('.quake-slider').quake({
        thumbnails: true,
        animationSpeed: 500,
        pauseTime: 5000,
        applyEffectsRandomly: true,
        navPlacement: 'inside',
        navAlwaysVisible: false,
        captionOpacity: '0.3',
        rows: 5,
        cols: 12,
        captionOpacity: 0.3,
        captionOrientations: ['top'],
        effects: ['swirlFadeOutRotateFancy', 'spiral', 'diagonalShow', 'boxFadeIn', 'boxFadeOutOriginal', 'boxFadeOutOriginalRotate', 'diagonalFade', 'explode', 'barsUp', 'barsDown', 'chopDiagonal']
    });


    /**
     * 这里的 clientData 和服务器端的 clientData 有相同的实现，通过 clientData 的传递我们完全摆脱
     * 把 client 数据写到网页上的问题，从而可以支持我们实现网站的“真实静态化”
     *
     */
    bZF.clientData = {};
    bZF.clientData.clientDataChangeTimeStampKey = 'bzf_client_data_change_timestamp';
    bZF.clientData.clientDataStorageKey = 'bzf_client_data_storage';
    bZF.clientData.clientDataStorageTimestampKey = 'bzf_client_data_storage_timestamp';

    // 所有 clientData 的 key 定义
    bZF.clientData.KeyIsUserLogin = 'isUserLogin';
    bZF.clientData.KeyUserNameDisplay = 'userNameDisplay';

    /**
     * 同步客户端数据
     */
    bZF.clientData.syncClientData = function () {

        var clientDataChangeTimestamp = $.cookie(bZF.clientData.clientDataChangeTimeStampKey);
        if (!clientDataChangeTimestamp) {
            //清除本地存储
            $.jStorage.deleteKey(bZF.clientData.clientDataStorageKey);
            $.jStorage.deleteKey(bZF.clientData.clientDataStorageTimestampKey);
            return;
        }

        var clientData = bZF.clientData.getClientData();
        var storageTimestamp = $.jStorage.get(bZF.clientData.clientDataStorageTimestampKey);

        if (clientData && storageTimestamp == clientDataChangeTimestamp) {
            // 发送 ready 通知
            $(document).trigger('bzf_client_data_ready');
            return;
        }

        // 做 ajax 调用同步数据
        bZF.ajaxCallGet(bZF.makeUrl('/Ajax/ClientData'), function (data) {

            // 同步数据到 jStorage 中
            $.jStorage.set(bZF.clientData.clientDataStorageKey, data);
            $.jStorage.set(bZF.clientData.clientDataStorageTimestampKey, clientDataChangeTimestamp);

            // 发送 ready 通知
            $(document).trigger('bzf_client_data_ready');

        }, function () {
            // 调用失败也一样需要 发送 ready 通知
            $(document).trigger('bzf_client_data_ready');
            console.log('/Ajax/ClientData 调用失败');
        });
    };

    /**
     * 取得 clientData
     */
    bZF.clientData.getClientData = function () {
        return $.parseJSON($.jStorage.get(bZF.clientData.clientDataStorageKey));
    };

    /**
     * 取得 clientData 数据
     *
     * @param key
     * @returns {null}
     */
    bZF.clientData.getClientDataValue = function (key) {
        var clientData = bZF.clientData.getClientData();
        if (!clientData) {
            return null;
        }
        var value = null;
        $.each(clientData, function (dataKey, dataValue) {
            if (dataKey == key) {
                value = dataValue;
            }
        });
        return value;
    };

    /**
     * 打印整个 ClientData 数据，用于调试调用
     */
    bZF.clientData.dump = function () {
        var clientData = bZF.clientData.getClientData();
        console.log('最新Cookie时间' + $.cookie(bZF.clientData.clientDataChangeTimeStampKey));
        console.log('clientData同步时间：' + $.jStorage.get(bZF.clientData.clientDataStorageTimestampKey));
        console.log(clientData);
    };

    /** ------------------------------------- /系统通用的代码 -----------------------------------------**/

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
     * 定位商品分类菜单的位置
     */
    bZF.dock_bzf_header_dropdown_menu = function () {
        // 全部分类按钮不允许点击
        $('#bzf_header_dropdown_menu').click(function () {
            return false;
        });
        var posX = $('#bzf_header_dropdown_menu').offset().left;
        var posY = $('#bzf_header_dropdown_menu').offset().top - $('body').offset().top + $('#bzf_header_dropdown_menu').outerHeight();
        $('.navsort').css({position: 'absolute', top: posY, left: posX});
    };
    bZF.dock_bzf_header_dropdown_menu();

    /**
     * 加载商品分类列表
     */
    $('.navsort').load(bZF.makeUrl('/Ajax/Category') + ' .allsort', null, function () {

        $('#bzf_header_dropdown_menu').each(function () {

            $('.allsort .item').hoverForIE6({delay: 150});

            // 设置了总是显示 dropdown_menu
            if (bZF.always_show_bzf_header_dropdown_menu) {
                $('.allsort').addClass('allsorthover');
                return;
            }

            var timer1 = null, timer2 = null, flag = false;
            $('#bzf_header_dropdown_menu,.navsort').bind("mouseover",function () {
                if (flag) {
                    clearTimeout(timer2);
                } else {
                    if ('bzf_header_dropdown_menu' == $(this).attr('id')) {
                        timer1 = setTimeout(function () {
                            $('.allsort').addClass('allsorthover');
                            flag = true;
                        }, 200);
                    }
                }
            }).bind("mouseout", function () {
                if (flag) {
                    timer2 = setTimeout(function () {
                        $('.allsort').removeClass('allsorthover');
                        flag = false;
                    }, 200);
                } else {
                    clearTimeout(timer1);
                }
            })
        });

        // 动态调整 shop_index 的高度
        $('.bzf_shop_index_head_category_back_panel').css('height', $('.navsort .allsort .mc').height() + 'px');
    });

    /**
     * 页面右侧浮动面板，包括 回到顶部按钮之类
     *
     */
    $('#bzf_right_float_panel').dockToRight({
        startline: 0,
        right_offsetx: 0,
        right_offsety: 100,
        dockToObject: $('#main_body'),
        dockToObjectOffsetX: 5
    });

    $('#bzf_right_float_panel .bzf_right_float_panel_block').hover(function () {
        $(this).addClass('hover');
    }, function () {
        $(this).removeClass('hover');
    });

    /************** layout.tpl  显示购物车里面有多少商品 ******************/
    bZF.cart_goods_count = function () {
        bZF.ajaxCallGet(bZF.makeUrl('/Cart/Show/ajaxGoodsCount?' + new Date().getTime()), function (data) {
            // 更新 layout 上面的购物车商品数量显示
            if (!data) {
                data = 0;
            }
            $('#cart_goods_count').text(data);
        });
    };
    // 页面加载的时候调用一次
    bZF.cart_goods_count();

    /************** layout.tpl  悬浮在客服面板上，显示客服信息 ******************/
    $('#bzf_right_float_kefu_block').mouseenter(function (e) {

        var hoverObject = this;

        // 延迟 200ms 显示弹出框，防止闪动得太厉害
        bZF.themeShop.popoverShowTimer = setTimeout(function () {

            $(hoverObject).popover({
                content: $('.bzf_popover_html', hoverObject).html(),
                trigger: 'manual',
                html: true,
                placement: 'left',
                template: '<div class="popover" style="width:244px;height:204px;" onmouseover="clearTimeout(bZF.themeShop.popoverCloseTimer);jQuery(this).mouseleave(function() {jQuery(this).remove();});"><div class="arrow"></div><div class="popover-inner"><div class="popover-content"><p></p></div></div></div>'
            });

            $(hoverObject).popover('show');

        }, 200);

    }).mouseleave(function (e) {
        clearTimeout(bZF.themeShop.popoverShowTimer);
        var ref = $(this);
        bZF.themeShop.popoverCloseTimer = setTimeout(function () {
            ref.popover('destroy');
        }, 100);
    });

    /************** layout.tpl  悬浮在我的购物车上，显示购物车里面的内容 ******************/
    $('#bzf_header_cart_block, #bzf_right_float_cart_block').mouseenter(function (e) {

        var hoverObject = this;

        // 延迟 200ms 显示弹出框，防止闪动得太厉害
        bZF.themeShop.popoverShowTimer = setTimeout(function () {

            $(hoverObject).popover({
                content: '<div><span style="font-size:14px;font-weight: bold;">我的购物车</span><a style="margin-left: 20px;" class="btn btn-small btn-success" href="' + bZF.makeUrl('/Cart/Show') + '">去结算-&gt;</a><div style="margin-top: 10px;" class="cart_show_container_small"><img style="width:600px;height:250px;" class="lazyload" src="' + BLANK_IMAGE_URL + '" /></div></div>',
                trigger: 'manual',
                html: true,
                placement: 'left',
                template: '<div class="popover" style="width:620px;height:300px;" onmouseover="clearTimeout(bZF.themeShop.popoverCloseTimer);jQuery(this).mouseleave(function() {jQuery(this).remove();});"><div class="arrow"></div><div class="popover-inner"><div class="popover-content"><p></p></div></div></div>'
            });

            $(hoverObject).popover('show');

            // ajax 调用，加载购物车里面的内容显示
            $('.cart_show_container_small').load(bZF.makeUrl('/Cart/Show/ajaxShow?rand=' + new Date().getTime()) + ' #cart_show_ajaxshow', null,
                function (responseText, textStatus, XMLHttpRequest) {
                    /******************* cart_show_ajaxshow.tpl 购物车悬浮窗 删除商品功能 ************************/
                    $('.cart_show_container_small a.cartRemoveGoods').click(function (e) {
                        var currentNode = this;
                        var href = $(this).attr('href');
                        if (href && '' != $.trim(href)) {
                            // 调用 ajax 操作把商品从购物车中删除
                            bZF.ajaxCallGet(href, function () {

                                // 动画删除节点
                                var $nodeToRemove = $(currentNode.parentNode.parentNode);
                                $nodeToRemove.fadeOut(800, function () {
                                    $nodeToRemove.remove();
                                });

                                bZF.cart_goods_count();
                            });
                        } else {
                            $(currentNode.parentNode.parentNode).remove();
                            bZF.cart_goods_count();
                        }
                        return false;
                    });
                });

        }, 200);

    }).mouseleave(function (e) {
        clearTimeout(bZF.themeShop.popoverShowTimer);
        var ref = $(this);
        bZF.themeShop.popoverCloseTimer = setTimeout(function () {
            ref.popover('destroy');
        }, 100);
    });

    /**
     * goods_view.tpl 页面，用户选择商品的不同属性
     *
     * @param level 第几级选择
     *
     */
    bZF.goods_view_choose_spec = function (level, elem) {

        // 不活跃的节点不允许点击
        if (!level || level > 3 || $(elem).hasClass('inactive')) {
            return;
        }

        // 显示为选中状态
        $('span', elem.parentNode).removeClass('active');
        $('input[type="radio"]', elem.parentNode).attr('checked', null);
        $(elem).addClass('active');
        $('input[type="radio"]', elem).attr('checked', 'checked');

        // 解析 goods_spec 数据
        if (!bZF.goods_view_choose_spec.goods_view_goods_spec_array) {

            bZF.goods_view_choose_spec.goods_view_goods_spec_array = $.parseJSON(goods_view_goods_spec_json);

            bZF.goods_view_choose_spec.goods_view_goods_spec_array.goods_number_dict = {};
            bZF.goods_view_choose_spec.goods_view_goods_spec_array.goods_spec_add_price_dict = {};
            bZF.goods_view_choose_spec.goods_view_goods_spec_array.img_id_dict = {};

            // 遍历生成key
            var goodsSpecValue1Array = bZF.goods_view_choose_spec.goods_view_goods_spec_array.goodsSpecValue1Array;
            var goodsSpecValue2Array = bZF.goods_view_choose_spec.goods_view_goods_spec_array.goodsSpecValue2Array;
            var goodsSpecValue3Array = bZF.goods_view_choose_spec.goods_view_goods_spec_array.goodsSpecValue3Array;
            var goodsNumberArray = bZF.goods_view_choose_spec.goods_view_goods_spec_array.goodsNumberArray;
            var goodsSpecAddPriceArray = bZF.goods_view_choose_spec.goods_view_goods_spec_array.goodsSpecAddPriceArray;
            var imgIdArray = bZF.goods_view_choose_spec.goods_view_goods_spec_array.imgIdArray;

            for (var index = 0; index < goodsSpecValue1Array.length; index++) {
                var key = goodsSpecValue1Array[index] + ',' + goodsSpecValue2Array[index] + ',' + goodsSpecValue3Array[index];
                bZF.goods_view_choose_spec.goods_view_goods_spec_array.goods_number_dict[key] = goodsNumberArray[index];
                bZF.goods_view_choose_spec.goods_view_goods_spec_array.goods_spec_add_price_dict[key] = goodsSpecAddPriceArray[index];
                bZF.goods_view_choose_spec.goods_view_goods_spec_array.img_id_dict[key] = imgIdArray[index];
            }
        }

        var goods_view_goods_spec_array = bZF.goods_view_choose_spec.goods_view_goods_spec_array;

        // 当前选中的值
        var currentSpecValue = $('input', elem).val();

        // 看有没有下一级可选
        if (level < 3) {

            var currentSpecValueArray = null;
            var nextSpecValueArray = null;
            var nextSpecAvailableValueArray = {};
            var nextSpecValueElemId = null;

            switch (level) {
                case 1:
                    nextSpecValueElemId = 'goods_view_spec_value2';
                    currentSpecValueArray = goods_view_goods_spec_array.goodsSpecValue1Array;
                    nextSpecValueArray = goods_view_goods_spec_array.goodsSpecValue2Array;
                    // 清除下级的选中状态
                    $('#goods_view_spec_value2 span').removeClass('active');
                    $('#goods_view_spec_value2 span').addClass('inactive');
                    $('#goods_view_spec_value3 span').removeClass('active');
                    $('#goods_view_spec_value3 span').addClass('inactive');

                    // 遍历，看下一级哪个可以选择
                    for (var index = 0; index < currentSpecValueArray.length; index++) {
                        if (currentSpecValue == currentSpecValueArray[index] && '' != $.trim(nextSpecValueArray[index])) {
                            nextSpecAvailableValueArray[nextSpecValueArray[index]] = 1;
                        }
                    }

                    break;

                case 2:
                    nextSpecValueElemId = 'goods_view_spec_value3';
                    currentSpecValueArray = goods_view_goods_spec_array.goodsSpecValue2Array;
                    nextSpecValueArray = goods_view_goods_spec_array.goodsSpecValue3Array;
                    $('#goods_view_spec_value3 span').removeClass('active');
                    $('#goods_view_spec_value3 span').addClass('inactive');

                    // 遍历，看下一级哪个可以选择
                    var firstSpecValue = $('#goods_view_spec_value1 span.active input').val();
                    var goodsSpecValue1Array = goods_view_goods_spec_array.goodsSpecValue1Array;
                    for (var index = 0; index < currentSpecValueArray.length; index++) {
                        if (currentSpecValue == currentSpecValueArray[index]
                            && '' != $.trim(nextSpecValueArray[index])
                            && firstSpecValue == goodsSpecValue1Array[index]) {
                            nextSpecAvailableValueArray[nextSpecValueArray[index]] = 1;
                        }
                    }

                    break;

                default:
                // do nothing
            }

            // 设置下一级是否可以选择
            $('#' + nextSpecValueElemId + ' span').each(function (index, elem) {
                var elemValue = $('input', elem).val();
                if (nextSpecAvailableValueArray[elemValue]) {
                    $(elem).removeClass('inactive');
                }
            });
        }

        // 取得用户的完整选择
        var userChooseSpecKey = '';
        userChooseSpecKey += ($('#goods_view_spec_value1 span.active input').size() > 0) ?
            $('#goods_view_spec_value1 span.active input').val() : '';
        userChooseSpecKey += ',';
        userChooseSpecKey += ($('#goods_view_spec_value2 span.active input').size() > 0) ?
            $('#goods_view_spec_value2 span.active input').val() : '';
        userChooseSpecKey += ',';
        userChooseSpecKey += ($('#goods_view_spec_value3 span.active input').size() > 0) ?
            $('#goods_view_spec_value3 span.active input').val() : '';

        // 设置库存
        if (goods_view_goods_spec_array.goods_number_dict.hasOwnProperty(userChooseSpecKey)) {
            var goodsNumber = goods_view_goods_spec_array.goods_number_dict[userChooseSpecKey];
            $('#bzf_goods_view_goods_number').val(goodsNumber);
            var goodsNumberDisplay = '(库存 ' + goodsNumber + ' 件)';
            $('#goods_view_goods_number_display').text(goodsNumberDisplay);
        } else {
            $('#bzf_goods_view_goods_number').val(0);
            $('#goods_view_goods_number_display').text('');
        }

        // 设置价格
        if (goods_view_goods_spec_array.goods_spec_add_price_dict[userChooseSpecKey]) {
            var goodsSpecAddPrice = goods_view_goods_spec_array.goods_spec_add_price_dict[userChooseSpecKey];
            $('#bzf_goods_view_goods_spec_add_price').val(bZF.money_to_display(goodsSpecAddPrice));
        } else {
            $('#bzf_goods_view_goods_spec_add_price').val(0);
        }
        var goodsFinalPrice = parseFloat($('#bzf_goods_view_shop_price_input').val());
        goodsFinalPrice = isNaN(goodsFinalPrice) ? 0 : goodsFinalPrice;
        goodsFinalPrice += parseFloat($('#bzf_goods_view_goods_spec_add_price').val());
        $('#bzf_goods_view_shop_price_input_span').text(goodsFinalPrice.toFixed(2));

        // 激发图片的切换
        if (goods_view_goods_spec_array.img_id_dict[userChooseSpecKey]) {
            var imgId = goods_view_goods_spec_array.img_id_dict[userChooseSpecKey];
            $('#bzf_goods_view_thumb_image_slider a[data-img_id="' + imgId + '"]').trigger('click');
        }

        // 发送消息，用户选择了商品的不同组合
        $('body').trigger('event_goods_view_choose_spec', elem);
    };

    /************************** goods_view.tpl 页面，用户选择购买一个商品 ***************************/
    bZF.goods_view_goods_buy = function () {

        // 取得商品 id
        var goods_id = $('#bzf_goods_view_goods_id_input').val();
        if (!goods_id || goods_id <= 0) {
            bZF.showMessage('goods_id ' + goods_id + ' 非法');
            return;
        }

        // 取得商品购买数量
        var goods_choose_buycount = parseInt($('#goods_view_buy_count').val());
        goods_choose_buycount = isNaN(goods_choose_buycount) ? 0 : goods_choose_buycount;
        if (goods_choose_buycount <= 0) {
            bZF.showMessage('商品最少买 1 个');
            return;
        }

        // 如果商品有多项选择，用户必须做选择
        var checkChooseSpecArray = ['#goods_view_spec_value1', '#goods_view_spec_value2', '#goods_view_spec_value3'];
        for (var index = 0; index < checkChooseSpecArray.length; index++) {
            var currentSpecIdSelector = checkChooseSpecArray[index];
            if ($(currentSpecIdSelector).size() > 0 && $(currentSpecIdSelector + ' span.active').size() <= 0) {
                var $spanNodeList = $(currentSpecIdSelector + ' span').not('.inactive');
                if ($spanNodeList.size() > 0) {
                    bZF.showMessage('请选择 ' + $(currentSpecIdSelector + ' td').first().text());
                    return;
                }
            }
        }

        // 检查商品的库存
        var goodsNumberAvailable = $('#bzf_goods_view_goods_number').val();
        if (0 == goodsNumberAvailable) {
            bZF.showMessage('库存不足，请换一个选择 ');
            return;
        }

        if (goodsNumberAvailable < goods_choose_buycount) {
            bZF.showMessage('库存只剩 ' + goodsNumberAvailable + ' 件，请修改购买数量');
            return;
        }

        // 动画效果显示加入到购物车的过程
        var $sourceNode = $('.bzf_goods_view_big_image img');
        var $targetNode = $('.icon_cart').parent();
        var $cloneNode = $sourceNode.clone().hide().appendTo('body');
        $cloneNode.css({position: 'absolute', top: $sourceNode.offset().top, left: $sourceNode.offset().left, zIndex: 1000,
            width: $sourceNode.width(), height: $sourceNode.height()});
        $cloneNode.show();
        // 动画显示
        $cloneNode.animate({top: $targetNode.offset().top, left: $targetNode.offset().left, width: $targetNode.width(), height: $targetNode.height()}, 1000, function () {
            $cloneNode.remove();
            $targetNode.jrumble({
                x: 0,
                y: 0,
                rotation: 8
            });
            $targetNode.addClass('hover');
            $targetNode.trigger('startRumble');
            setTimeout(function () {
                $targetNode.trigger('stopRumble');
                $targetNode.removeClass('hover');
            }, 1000);
        });

        // 取得用户的完整选择
        var goods_choose_spec_array = [];
        $('table.bzf_goods_view_select span.active').each(function (index, elem) {
            goods_choose_spec_array.push($('input', elem).val());
        });

        // ajax 调用，添加商品到购物车
        var goods_choose_speclist = goods_choose_spec_array.join(',');
        bZF.ajaxCallPost(bZF.makeUrl('/Goods/Cart'),
            {goods_id: goods_id, goods_choose_speclist: goods_choose_speclist, goods_choose_buycount: goods_choose_buycount},
            function (data) {
                bZF.showMessage(goods_choose_speclist + ' -- ' + goods_choose_buycount + '件 成功加入购物车');
                bZF.cart_goods_count();
            });
    };

    /**
     * goods_view.tpl 页面，商品购买数量
     */
    $('#goods_view_buy_count').spinit({min: 1, max: 100000, stepInc: 1, pageInc: 10, height: 22});

    /**
     * goods_view.tpl 页面，缩略图滑动控件
     */
    $('#bzf_goods_view_thumb_image_slider').jcarousel({
        buttonPrevHTML: '<div>&lt;</div>',
        buttonNextHTML: '<div>&gt;</div>',
        setupCallback: function (carousel) {
            if (carousel.options.size > 5) {
                carousel.options.wrap = 'circular';
            }
        },
        itemVisibleInCallback: function (evt, state, first, last, prevFirst, prevLast) {
            // 由于使用了图片 lazyload，激发一些 event 促进懒加载
            $("img.lazyload", state).show().lazyload({
                effect: "fadeIn"
            });
            $(state).trigger('resize');
        }
    });

    // 采用鼠标滑动切换的方式
    $('#bzf_goods_view_thumb_image_slider .cloud-zoom-gallery').hover(function () {
        $(this).trigger('click');
    }, null);

    /**
     * goods_view.tpl 页面，商品头图放大
     */
    $('.bzf_goods_view_big_image .cloud-zoom, #bzf_goods_view_thumb_image_slider .cloud-zoom-gallery').CloudZoom();
    // 监听 cloud-zoom-gallery-select 事件，设置对应的选中状态
    $('#bzf_goods_view_thumb_image_slider .cloud-zoom-gallery').on('cloud-zoom-gallery-select', function () {
        $('#bzf_goods_view_thumb_image_slider .cloud-zoom-gallery').parent().removeClass('active');
        $(this).parent().addClass('active');
    });

    /**
     * 激发第一个缩略图的点击
     */
    $('#bzf_goods_view_thumb_image_slider .cloud-zoom-gallery').first().trigger('click');

    /**
     * goods_view.tpl 页面，推荐关联商品
     */
    $('.jcarousel-skin-goods-recommand').jcarousel({
        buttonPrevHTML: '<div>&lt;</div>',
        buttonNextHTML: '<div>&gt;</div>',
        //auto: 3,
        initCallback: function (carousel) {
            carousel.clip.hover(function () {
                carousel.stopAuto();
            }, function () {
                carousel.startAuto();
            });
        },
        setupCallback: function (carousel) {
            if (carousel.options.size > 5) {
                carousel.options.wrap = 'circular';
            }
        },
        itemVisibleInCallback: function (evt, state, first, last, prevFirst, prevLast) {
            // 由于使用了图片 lazyload，激发一些 event 促进懒加载
            $("img.lazyload", state).show().lazyload({
                effect: "fadeIn"
            });
            $(state).trigger('resize');
        }
    });

    $('.jcarousel-skin-goods-recommand li').hover(function () {
        //给当前记录加上 active 标记
        $(this).addClass('active');
    }, function () {
        //清除 active 标记
        $(this).removeClass('active');
    });

    /********************* goods_view.tpl 页面，商品推荐，用户鼠标 hover 就自动切换 *******************/
    $('.bzf_goods_view_relate_goods_recommand .nav-tabs a').hover(function () {
        $(this).trigger('click');
    }, null);

    /**
     * goods_view.tpl 页面，让商品详情TabBar 和 商户信息 sticky，跟随滚动
     */
    (function ($) {
        var stickyPanelOptions = {afterDetachCSSClass: 'bzf_sticky'};
        if (bZF.isIE6) { // sticky 功能对于 IE6 要特殊处理
            stickyPanelOptions = {
                afterDetachCSSClass: 'bzf_sticky bzf_sticky_top_ie6',
                onDetached: function (detachedPanel, panelSpacer) {
                    $(detachedPanel).attr('style', null);
                }
            };
        }
        $('#bzf_goods_view_supplier_pane, #bzf_goods_view_goods_detail_tabbar').stickyPanel(stickyPanelOptions);
    })(jQuery);

    /**
     * goods_view.tpl 页面，当鼠标进入商品信息的时候，自动展开
     */
    $('#bzf_goods_view_supplier_pane').on('mouseenter',function () {
        var $this = $(this);
        // 不是悬浮状态不操作
        if (!$this.hasClass('bzf_sticky')) {
            return;
        }
        // 鼠标进入的时候显示面板
        var $supplierMainPane = $('#bzf_goods_view_supplier_pane .bzf_supplier_main_pane');
        if ($supplierMainPane.hasClass('bzf_hide')) {
            $supplierMainPane.removeClass('bzf_hide');
        }
    }).on('mouseleave', function () {
        var $this = $(this);
        // 不是悬浮状态不操作
        if (!$this.hasClass('bzf_sticky')) {
            return;
        }
        // 鼠标移开的时候恢复隐藏
        var $supplierMainPane = $('#bzf_goods_view_supplier_pane .bzf_supplier_main_pane');
        if (!$supplierMainPane.hasClass('bzf_hide')) {
            $supplierMainPane.addClass('bzf_hide');
        }
    });

    /**
     * goods_view.tpl 页面，ajax 加载一页一页的用户评论数据
     */
    bZF.loadGoodsComment = function () {
        var $elem = $(this);
        var callUrl = $elem.attr('href');
        if ('' == $.trim(callUrl)) {
            // 空 URL 不做操作
            return;
        }

        // 加载页面
        $('#bzf_goods_view_goods_comments_pane').load(callUrl, function (responseText, textStatus, XMLHttpRequest) {
            bZF.enhanceHtml($('#bzf_goods_view_goods_comments_pane'));
            $('#bzf_goods_view_goods_comments_pane div.pagination li a').on('click', bZF.loadGoodsComment);

        });

        return false;
    }
    $('#bzf_goods_view_goods_comments_pane div.pagination li a').on('click', bZF.loadGoodsComment);

    /**
     * goods_index.tpl 页面，推荐关联商品
     */
    $('.jcarousel-skin-goods-recommand-compact').jcarousel({
        buttonPrevHTML: '<div>&lt;</div>',
        buttonNextHTML: '<div>&gt;</div>',
        auto: 2,
        initCallback: function (carousel) {
            carousel.clip.hover(function () {
                carousel.stopAuto();
            }, function () {
                carousel.startAuto();
            });
        },
        setupCallback: function (carousel) {
            if (carousel.options.size > 4) {
                // 多于 4 个商品就自动播放
                carousel.options.wrap = 'circular';
            }
        },
        itemVisibleInCallback: function (evt, state, first, last, prevFirst, prevLast) {
            // 由于使用了图片 lazyload，激发一些 event 促进懒加载
            $("img.lazyload", state).show().lazyload({
                effect: "fadeIn"
            });
            $(state).trigger('resize');
        }
    });

    $('.jcarousel-skin-goods-recommand-compact li').hover(function () {
        //给当前记录加上 active 标记
        $(this).addClass('active');
    }, function () {
        //清除 active 标记
        $(this).removeClass('active');
    });


    /**
     * goods_view.tpl, goods_category.tpl, goods_search.tpl 页面  记录商品浏览的历史
     */
    bZF.goodsViewHistory = {};
    bZF.goodsViewHistory.storageKey = 'goodsViewHistoryKey';
    bZF.goodsViewHistory.maxSize = 5; // 缺省最多记录 5 个
    // 清除历史记录
    bZF.goodsViewHistory.clearGoodsViewHistoryArray = function () {
        $.jStorage.set(bZF.goodsViewHistory.storageKey, []);
    };
    // 取得商品浏览历史
    bZF.goodsViewHistory.getGoodsViewHistoryArray = function () {
        var goodsViewHistoryArray = $.jStorage.get(bZF.goodsViewHistory.storageKey);
        if (!goodsViewHistoryArray) {
            return [];
        }
        return goodsViewHistoryArray;
    };
    // 保存商品浏览历史
    bZF.goodsViewHistory.saveGoodsViewHistoryArray = function (goodsViewHistoryArray) {
        $.jStorage.set(bZF.goodsViewHistory.storageKey, goodsViewHistoryArray);
    };
    // 记录一个商品浏览记录
    bZF.goodsViewHistory.pushGoods = function (goodsId, goodsViewUrl, goodsImage, goodsName, goodsPrice) {
        var goodsViewHistoryArray = bZF.goodsViewHistory.getGoodsViewHistoryArray();
        if (goodsViewHistoryArray.length > 0) {
            // 相同商品不要重复放入
            var goodsViewHistoryArrayTmp = [];
            for (var index = 0; index < goodsViewHistoryArray.length; index++) {
                var elem = goodsViewHistoryArray[index];
                if (parseInt(elem.goodsId) != parseInt(goodsId)) {
                    goodsViewHistoryArrayTmp.push(elem);
                }
            }
            goodsViewHistoryArray = goodsViewHistoryArrayTmp;
        }
        var goodsItem = {
            'goodsId': goodsId,
            'goodsViewUrl': goodsViewUrl,
            'goodsImage': goodsImage,
            'goodsName': goodsName,
            'goodsPrice': goodsPrice
        };
        goodsViewHistoryArray.unshift(goodsItem);
        // 删除多余的数据
        if (goodsViewHistoryArray.length > bZF.goodsViewHistory.maxSize) {
            goodsViewHistoryArray.splice(bZF.goodsViewHistory.maxSize, 1);
        }
        // 保存浏览记录
        bZF.goodsViewHistory.saveGoodsViewHistoryArray(goodsViewHistoryArray);
    };

    // 记录用户的浏览记录
    (function () {
        // 只有在 goods_view.tpl 页面才做记录
        if ($('#bzf_goods_view_thumb_image_slider').size() <= 0) {
            return;
        }
        var goodsId = $('#bzf_goods_view_goods_id_input').val();
        var goodsViewUrl = window.location.href;
        var goodsImage = $.trim($($('#bzf_goods_view_thumb_image_slider ul li a img').get(0)).attr('data-original'));
        var goodsName = $.trim($('#bzf_goods_title_caption').text().replace('"', '').replace("'", '')); // 去除引号
        var goodsPrice = $('#bzf_goods_view_shop_price_input').val();

        bZF.goodsViewHistory.pushGoods(goodsId, goodsViewUrl, goodsImage, goodsName, goodsPrice);
    })();

    // goods_view.tpl, goods_category.tpl 页面生成浏览记录
    $('#bzf_goods_view_history, #bzf_goods_search_history').each(function () {
        var $historyDiv = $(this);
        var goodsViewHistoryArray = bZF.goodsViewHistory.getGoodsViewHistoryArray();
        $.each(goodsViewHistoryArray, function (index, elem) {
            var renderHtml = '<div class="goods_view_item">'
                + '<a target="_blank" href="' + $.trim(elem.goodsViewUrl) + '" title="' + $.trim(elem.goodsName) + '">'
                + '<image src="' + $.trim(elem.goodsImage) + '"/></a>'
                + '<p class="price">￥' + elem.goodsPrice + '</p>';
            $historyDiv.append(renderHtml);
        });
    });

    /************** goods_index.tpl 页面，广告图片墙鼠标 hover 自动切换 **************************/
    $('.bzf_shop_index_adv_block ul a').hover(function () {
        $(this).trigger('click');
    }, null);

    /************************ goods_category.tpl 页面，左侧商品分类树形结构 ****************************/
    if ($('#bzf_goods_category_tree_table_panel').size() > 0) {
        $('#bzf_goods_category_tree_table_panel table').detach().treetable({ expandable: true, clickableNodeNames: true, initialState: 'collapsed' }).appendTo($('#bzf_goods_category_tree_table_panel'));

        // 让当前分类自动展开
        var categoryId = $('input[name="category_id"]').val();
        $('#bzf_goods_category_tree_table_panel table tbody tr[data-tt-id="bzf_goods_category_'
            + categoryId + '"] td').trigger('click');
    }

    /*********************** goods_search.tpl, goods_category.tpl 页面，上面属性过滤 ************************************/
        // 建立独立的命名空间
    bZF.goods_filter = {};

    // 提交查询 Form
    bZF.goods_filter.submitForm = function () {
        // 把隐藏值清空
        $('#bzf_goods_search_filter_panel input[type="hidden"]').val('');
        // 拼接值
        $('#bzf_goods_search_filter_panel .bzf_choose_div').each(function (index, div) {
            var $div = $(div);
            var valueArray = [];
            $('button.active', $div).each(function (index, elem) {
                valueArray.push($(elem).attr('data-filterValue'));
            });
            var inputSelector = '#bzf_goods_search_filter_panel input[name="'
                + $div.attr('data-filterKey') + '"]';
            // 在开始加入一个 .，后面在 submit 的时候我们去掉它
            var valueStr = '.' + valueArray.join('_');
            var inputValue = $(inputSelector).val();
            if ('' == $.trim(inputValue)) {
                $(inputSelector).val(valueStr);
            } else {
                $(inputSelector).val(inputValue + valueStr);
            }
        });
        // 去除掉 value 开头的 .
        $('#bzf_goods_search_filter_panel input[type="hidden"]').each(function (index, input) {
            var inputValue = $.trim($(input).val());
            if ('.' == inputValue.charAt(0)) {
                $(input).val(inputValue.substr(1));
            }
        });
        // 提交表单
        $('#bzf_goods_search_filter_panel').parent('form').submit();
    };

    // 设置 filter 按钮为单选
    bZF.goods_filter.setFilterButtonRadio = function ($trNode) {
        // 每个 button 的点击变成 单选
        $('.bzf_choose_div button', $trNode).off('click');
        $('.bzf_choose_div button', $trNode).on('click', function (event) {
            var $this = $(this);
            // 反选当前选择
            if ($this.hasClass('active')) {
                $this.removeClass('active');
            } else {
                // 删除同级别别的选择
                $('button', this.parentNode).not($this).removeClass('active');
                $this.addClass('active');
            }
            // 提交表单
            bZF.goods_filter.submitForm();
        });
    };

    // 设置 filter 按钮为多选
    bZF.goods_filter.setFilterButtonCheckbox = function ($trNode) {
        // 每个 button 的点击变成 多选
        $('.bzf_choose_div button', $trNode).off('click');
        $('.bzf_choose_div button', $trNode).on('click', function (event) {
            var $this = $(this);
            if ($this.hasClass('active')) {
                $this.removeClass('active');
            } else {
                $this.addClass('active');
            }
        });
    };

    // 点击打开多选面板
    bZF.goods_filter.filterMultiChooseOpen = function (trNode) {
        var $trNode = $(trNode);
        // 显示多选状态
        $trNode.addClass('bzf_multi_choose').addClass('well');
        // 保留之前的 active 状态
        $('.bzf_choose_div button.active', $trNode).attr('data-active', true);
        // 每个 button 的点击变成 多选
        bZF.goods_filter.setFilterButtonCheckbox($trNode);
    };

    // 点击关闭多选面板
    bZF.goods_filter.filterMultiChooseClose = function (trNode) {
        var $trNode = $(trNode);
        // 去除多选状态显示
        $trNode.removeClass('bzf_multi_choose').removeClass('well');
        // 把按钮设置为单选
        bZF.goods_filter.setFilterButtonRadio($trNode);
        // 取消选中状态, 恢复之前的选择
        $('.bzf_choose_div button', $trNode).removeClass('active');
        $('.bzf_choose_div button[data-active]', $trNode).addClass('active').removeAttr('data-active');
    };

    // 同步 filter 面板的显示状态
    (function () {

        if ($('#bzf_goods_search_filter_panel').size() <= 0) {
            return;
        }

        // 如果没有筛选面板，直接返回
        var chooseDivSize = $('#bzf_goods_search_filter_panel .bzf_choose_div').size();
        if (chooseDivSize <= 0) {
            return;
        }

        // 缺省设置为单选
        bZF.goods_filter.setFilterButtonRadio($('#bzf_goods_search_filter_panel'));

        // filter 的数据格式为 123_45.34_5_6.78
        var filterItemValue = '' + bZF.getCurrentUrlParam('brand_id');
        var filterStr = bZF.getCurrentUrlParam('filter');
        if (filterStr && '' != filterStr) {
            filterItemValue += '.' + filterStr;
        }
        var filterArray = [];
        if (-1 !== filterItemValue.indexOf('.')) {
            filterArray = filterItemValue.split('.');
        } else {
            filterArray.push(filterItemValue);
        }

        // 筛选面板 和 属性值 不匹配 （某些面板没有值，比如所有商品都没有设置这个属性）
        // 我们这里只处理了商品没有设置品牌的情况，其它情况没有处理
        if (chooseDivSize < filterArray.length) {
            filterArray.shift();
        }

        $('#bzf_goods_search_filter_panel .bzf_choose_div').each(function (index, div) {
            if (index > filterArray.length) {
                return;
            }
            var filterItemValue = $.trim(filterArray[index]);
            if (!filterItemValue || '' == filterItemValue) {
                return;
            }
            var buttonValueArray = [];
            if (-1 !== filterItemValue.indexOf('_')) {
                buttonValueArray = filterItemValue.split('_');
            } else {
                buttonValueArray.push(filterItemValue);
            }
            var $div = $(div);
            $.each(buttonValueArray, function (index, value) {
                $('button[data-filterValue="' + value + '"]', $div).addClass('active');
            });
        });
    })();

    /******************* goods_search.tpl 页面，用户 hover 某个商品，显示标题 ***********************/
    $('.bzf_goods_search_goods_item .bzf_goods_image').hover(function () {
        $(this).addClass('bzf_hover');
    }, function () {
        $(this).removeClass('bzf_hover');
    });

    /**************** goods_search.tpl 页面，根据 url 参数设置对应的控件值 ******************/
    (function () {
        var currentUrl = window.location.href;
        if (currentUrl.indexOf('/Goods/Search') > 0) {
            // 搜索页面如果搜索了商品名，我们需要设置搜索框的内容显示
            $('.bzf_header_search_block input[name="keywords"]').val(bZF.getCurrentUrlParam('keywords'));
        }
    })();

    /**************** goods_category.tpl, goods_search 页面，根据 URL 设置排序按钮的显示状态 ******/
    (function () {

        if ($('.bzf_goods_search_order_filter_bar').size() <= 0) {
            return;
        }

        // 设置隐藏值
        $('.bzf_goods_search_order_filter_bar input[name="category_id"]').val($.trim(bZF.getCurrentUrlParam('category_id')));
        $('.bzf_goods_search_order_filter_bar input[name="orderBy"]').val($.trim(bZF.getCurrentUrlParam('orderBy')));
        $('.bzf_goods_search_order_filter_bar input[name="orderDir"]').val($.trim(bZF.getCurrentUrlParam('orderDir')));
        $('.bzf_goods_search_order_filter_bar input[name="shop_price_min"]').val($.trim(bZF.getCurrentUrlParam('shop_price_min')));
        $('.bzf_goods_search_order_filter_bar input[name="shop_price_max"]').val($.trim(bZF.getCurrentUrlParam('shop_price_max')));

        // 设置排序面板状态
        var $activeButton = null;
        var orderBy = $.trim(bZF.getCurrentUrlParam('orderBy'));
        if ('' == orderBy) {
            $activeButton = $('#bzf_goods_search_order_filter_bar_button_default');
        } else {
            $activeButton = $('.bzf_goods_search_order_filter_bar button[data-orderBy="' + orderBy + '"]')
        }

        // 设置对应的按钮状态
        if ($activeButton) {
            $activeButton.addClass('btn-danger');
            $activeButton.attr('data-orderDir', $.trim(bZF.getCurrentUrlParam('orderDir')));
            if ('asc' == $activeButton.attr('data-orderDir')) {
                $('i', $activeButton).removeClass('icon-arrow-down');
                $('i', $activeButton).addClass('icon-arrow-up');
            }
            $('i', $activeButton).addClass('icon-white');
        }

        // 按钮点击，设置排序参数
        $('.bzf_goods_search_order_filter_bar .btn-group button').on('click', function () {
            $('.bzf_goods_search_order_filter_bar input[name="orderBy"]').val($(this).attr('data-orderBy'));
            if ($(this).hasClass('btn-danger')) {
                // 二次点击
                var dir = 'desc';
                if ('desc' == $(this).attr('data-orderDir')) {
                    dir = 'asc';
                }
                $('.bzf_goods_search_order_filter_bar input[name="orderDir"]').val(dir);
            } else {
                // 首次点击
                $('.bzf_goods_search_order_filter_bar input[name="orderDir"]').val($(this).attr('data-orderDir'));
            }
            return true;
        });

    })();


    /********************  cart_pay 页面，处理用户使用 余额，红包支付 *************************/
    bZF.cart_pay_calculate = function () {
        // 总价格
        var totalPrice = parseFloat($('#cart_pay_order_amount').text());
        totalPrice = isNaN(totalPrice) ? 0 : totalPrice;
        //使用余额
        var surplus = parseFloat($('#cart_pay_surplus').val());
        surplus = isNaN(surplus) ? 0 : surplus;

        //使用红包
        var bonusValue = $('#cart_pay_bonus_value').val(); // 如果订单已经绑定了某个红包，这里可以使用
        if (bonusValue <= 0) {
            bonusValue = parseFloat($('#cart_pay_bonus_select').find("option:selected").val());
        }
        bonusValue = isNaN(bonusValue) ? 0 : bonusValue;

        var moneyToPay = totalPrice - surplus - bonusValue;
        var bonusWasteStr = '';
        if (moneyToPay < 0) {
            var bonusWaste = Math.abs(moneyToPay);
            var bonusUsed = bonusValue - bonusWaste;
            bonusWasteStr = '（红包' + bonusValue + '元，实际使用' + bonusUsed + '元，浪费' + bonusWaste + '元）';
            moneyToPay = 0;
        }

        // 设置警告文本
        $('#cart_pay_bonus_warn').text(bonusWasteStr);

        var html = '订单总金额<span class="price">' + totalPrice.toFixed(2) + '</span>元，使用余额支付<span class="price">' + surplus.toFixed(2) + '</span>元，使用红包<span class="price">' + bonusValue.toFixed(2) + '</span>元，最后需要支付<span class="price">' + moneyToPay.toFixed(2) + '</span>元' + bonusWasteStr;

        $('#cart_pay_final_price_desc').html(html);
    };

    /** ********* 支付页面，用户选择红包 ********** */
    $("#cart_pay_bonus_select").change(function () {
        bZF.cart_pay_calculate();
    });

    /** ********* 支付页面，用户修改余额 ********** */
    $("#cart_pay_surplus").on('keyup', function () {
        bZF.cart_pay_calculate();
    });

    // 页面加载执行一次
    bZF.cart_pay_calculate();

    /************** my_order_goodscomment.tpl 页面，对商品进行评分 ***************/
    bZF.my_order_goodscomment = function (rec_id) {
        var ajaxCallUrl = bZF.makeUrl('/My/Order/GoodsComment');
        $('#bzf_my_order_detail_goods_comment').load(ajaxCallUrl + '?rec_id=' + rec_id, function () {
            bZF.enhanceHtml($('#bzf_my_order_detail_goods_comment'));
            $('#bzf_my_order_detail_goods_comment').modal({dynamic: true});
        });
    };

    /***************************** 处理 ClientData 的显示 **********************************/
    $(document).on('bzf_client_data_ready', function () {

        /*** 显示用户登录信息 ***/
        (function ($) {
            var isUserLogin = bZF.clientData.getClientDataValue(bZF.clientData.KeyIsUserLogin);
            if (isUserLogin) {
                // 如果用户已经登录了，我们修改 welcome 信息
                var userNameDisplay = bZF.clientData.getClientDataValue(bZF.clientData.KeyUserNameDisplay);
                $('.bzf_header_login_register .bzf_welcome').html('欢迎您，'
                    + userNameDisplay + '&nbsp;<a href="' + bZF.makeUrl('/User/Logout') + '">[退出]</a>');
            }
        })(jQuery);

        /******************** 显示网页头部 QQ彩贝登陆提示 ********************/
        (function ($) {
            // 解析彩贝信息，json 结构
            var qqcaibeiViewInfoArray = $.parseJSON(bZF.clientData.getClientDataValue("qqcaibei_viewinfoarray"));
            if (!qqcaibeiViewInfoArray) {
                return;
            }

            //设置显示值
            $('#qqcaibei_header_panel_headshow').html(qqcaibeiViewInfoArray['HeadShow']);
            $('#qqcaibei_header_panel_showmsg').html(qqcaibeiViewInfoArray['ShowMsg']);
            $('#qqcaibei_header_panel_jifenurl').attr('href', qqcaibeiViewInfoArray['JifenUrl']);

            //显示彩贝栏
            $('#qqcaibei_header_panel').show();
        })(jQuery);

        /***  goods_view 页面，用户如果有特权价格需要显示特权价格 ***/
        (function ($) {
            // 取得特权价格字段
            var goods_special_price_field = bZF.clientData.getClientDataValue('goods_special_price_field');
            if (!goods_special_price_field) {
                return;
            }
            // 取得对应的价格，判断价格是否有效
            var specialPrice = parseFloat($('#bzf_goods_special_price_' + goods_special_price_field).val());
            if (isNaN(specialPrice) || parseFloat(specialPrice) <= 0) {
                return;
            }
            // 取得对应价格需要我们在页面上显示的消息
            var specialPriceNotice = $('#bzf_goods_special_price_' + goods_special_price_field + '_notice').val();
            var specialPriceGoodsTitlePrefix = $('#bzf_goods_special_price_' + goods_special_price_field + '_goods_title_prefix').val();

            // 设置特权价格显示的字段
            $('#bzf_goods_special_price label').text(specialPriceNotice);
            $('#bzf_goods_special_price span').text('￥' + specialPrice.toFixed(2));
            $('#bzf_goods_title_caption .bzf_prefix').text(specialPriceGoodsTitlePrefix);

            // 显示对应的字段
            $('#bzf_goods_special_price').show();
            $('#bzf_goods_title_caption .bzf_prefix').show();

            // 用户选择商品的不同规格，我们需要同时计算特殊价格
            $('body').on('event_goods_view_choose_spec', function (event, elem) {
                var addPrice = parseFloat($('#bzf_goods_view_goods_spec_add_price').val());
                addPrice = isNaN(addPrice) ? 0 : addPrice;
                var finalPrice = specialPrice + addPrice;
                $('#bzf_goods_special_price span').text('￥' + finalPrice.toFixed(2));
            });

        })(jQuery);

    });

    // 同步 clientData，并且发送 bzf_client_data_ready 消息
    bZF.clientData.syncClientData();

})(window, jQuery));
