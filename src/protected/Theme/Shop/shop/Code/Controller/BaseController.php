<?php

/**
 * @author QiangYu
 *
 * 所有 Controller 的基类
 *
 * */

namespace Controller;

use Core\Helper\Utility\ClientData;
use Core\OrderRefer\ReferHelper;

class BaseController extends \Core\Controller\BaseController
{

    public function beforeRoute($f3)
    {
        parent::beforeRoute($f3);
        // do nothing

        //用于显示购物车里面有多少商品
        global $smarty;

        //设置 SEO 信息
        $smarty->assign('seo_title', bzf_get_option_value('seo_title'));
        $smarty->assign('seo_description', bzf_get_option_value('seo_description'));
        $smarty->assign('seo_keywords', bzf_get_option_value('seo_keywords'));

        // 记录用户从什么来源到达网站的
        ReferHelper::setOrderRefer($f3); //设置 order_refer 信息
    }

    public function afterRoute($f3)
    {
        // 使用 Cookie 输出 flash message，目的是方便页面做缓存，别把这些数据写在页面上
        if (count($this->flashMessageArray) > 0) {
            setcookie('flash_message', json_encode($this->flashMessageArray), 0, $f3->get('BASE') . '/');
        } else {
            //setcookie('flash_message', null, 0, $f3->get('BASE')); // 清除上次的旧数据
        }

        // 记录用户从什么来源到达网站的
        ReferHelper::syncOrderReferStorage($f3); //同步信息

        // 通知客户端数据发生了变化，让客户端主动来取数据
        ClientData::notifyClientDataChange();

        parent::afterRoute($f3);
    }

    /**
     * Http 的 HEAD 方法调用
     *
     * @param $f3
     */
    public function head($f3)
    {
        // do nothing here
    }

}
