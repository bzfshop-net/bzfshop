<?php

/**
 * @author QiangYu
 *
 * 配置 360 一站通登陆
 *
 * */

namespace Controller\Thirdpart\Dev360Auth;


use Core\Helper\Utility\Validator;
use Plugin\Thirdpart\Dev360Auth\Dev360AuthPlugin;

class Configure extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        // 取所有的设置值
        $optionValueArray = array();
        // shop
        $optionValueArray['shop_dev360auth_app_id']      = Dev360AuthPlugin::getOptionValue('shop_dev360auth_app_id');
        $optionValueArray['shop_dev360auth_app_key']     = Dev360AuthPlugin::getOptionValue('shop_dev360auth_app_key');
        $optionValueArray['shop_dev360auth_app_secrect'] =
            Dev360AuthPlugin::getOptionValue('shop_dev360auth_app_secrect');

        // aimeidaren
        $optionValueArray['aimeidaren_dev360auth_app_id']      =
            Dev360AuthPlugin::getOptionValue('aimeidaren_dev360auth_app_id');
        $optionValueArray['aimeidaren_dev360auth_app_key']     =
            Dev360AuthPlugin::getOptionValue('aimeidaren_dev360auth_app_key');
        $optionValueArray['aimeidaren_dev360auth_app_secrect'] =
            Dev360AuthPlugin::getOptionValue('aimeidaren_dev360auth_app_secrect');

        global $smarty;

        $smarty->assign($optionValueArray);

        out_display:
        $smarty->display('dev360auth_configure.tpl', 'get');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('POST'));
        // shop
        $shop_dev360auth_app_id      = $validator->required()->digits()->validate('shop_dev360auth_app_id');
        $shop_dev360auth_app_key     = $validator->required()->validate('shop_dev360auth_app_key');
        $shop_dev360auth_app_secrect = $validator->required()->validate('shop_dev360auth_app_secrect');
        // aimeidaren
        $aimeidaren_dev360auth_app_id      = $validator->required()->digits()->validate('aimeidaren_dev360auth_app_id');
        $aimeidaren_dev360auth_app_key     = $validator->required()->validate('aimeidaren_dev360auth_app_key');
        $aimeidaren_dev360auth_app_secrect = $validator->required()->validate('aimeidaren_dev360auth_app_secrect');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 保存设置 shop
        Dev360AuthPlugin::saveOptionValue('shop_dev360auth_app_id', $shop_dev360auth_app_id);
        Dev360AuthPlugin::saveOptionValue('shop_dev360auth_app_key', $shop_dev360auth_app_key);
        Dev360AuthPlugin::saveOptionValue('shop_dev360auth_app_secrect', $shop_dev360auth_app_secrect);
        // 保存设置 aimeidaren
        Dev360AuthPlugin::saveOptionValue('aimeidaren_dev360auth_app_id', $aimeidaren_dev360auth_app_id);
        Dev360AuthPlugin::saveOptionValue('aimeidaren_dev360auth_app_key', $aimeidaren_dev360auth_app_key);
        Dev360AuthPlugin::saveOptionValue('aimeidaren_dev360auth_app_secrect', $aimeidaren_dev360auth_app_secrect);

        $this->addFlashMessage('保存设置成功');

        out_display:
        $smarty->display('dev360auth_configure.tpl', 'post');
    }
}
