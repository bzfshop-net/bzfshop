<?php
use Core\Asset\ManagerHelper;
use Theme\Shop\ShopThemePlugin;


/**************** 注册 smarty 函数 *********************/
function theme_shop_smarty_register()
{
    global $smarty;
    $smarty->registerPlugin('function', 'theme_shop_get_asset_url', 'theme_shop_smarty_helper_function_get_asset_url');
}


function theme_shop_smarty_helper_function_get_asset_url(array $paramArray, $smarty)
{
    if (!isset($paramArray['asset'])) {
        return '';
    }

    return ManagerHelper::getAssetUrl(
        ShopThemePlugin::pluginGetUniqueId(),
        $paramArray['asset']
    );
}
