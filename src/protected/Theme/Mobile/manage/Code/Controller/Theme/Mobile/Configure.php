<?php

/**
 * @author QiangYu
 *
 * 我的订单控制器
 *
 * */

namespace Controller\Theme\Mobile;


use Cache\MobileClear;
use Core\Helper\Utility\Validator;
use Theme\Mobile\MobileThemePlugin;

class Configure extends \Controller\AuthController
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
        'icp',
        'goods_after_service',
        'google_analytics_ua',
        'smarty_cache_time_cache_page',
        'smarty_cache_time_goods_index',
        'smarty_cache_time_goods_search',
        'smarty_cache_time_goods_view',
        'smarty_cache_time_goods_buy',
    );

    private $optionKeyNoFilterArray = array(
        'goods_after_service'
    );

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_configure');

        // 取得所有 Option 的值
        $optionValueArray = array();

        foreach ($this->optionKeyArray as $optionKey) {
            $optionValueArray[$optionKey] = MobileThemePlugin::getOptionValue($optionKey);
        }

        global $smarty;
        $smarty->assign($optionValueArray);
        $smarty->display('theme_mobile_configure.tpl', 'get');
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

            MobileThemePlugin::saveOptionValue($optionKey, $optionValue);
        }

        // 清除所有缓存
        $cacheClear = new MobileClear();
        $cacheClear->clearAllCache();

        $this->addFlashMessage('保存设置成功');

        out_display:
        global $smarty;
        $smarty->display('theme_mobile_configure.tpl', 'post');
    }
}
