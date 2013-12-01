<?php

/**
 * @author QiangYu
 *
 * 加载快递公司列表
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Service\Meta\Express as ExpressService;

class Express extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    public function ListExpress($f3)
    {
        // 检查缓存
        $cacheKey     = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);
        $expressArray = $f3->get($cacheKey);
        if (!empty($expressArray)) {
            goto out;
        }

        $expressService = new ExpressService();
        $expressArray   = $expressService->fetchAvailableExpressArray();

        $f3->set($cacheKey, $expressArray, 300); //缓存 5 分钟

        out:
        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $expressArray);
    }

}
