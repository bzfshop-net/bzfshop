<?php

/**
 * @author QiangYu
 *
 * 配置 亿起发 CPS
 *
 * */

namespace Controller\Thirdpart\YiqifaCps;


use Core\Helper\Utility\Validator;
use Plugin\Thirdpart\YiqifaCps\YiqifaCpsPlugin;

class Configure extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        // 取所有的设置值
        $optionValueArray                          = array();
        $optionValueArray['yiqifacps_rate_web']    = YiqifaCpsPlugin::getOptionValue('yiqifacps_rate_web');
        $optionValueArray['yiqifacps_rate_mobile'] = YiqifaCpsPlugin::getOptionValue('yiqifacps_rate_mobile');
        $optionValueArray['yiqifacps_duration']    = YiqifaCpsPlugin::getOptionValue('yiqifacps_duration');
        $optionValueArray['qqcaibei_key1']         = YiqifaCpsPlugin::getOptionValue('qqcaibei_key1');
        $optionValueArray['qqcaibei_key2']         = YiqifaCpsPlugin::getOptionValue('qqcaibei_key2');

        global $smarty;

        $smarty->assign($optionValueArray);

        out_display:
        $smarty->display('yiqifacps_configure.tpl', 'get');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        global $smarty;

        // 参数验证
        $validator             = new Validator($f3->get('POST'));
        $yiqifacps_rate_web    = $validator->required()->validate('yiqifacps_rate_web');
        $yiqifacps_rate_mobile = $validator->required()->validate('yiqifacps_rate_mobile');
        $qqcaibei_key1         = $validator->required()->validate('qqcaibei_key1');
        $qqcaibei_key2         = $validator->required()->validate('qqcaibei_key2');
        $yiqifacps_duration    = $validator->required()->digits()->min(1)->validate('yiqifacps_duration');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 保存设置
        YiqifaCpsPlugin::saveOptionValue('yiqifacps_rate_web', $yiqifacps_rate_web);
        YiqifaCpsPlugin::saveOptionValue('yiqifacps_rate_mobile', $yiqifacps_rate_mobile);
        YiqifaCpsPlugin::saveOptionValue('yiqifacps_duration', $yiqifacps_duration);
        YiqifaCpsPlugin::saveOptionValue('qqcaibei_key1', $qqcaibei_key1);
        YiqifaCpsPlugin::saveOptionValue('qqcaibei_key2', $qqcaibei_key2);

        $this->addFlashMessage('保存设置成功');

        out_display:
        $smarty->display('yiqifacps_configure.tpl', 'post');
    }
}
