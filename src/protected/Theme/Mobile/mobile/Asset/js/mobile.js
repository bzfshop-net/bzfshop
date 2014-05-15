/**
 * @author QiangYu
 *
 * mobile project JS file
 */
(function (window, $) {

    /************** fix ajax call getJSON with no response in IE **************/
    jQuery.support.cors = true;

    if (!window.console) {
        window.console = { log: function () {
        } };
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

    var bZF = {};

    // bZF 放入到全局命名空间
    window.bZF = bZF;

    /**
     * 构建完整的 URL，有一些跨域请求需要完整的 URL 才能执行
     *
     * @param url
     * @returns {*}
     */
    bZF.makeUrl = function (url) {
        return WEB_ROOT_HOST + WEB_ROOT_BASE + url;
    };

    /******** 打印出错消息，方便检查错误 *********/
    bZF.showMessage = function (message) {

        $("#flash_message_popup ul", $($.mobile.activePage)).remove();
        $("#flash_message_popup", $($.mobile.activePage)).append('<ul><li>' + message + '</li></ul>');

        // 弹出框
        $('#flash_message_popup', $($.mobile.activePage)).trigger('refresh');
        $('#flash_message_popup', $($.mobile.activePage)).popup("open");

        // 显示在 console ，方便调试
        console.log('[' + new Date().pattern("yyyy-MM-dd HH:mm:ss") + ']' + message);
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
            beforeSend: function () {
                $.mobile.loading('show');
            },
            complete: function () {
                $.mobile.loading('hide');
            },
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
            beforeSend: function () {
                $.mobile.loading('show');
            },
            complete: function () {
                $.mobile.loading('hide');
            },
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

    /********** 系统全局初始化设置 **********/
    $(document).bind('mobileinit', function () {
        $.mobile.loader.prototype.options.text = '玩命加载中...';
        $.mobile.loader.prototype.options.textVisible = true;
        $.mobile.loader.prototype.options.theme = 'e';
        $.mobile.loader.prototype.options.html = '';

        $.mobile.pageLoadErrorMessage = '页面加载错误';
        $.mobile.pageLoadErrorMessageTheme = 'e';

        $.mobile.page.prototype.options.backBtnText = '返回';
        $.mobile.page.prototype.options.backBtnTheme = "f";

        // 让 route 能识别不同页面的参数
        $.mobile.jqmRouter = {ajaxApp: true};
    });


    /******* 缓存页面的加载处理 *******/
    bZF.cachePageHtml = null;
    bZF.sessionId = null;
    bZF.cachePageId = 'cache_page';
    bZF.cachePageExistId = 'cache_page_exist';

    /**
     *
     * 通用的页面初始化过程，所有的页面都需要
     *
     * */
    bZF.common_page_init = function () {

        // 遍历页面，插入缓存页面组件
        $('div[data-role="page"]', $(document)).each(function (index, elem) {

            if ($('#' + bZF.cachePageExistId, $(elem)).size() > 0) {
                // 页面里面已经插入了 cache_page ，不要做任何操作了
                return;
            }

            $(elem).append(bZF.cachePageHtml);

            // 成功插入了 cachePage，我们修改 page_header 中的链接，采用 ajax 方式做调用更加友好
            $('#page_header_goods_category', $(elem)).attr('href', '#goods_category_panel');
            $('#page_header_my_account', $(elem)).attr('href', '#my_account_panel');

            $('#page_header_user_login', $(elem)).attr('href', '#user_login_popup');
            $('#page_header_user_login', $(elem)).attr('data-rel', 'popup');
            $('#page_header_user_login', $(elem)).attr('data-position-to', 'window');

            $('#page_header_user_register', $(elem)).attr('href', '#user_register_popup');
            $('#page_header_user_register', $(elem)).attr('data-rel', 'popup');
            $('#page_header_user_register', $(elem)).attr('data-position-to', 'window');

            $(elem).trigger('create');
        });

        // 登陆、注册 需要每次都更新验证码
        $('#user_login_popup,#user_register_popup').on('popupafteropen', function () {
            // 刷新验证码
            $('img', this).attr('src', bZF.makeUrl('/Image/Captcha') + '?hash=' + new Date().getTime() + '&' + SESSION_NAME + '=' + SESSION_ID);
            // 记录当前连接，方便跳转回来
            $('input[name="returnUrl"]', this).val($.mobile.activePage.data('url'));
        });

        // 我的账号，需要显示用户名
        if (USER_NAME_DISPLAY) {
            $('#my_account_user_name_display a').text(USER_NAME_DISPLAY);
        }

        /*********** 如果有 flash message 则显示它 *************/
        (function ($) {
            var flashMessageStr = FLASH_MESSAGE_STR;
            if (!flashMessageStr || !(flashMessageStr = eval(flashMessageStr))) {
                return;
            }

            var msgHtml = '';
            // 解析 json, flash message 只是简单的一维数组
            $.each(flashMessageStr, function (i, item) {
                msgHtml += '<li>' + item + '</li>';
            });

            msgHtml = '<ul>' + msgHtml + '</ul>';

            $("#flash_message_popup ul", $($.mobile.activePage)).remove();
            $("#flash_message_popup", $($.mobile.activePage)).append(msgHtml);

            // 延迟触发弹出框
            setTimeout(function () {
                $('#flash_message_popup', $($.mobile.activePage)).trigger('refresh');
                $('#flash_message_popup', $($.mobile.activePage)).popup("open");
            }, 700);

        })(jQuery);

    };

    /** 页面初始化，加载缓存页面 **/
    $(document).on('pageinit', function () {

        // 加载 cachePage
        if (!bZF.cachePageHtml || bZF.sessionId != SESSION_ID) {
            bZF.sessionId = SESSION_ID;
            // 预取 Cache 页面，以后都不用再取了
            $.get(bZF.makeUrl('/Cache/Page?' + SESSION_NAME + '=' + SESSION_ID), function (data) {
                bZF.cachePageHtml = data;
                bZF.common_page_init();
            });

        } else {
            bZF.common_page_init();
        }

        /**
         * 开启图片懒加载，所有 <img class="lazyload" src="placeholder.jpg" data-original="pic.jpg" />
         */
        $("img.lazyload").show().lazyload({
            event: 'scrollstop'
        });

        /** 点击加载图片 **/
        $("img.lazyloadtap").show().lazyload({
            event: 'tap'
        });

        setTimeout(function () {
            // 第一次触发懒加载
            $(document).trigger('scrollstop');
        }, 500);


        // 所有的 bzf_zoom 图片都允许点击放大显示
        $('img.bzf_zoom').on('tap', function () {
            var zoomImage = $(this).attr('data-image-zoom');
            if (!zoomImage) {
                zoomImage = $(this).attr('data-original');
            }
            if (!zoomImage) {
                zoomImage = $(this).attr('src');
            }
            $('#zoom_image_popup img', $($.mobile.activePage)).attr('src', ZOOM_IMAGE_PLACEHOLDER);
            $('#zoom_image_popup img', $($.mobile.activePage)).attr('data-original', zoomImage);
            $('#zoom_image_popup', $($.mobile.activePage)).trigger('refresh');

            // 300ms 之后刷新显示
            setTimeout(function () {
                // 把 data-original 复制给 src
                $('#zoom_image_popup img', $($.mobile.activePage)).attr('src',
                    $('#zoom_image_popup img', $($.mobile.activePage)).attr('data-original'));
                $('#zoom_image_popup', $($.mobile.activePage)).trigger('refresh');
            }, 300);

            $('#zoom_image_popup', $($.mobile.activePage)).popup('open');
        });

        /*********  goods_view 页面表，商品加入到购物车 ***********/
        $('#bzf_goods_view_add_goods_to_cart').on('click', bZF.goods_view_goods_buy);

        /*********  cart_show 页面表单验证 ***********/
        $('#cart_show_form').submit(bZF.cart_show_check_input);

    });

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

        // 检查商品库存
        var goods_choose_speclist = $('#goods_choose_speclist').find('option:selected').val();

        // 检查 商品规格 的库存
        var goodsNumber = parseInt($('#goods_choose_speclist').find('option:selected').attr('goods_number'));
        goodsNumber = isNaN(goodsNumber) ? 0 : goodsNumber;
        if (goods_choose_buycount > goodsNumber) {
            bZF.showMessage('库存不足，只剩 ' + goodsNumber + ' 件');
            return;
        }

        // ajax 调用，添加商品到购物车
        bZF.ajaxCallPost(bZF.makeUrl('/Goods/Cart'),
            {goods_id: goods_id, goods_choose_speclist: goods_choose_speclist, goods_choose_buycount: goods_choose_buycount},
            function (data) {
                bZF.showMessage(goods_choose_speclist + ' -- ' + goods_choose_buycount + '件 成功加入购物车');
            });
    };

    /**** cart_show 页面检查用户输入 ****/
    bZF.cart_show_check_input = function () {

        var address = $('#cart_show_form input[name="address"]').val();
        if (!address) {
            bZF.showMessage('收件地址不能为空');
            return false;
        }

        var consignee = $('#cart_show_form input[name="consignee"]').val();
        if (!consignee) {
            bZF.showMessage('收件人不能为空');
            return false;
        }

        var mobile = $('#cart_show_form input[name="mobile"]').val();
        if (!mobile) {
            bZF.showMessage('手机号不能为空');
            return false;
        }

        return true;
    };

    /**
     *  把所有懒加载的图片一次性加载完成
     *
     * @param node
     */
    bZF.load_all_lazy_image = function () {
        $('img[data-original]', $.mobile.activePage).each(function (index, elem) {
            $(elem).attr('src', $(elem).attr('data-original'));
        });
        bZF.showMessage('图片已全部显示');
    };

})(window, jQuery);