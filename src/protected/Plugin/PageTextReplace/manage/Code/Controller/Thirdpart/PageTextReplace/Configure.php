<?php

/**
 * @author QiangYu
 *
 * 配置 PageTextReplace
 *
 * */

namespace Controller\Thirdpart\PageTextReplace;


use Core\Helper\Utility\Validator;
use Plugin\Thirdpart\PageTextReplace\PageTextReplacePlugin;

class Configure extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        // 取所有的设置值
        $optionValueArray            = array();
        $optionValueArray['pattern'] = PageTextReplacePlugin::getOptionValue('pattern');
        $optionValueArray['replace'] = PageTextReplacePlugin::getOptionValue('replace');

        global $smarty;

        $smarty->assign($optionValueArray);

        out_display:
        $smarty->display('pagetextreplace_configure.tpl', 'get');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        global $smarty;

        // 参数验证
        $pattern = trim($f3->get('POST[pattern]'));
        $replace = trim($f3->get('POST[replace]'));

        // 保存设置
        PageTextReplacePlugin::saveOptionValue('pattern', $pattern);
        PageTextReplacePlugin::saveOptionValue('replace', $replace);

        $this->addFlashMessage('保存设置成功');

        out_display:
        $smarty->display('pagetextreplace_configure.tpl', 'post');
    }
}
