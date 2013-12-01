<?php

namespace Controller\Thirdpart\YiqifaCps;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\ClientData;
use Core\Helper\Utility\Route as RouteHelper;
use Core\OrderRefer\ReferHelper;
use Core\Service\User\User as UserBasicService;
use Plugin\Thirdpart\YiqifaCps\YiqifaCpsPlugin;

class CaibeiLogin extends \Controller\BaseController
{

    public function post($f3)
    {
        //首先验证参数签名
        $map = $_POST;
        unset($map['Vkey']); //读取除了vkey外的所有参数，并且放入数组map中

        ksort($map); //进行按参数的升序排序

        //进行签名，注意考虑到md5加密输出的大小写问题，所有约定md5的输出均为小写
        $vkey  = implode('', array_values($map));
        $md5_1 = strtolower(md5($vkey . YiqifaCpsPlugin::getOptionValue('qqcaibei_key1')));
        $vkey  = strtolower(md5($md5_1 . YiqifaCpsPlugin::getOptionValue('qqcaibei_key2')));

        if ($vkey != $_POST['Vkey']) {
            // 参数签名错误
            goto out;
        }

        // 保存额外的 亿起发 参数
        $orderRefer               = array();
        $orderRefer['utm_medium'] = 'QQCAIBEI';

        //设置 cookie
        ReferHelper::setOrderReferSpecific($f3, $orderRefer, YiqifaCpsPlugin::getOptionValue('yiqifacps_duration'));

        $f3->set('SESSION[yiqifa_caibei_order_refer]', ReferHelper::parseOrderRefer($f3));

        // 取得QQ彩贝传递过来的参数
        //$acct     = @$_POST['Acct'];
        $url    = @$_POST['Url'];
        $openId = @$_POST['OpenId'];
        //$clubInfo = @intval($_POST['ClubInfo']); // 会员等级信息，目前没用
        $viewInfo = @$_POST['ViewInfo'];
        if (get_magic_quotes_gpc()) {
            $viewInfo = stripslashes($viewInfo);
        }
        $viewInfoArray = array();
        parse_str($viewInfo, $viewInfoArray); //解析 viewInfoArray 数组
        //$f3->set('SESSION[qqcaibei_viewinfoarray]', json_encode($viewInfoArray)); //放入到 session 里面

        // 设置 ClientData
        ClientData::saveClientData('qqcaibei_viewinfoarray', json_encode($viewInfoArray));

        if (empty($openId)) {
            // 没有  openId 没法登陆，直接退出
            goto out;
        }

        // 这里做 QQ彩贝 联合登陆
        $sns_login = "qq:{$openId}";

        // 用户登陆操作
        $userBasicService = new UserBasicService();
        $authUser         = $userBasicService->doAuthSnsUser($sns_login, null, null, false);

        if ($authUser) {
            goto out_login_user;
        }

        // 之前没有登陆过，自动注册用户
        $authUser = $userBasicService->doAuthSnsUser($sns_login, $openId . '@qq.com', $openId . '@qq.com', true);
        printLog(
            '注册QQ用户：' . print_r($viewInfoArray, true),
            'QQLOGIN',
            \Core\Log\Base::INFO
        );

        out_login_user:

        AuthHelper::saveAuthUser($authUser->toArray(), 'qqcaibei');

        // 设置用户名在网页显示
        ClientData::saveClientData(\Controller\User\Login::$clientDataIsUserLoginKey, true);
        ClientData::saveClientData(
            \Controller\User\Login::$clientDataUserNameDisplayKey,
            'QQ彩贝用户：' . $viewInfoArray['NickName']
        );

        out:

        // 页面跳转到商品
        $redirectUrl = empty($url) ? '/' : $url;

        RouteHelper::reRoute($this, $redirectUrl);
        return;
    }

    public function get($f3)
    {
        RouteHelper::reRoute($this, '/');
    }
}
