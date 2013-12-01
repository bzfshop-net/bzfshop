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

class FootNav extends Base
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_configure');

        global $smarty;

        $smarty->assign(
            'footNav',
            json_decode(ShopThemePlugin::getOptionValue('foot_nav_json_data'), true)
        );

        $smarty->assign(
            'friendLink',
            json_decode(ShopThemePlugin::getOptionValue('friend_link_json_data'), true)
        );

        $smarty->display('theme_shop_footnav.tpl', 'get');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_configure');

        // 保存数据
        ShopThemePlugin::saveOptionValue('foot_nav_json_data', json_encode($f3->get('POST[footNav]')));
        ShopThemePlugin::saveOptionValue('friend_link_json_data', json_encode($f3->get('POST[friendLink]')));

        // 清除 所有页面
        $shopClear = new ShopClear();
        $shopClear->clearAllCache();

        $this->addFlashMessage('保存设置成功');

        RouteHelper::reRoute($this, '/Theme/Shop/FootNav');
    }
}
