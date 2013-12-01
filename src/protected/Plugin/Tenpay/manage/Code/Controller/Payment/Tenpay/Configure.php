<?php

/**
 * @author QiangYu
 *
 * 配置财付通即时到账插件
 *
 * */

namespace Controller\Payment\Tenpay;


use Core\Helper\Utility\Validator;
use Plugin\Payment\Tenpay\TenpayPlugin;

class Configure extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        // 取所有的设置值
        $optionValueArray                = array();
        $optionValueArray['partner_id']  = TenpayPlugin::getOptionValue('partner_id');
        $optionValueArray['partner_key'] = TenpayPlugin::getOptionValue('partner_key');

        global $smarty;

        $smarty->assign($optionValueArray);

        out_display:
        $smarty->display('tenpay_configure.tpl', 'get');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        global $smarty;

        // 参数验证
        $validator   = new Validator($f3->get('POST'));
        $partner_id  = $validator->required()->validate('partner_id');
        $partner_key = $validator->required()->validate('partner_key');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 保存设置
        TenpayPlugin::saveOptionValue('partner_id', $partner_id);
        TenpayPlugin::saveOptionValue('partner_key', $partner_key);

        $this->addFlashMessage('保存设置成功');

        out_display:
        $smarty->display('tenpay_configure.tpl', 'post');
    }
}
