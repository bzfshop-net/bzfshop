<?php

/**
 * @author QiangYu
 *
 * 棒主妇商城首页的广告设置，顶部图片滚动
 *
 * */

namespace Controller\Theme\Shop;

use Cache\ShopClear;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Theme\Shop\ShopThemePlugin;

class AdvShopSlider extends Base
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_adv_configure');

        global $smarty;

        $smarty->assign(
            'shop_index_adv_slider',
            json_decode(ShopThemePlugin::getOptionValue('shop_index_adv_slider'), true)
        );

        $smarty->display('theme_shop_advshop_slider.tpl', 'get');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_adv_configure');

        // 参数验证
        $validator = new Validator($f3->get('POST'));

        // slider 广告设置
        $imageArray  = $validator->validate('image');
        $urlArray    = $validator->validate('url');
        $targetArray = $validator->validate('target');

        $imageSize             = is_array($imageArray) ? count($imageArray) : 0;
        $shop_index_adv_slider = array();

        // 组织数据结构
        for ($index = 0; $index < $imageSize; $index++) {
            $shop_index_adv_slider[] =
                array('image' => $imageArray[$index], 'url' => $urlArray[$index], 'target' => $targetArray[$index]);
        }

        ShopThemePlugin::saveOptionValue(
            'shop_index_adv_slider',
            json_encode($shop_index_adv_slider)
        );

        // 清除 /Shop/Index 页面
        $shopClear = new ShopClear();
        $shopClear->clearHomePage();

        $this->addFlashMessage('保存设置成功');

        RouteHelper::reRoute($this, '/Theme/Shop/AdvShopSlider');
    }
}
