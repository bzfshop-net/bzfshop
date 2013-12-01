<?php

/**
 * @author QiangYu
 *
 * 棒主妇商城设置 Base Controller
 *
 * */

namespace Controller\Theme\Shop;

use Core\Asset\ManagerHelper;
use Core\Helper\Utility\Validator;
use Theme\Shop\ShopThemePlugin;

require_once(dirname(__FILE__) . '/../../../smarty_helper.php');

class Base extends \Controller\AuthController
{
    public function beforeRoute($f3)
    {
        parent::beforeRoute($f3);

        // 发布我们自己的资源
        ManagerHelper::publishAsset(
            ShopThemePlugin::pluginGetUniqueId(),
            'css'
        );
        ManagerHelper::publishAsset(
            ShopThemePlugin::pluginGetUniqueId(),
            'js'
        );
        ManagerHelper::publishAsset(
            ShopThemePlugin::pluginGetUniqueId(),
            'img'
        );

        // 插件注册 css, js
        ManagerHelper::registerCss(
            ManagerHelper::getAssetUrl(
                ShopThemePlugin::pluginGetUniqueId(),
                'css/theme_shop.css'
            )
        );
        ManagerHelper::registerCss(
            ManagerHelper::getAssetUrl(
                ShopThemePlugin::pluginGetUniqueId(),
                'css/advblock.css'
            )
        );
        ManagerHelper::registerJs(
            ManagerHelper::getAssetUrl(
                ShopThemePlugin::pluginGetUniqueId(),
                'js/theme_shop.js'
            )
        );

        // 注册自己使用的 smarty 函数
        theme_shop_smarty_register();
    }

}
