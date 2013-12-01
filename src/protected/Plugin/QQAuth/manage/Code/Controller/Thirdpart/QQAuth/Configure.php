<?php

/**
 * @author QiangYu
 *
 * 配置 QQ 登陆
 *
 * */

namespace Controller\Thirdpart\QQAuth;


use Core\Helper\Utility\Validator;
use Plugin\Thirdpart\QQAuth\QQAuthPlugin;

class Configure extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        // 取所有的设置值
        $optionValueArray                  = array();
        $optionValueArray['qqauth_appid']  = QQAuthPlugin::getOptionValue('qqauth_appid');
        $optionValueArray['qqauth_appkey'] = QQAuthPlugin::getOptionValue('qqauth_appkey');

        global $smarty;

        $smarty->assign($optionValueArray);

        out_display:
        $smarty->display('qqauth_configure.tpl', 'get');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        global $smarty;

        // 参数验证
        $validator     = new Validator($f3->get('POST'));
        $qqauth_appid  = $validator->required()->validate('qqauth_appid');
        $qqauth_appkey = $validator->required()->validate('qqauth_appkey');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 保存设置
        QQAuthPlugin::saveOptionValue('qqauth_appid', $qqauth_appid);
        QQAuthPlugin::saveOptionValue('qqauth_appkey', $qqauth_appkey);

        $this->addFlashMessage('保存设置成功');

        out_display:
        $smarty->display('qqauth_configure.tpl', 'post');
    }
}
