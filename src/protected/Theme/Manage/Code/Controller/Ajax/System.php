<?php

/**
 * @author QiangYu
 *
 * 角色列表
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Plugin\PluginHelper;

class System extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    public function ListSystem($f3)
    {
        // 检查缓存
        $cacheKey  = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);
        $roleArray = $f3->get($cacheKey);
        if (!empty($roleArray)) {
            goto out;
        }

        $systemArray = array(
            array('system_tag' => PluginHelper::SYSTEM_SHOP, 'system_name' => '商城'),
            array('system_tag' => PluginHelper::SYSTEM_MOBILE, 'system_name' => '移动'),
        );

        out:
        $f3->expire(600); // 让客户端缓存 10 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $systemArray);
    }

}
