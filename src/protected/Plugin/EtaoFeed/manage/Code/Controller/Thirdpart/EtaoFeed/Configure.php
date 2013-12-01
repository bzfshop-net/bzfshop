<?php

/**
 * @author QiangYu
 *
 * 配置 一淘
 *
 * */

namespace Controller\Thirdpart\EtaoFeed;


use Core\Helper\Utility\Validator;
use Plugin\Thirdpart\EtaoFeed\EtaoFeedPlugin;

class Configure extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        // 取所有的设置值
        $optionValueArray                                   = array();
        $optionValueArray['etaofeed_seller_id']             = EtaoFeedPlugin::getOptionValue('etaofeed_seller_id');
        $optionValueArray['etaofeed_goods_url_extra_param'] =
            EtaoFeedPlugin::getOptionValue('etaofeed_goods_url_extra_param');

        global $smarty;

        $smarty->assign($optionValueArray);

        out_display:
        $smarty->display('etaofeed_configure.tpl', 'get');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_configure');

        global $smarty;

        // 参数验证
        $validator                      = new Validator($f3->get('POST'));
        $etaofeed_seller_id             = $validator->required()->validate('etaofeed_seller_id');
        $etaofeed_goods_url_extra_param = $validator->validate('etaofeed_goods_url_extra_param');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 保存设置
        EtaoFeedPlugin::saveOptionValue('etaofeed_seller_id', $etaofeed_seller_id);
        EtaoFeedPlugin::saveOptionValue('etaofeed_goods_url_extra_param', $etaofeed_goods_url_extra_param);

        $this->addFlashMessage('保存设置成功');

        out_display:
        $smarty->display('etaofeed_configure.tpl', 'post');
    }
}
