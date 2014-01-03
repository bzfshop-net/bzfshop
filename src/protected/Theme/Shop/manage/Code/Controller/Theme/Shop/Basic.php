<?php

/**
 * @author QiangYu
 *
 * 棒主妇商城基本信息设置
 *
 * */

namespace Controller\Theme\Shop;

use Cache\ShopClear;
use Core\Helper\Utility\Validator;
use Theme\Shop\ShopThemePlugin;

class Basic extends Base
{

    private $optionKeyArray = array(
        'site_name',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'merchant_name',
        'merchant_address',
        'kefu_telephone',
        'kefu_qq',
        'business_qq',
        'icp',
        'goods_after_service',
        'shop_index_notice',
        'goods_view_detail_notice',
        'statistics_code',
        'smarty_cache_time_ajax_category',
        'smarty_cache_time_shop_index',
        'smarty_cache_time_goods_view',
        'smarty_cache_time_goods_search',
        'smarty_cache_time_article_view',
    );

    private $optionKeyNoFilterArray = array(
        'statistics_code',
        'goods_after_service',
        'shop_index_notice',
        'goods_view_detail_notice',
    );

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_configure');

        // 取得所有 Option 的值
        $optionArray = array();

        foreach ($this->optionKeyArray as $optionKey) {
            $optionArray[$optionKey] = ShopThemePlugin::getOptionValue($optionKey);
        }

        global $smarty;
        $smarty->assign($optionArray);
        $smarty->display('theme_shop_basic.tpl', 'get');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_configure');

        // 参数验证
        $validator = new Validator($f3->get('POST'));

        foreach ($this->optionKeyArray as $optionKey) {
            if (in_array($optionKey, $this->optionKeyNoFilterArray)) {
                $optionValue = $f3->get('POST[' . $optionKey . ']');
            } else {
                $optionValue = $validator->validate($optionKey);
            }

            ShopThemePlugin::saveOptionValue($optionKey, $optionValue);
        }

        // 清除 /Shop/Index 页面
        $shopClear = new ShopClear();
        $shopClear->clearAllCache();

        $this->addFlashMessage('保存设置成功');

        out_display:
        global $smarty;
        $smarty->display('theme_shop_basic.tpl', 'post');

    }
}
