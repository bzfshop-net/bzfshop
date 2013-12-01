<?php

/**
 * @author QiangYu
 *
 * 所有 Controller 的基类
 *
 * */

namespace Controller;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Validator;
use Core\OrderRefer\ReferHelper;
use Theme\Mobile\MobileThemePlugin;

class BaseController extends \Core\Controller\BaseController
{
    // 由于 Mobile 不能使用 Cookie (很多手机不支持 Cookie)，我们只能采用 Session 来实现 FlashMessage 的存储
    private $isFlashMessageConsumed = false;
    // flash message 保存key
    private $flashMessageStoragekey = 'SESSION[mobileFlashMessageStorage]';

    public function getFlashMessageArray()
    {
        global $f3;

        $this->isFlashMessageConsumed = true;

        // 合并 session  中的 flash message
        $flashMessageArray        = $this->flashMessageArray;
        $sessionFlashMessageArray = $f3->get($this->flashMessageStoragekey);
        if (!empty($sessionFlashMessageArray)) {
            $flashMessageArray = array_merge($this->flashMessageArray, $sessionFlashMessageArray);
            $f3->clear($this->flashMessageStoragekey);
        }

        return $flashMessageArray;
    }

    public function beforeRoute($f3)
    {
        parent::beforeRoute($f3);

        //设置 SEO 信息
        global $smarty;
        $smarty->assign('seo_title', MobileThemePlugin::getOptionValue('seo_title'));
        $smarty->assign('seo_description', MobileThemePlugin::getOptionValue('seo_description'));
        $smarty->assign('seo_keywords', MobileThemePlugin::getOptionValue('seo_keywords'));

        // 设置页面显示用户名
        if (AuthHelper::isAuthUser()) {
            global $smarty;
            $authUser = AuthHelper::getAuthUser();
            $smarty->assign('USER_NAME_DISPLAY', $authUser['user_name']);
        }

        // 记录用户从什么来源到达网站的
        ReferHelper::setOrderRefer($f3); //设置 order_refer 信息
    }

    public function afterRoute($f3)
    {
        // 记录用户从什么来源到达网站的
        ReferHelper::syncOrderReferStorage($f3); //同步信息

        // flash message 没有被消耗，保存到 session 中等下次使用
        if (!$this->isFlashMessageConsumed) {
            $f3->set($this->flashMessageStoragekey, $this->flashMessageArray);
        }

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

