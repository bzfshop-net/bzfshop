<?php

/**
 * @author QiangYu
 *
 * 角色列表
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Service\Meta\Role as MetaRoleService;

class Role extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    public function ListRole($f3)
    {
        // 检查缓存
        $cacheKey  = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);
        $roleArray = $f3->get($cacheKey);
        if (!empty($roleArray)) {
            goto out;
        }

        $metaRoleService = new MetaRoleService();
        $roleArray       = $metaRoleService->fetchRoleArray();

        $f3->set($cacheKey, $roleArray, 300); //缓存 5 分钟

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, $roleArray);
    }

}
