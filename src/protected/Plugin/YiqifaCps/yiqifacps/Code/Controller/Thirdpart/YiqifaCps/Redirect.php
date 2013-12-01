<?php

namespace Controller\Thirdpart\YiqifaCps;


use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\OrderRefer\ReferHelper;
use Plugin\Thirdpart\YiqifaCps\YiqifaCpsPlugin;

class Redirect extends \Controller\BaseController
{

    public function get($f3)
    {
        // 设置 order_refer 记录，记录在客户端
        $orderRefer = array();
        // 检查彩贝的记录
        $caibeiRefer = $f3->get('SESSION[yiqifa_caibei_order_refer]');
        if (!empty($caibeiRefer)) {
            $orderRefer = $caibeiRefer;
            unset($orderRefer['refer_host']); // 去掉彩贝的 refer_host
        }
        // 清除彩贝记录
        $f3->set('SESSION[yiqifa_caibei_order_refer]', null);

        $orderRefer['utm_source'] = 'YIQIFACPS';

        // 保存额外的 亿起发 参数
        $validator = new Validator($_REQUEST);

        $referParamArray            = array();
        $referParamArray['src']     = $validator->validate('src');
        $referParamArray['cid']     = $validator->validate('cid');
        $referParamArray['wi']      = $validator->validate('wi');
        $referParamArray['channel'] = $validator->validate('channel');

        $orderRefer['refer_param'] = json_encode($referParamArray);

        //设置 cookie
        ReferHelper::setOrderReferSpecific($f3, $orderRefer, YiqifaCpsPlugin::getOptionValue('yiqifacps_duration'));

        // 页面跳转到商品
        $url         = $validator->validate('url');
        $redirectUrl = empty($url) ? '/' : $url;

        RouteHelper::reRoute($this, $redirectUrl);
        return;
    }

    public function post($f3)
    {
        // 把 post 的值都给 GET
        $get = $f3->get('GET');
        $get = array_merge($get, $f3->get('POST'));
        $f3->set('GET', $get);

        $this->get($f3);
    }
}
