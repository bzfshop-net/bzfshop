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

class AdminUser extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    /**
     * 列出管理员
     *
     * @param $f3
     */
    public function ListUserIdName($f3)
    {

        // 检查缓存
        $cacheKey   = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);
        $adminArray = $f3->get($cacheKey);
        if (!empty($adminArray)) {
            goto out;
        }

        $baseService = new BaseService();
        $adminArray  =
            $baseService->_fetchArray(
                'admin_user',
                'user_id,user_name',
                array(array('disable = 0')),
                array('order' => 'user_id desc'),
                0,
                0,
                0
            );

        $f3->set($cacheKey, $adminArray, 300); //缓存 5 分钟

        out:
        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $adminArray);
    }

    /**
     * 列出在线客服
     *
     * @param $f3
     */
    public function ListKefuIdName($f3)
    {

        // 检查缓存
        $cacheKey   = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);
        $adminArray = $f3->get($cacheKey);
        if (!empty($adminArray)) {
            goto out;
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

        $f3->set($cacheKey, $adminArray, 300); //缓存 5 分钟

        out:
        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $adminArray);
    }

}
