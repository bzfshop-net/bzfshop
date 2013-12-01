<?php

/**
 * @author QiangYu
 *
 * 配置支付宝WAP插件
 *
 * */

namespace Controller\Payment\AlipayWap;


use Core\Helper\Utility\Validator;
use Plugin\Payment\AlipayWap\AlipayWapPlugin;

class Configure extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        // 取所有的设置值
        $optionValueArray                = array();
        $optionValueArray['partner_id']  = AlipayWapPlugin::getOptionValue('partner_id');
        $optionValueArray['partner_key'] = AlipayWapPlugin::getOptionValue('partner_key');
        $optionValueArray['account']     = AlipayWapPlugin::getOptionValue('account');

        global $smarty;

        $smarty->assign($optionValueArray);

        out_display:
        $smarty->display('alipaywap_configure.tpl', 'get');
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
        $account     = $validator->required()->validate('account');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 保存设置
        AlipayWapPlugin::saveOptionValue('partner_id', $partner_id);
        AlipayWapPlugin::saveOptionValue('partner_key', $partner_key);
        AlipayWapPlugin::saveOptionValue('account', $account);

        $this->addFlashMessage('保存设置成功');

        out_display:
        $smarty->display('alipaywap_configure.tpl', 'post');
    }
}
