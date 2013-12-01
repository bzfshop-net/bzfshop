<?php

/**
 * @author QiangYu
 *
 * 棒主妇商城首页的广告设置，下部一个一个广告 block
 *
 * */

namespace Controller\Theme\Shop;


use Cache\ShopClear;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Theme\Shop\ShopThemePlugin;

class AdvShopBlock extends Base
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_adv_configure');

        global $smarty;

        $shop_index_advblock_json_data =
            json_decode(ShopThemePlugin::getOptionValue('shop_index_advblock_json_data'), true);
        if (!empty($shop_index_advblock_json_data)) {

            // 生成随机的 id 号给 html 使用
            foreach ($shop_index_advblock_json_data as &$advBlockObject) {
                $advBlockObject['id'] = Utils::generateRandomHtmlId();
                foreach ($advBlockObject['advBlockImageArray'] as &$advBlockImage) {
                    $advBlockImage['id'] = Utils::generateRandomHtmlId();
                }
            }

            $smarty->assign(
                'shop_index_advblock_json_data',
                $shop_index_advblock_json_data
            );
        }

        $smarty->display('theme_shop_advshop_block.tpl', 'get');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_adv_configure');

        // 参数验证
        $validator = new Validator($f3->get('POST'));

        // 广告设置 json 数据，由 JavaScript 打包发送过来
        $shop_index_advblock_json_data = $validator->validate('shop_index_advblock_json_data');

        $jsonObject = json_decode($shop_index_advblock_json_data, true);
        if (empty($jsonObject)) {
            $shop_index_advblock_json_data = null;
        }

        ShopThemePlugin::saveOptionValue(
            'shop_index_advblock_json_data',
            $shop_index_advblock_json_data
        );

        // 清除 /Shop/Index 页面
        $shopClear = new ShopClear();
        $shopClear->clearHomePage();

        $this->addFlashMessage('保存设置成功');

        RouteHelper::reRoute($this, '/Theme/Shop/AdvShopBlock');
    }
}
