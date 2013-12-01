<?php

/**
 * @author QiangYu
 *
 * 管理员列表
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\Validator;
use Core\Service\BaseService;

class AdminUser extends \Controller\BaseController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    /**
     * 列出在线客服
     *
     * @param $f3
     */
    public function ListKefuIdName($f3)
    {
        global $smarty;

        // 检查缓存
        $smartyCacheId = 'Ajax|' . md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);

        enableSmartyCache(true, 600); // 缓存 10 分钟
        // 判断是否有缓存
        if ($smarty->isCached('empty.tpl', $smartyCacheId)) {
            goto out_display;
        }

        $baseService = new BaseService();
        $adminArray  =
            $baseService->_fetchArray(
                'admin_user',
                'user_id,user_name',
                array(array('disable = 0'), array('is_kefu = 1')),
                array('order' => 'user_id desc'),
                0,
                0,
                0
            );

        $smarty->assign('outputContent', Ajax::buildResult(null, null, $adminArray));

        out_display:
        $f3->expire(600); // 客户端缓存 10 分钟
        Ajax::header();
        $smarty->display('empty.tpl', $smartyCacheId);
    }

}
