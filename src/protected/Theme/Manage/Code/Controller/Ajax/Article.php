<?php

/**
 * @author QiangYu
 *
 * 文章的 ajax 操作
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Article\Category as CategoryService;

class Article extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    /**
     * 列出所有文章分类
     *
     * @param $f3
     */
    public function ListCategory($f3)
    {
        // 检查缓存
        $cacheKey = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);

        if ($f3->get('GET[nocache]')) {
            goto nocache;
        }

        $categoryArray = $f3->get($cacheKey);
        if (!empty($categoryArray)) {
            goto out;
        }

        nocache: // 没有缓存数据

        $categoryService = new CategoryService();
        $categoryArray   = $categoryService->fetchArticleCategoryArray();

        $f3->set($cacheKey, $categoryArray, 300); //缓存 5 分钟

        out:
        if (!$f3->get('GET[nocache]')) {
            $f3->expire(60); // 客户端缓存 1 分钟
        }
        Ajax::header();
        echo Ajax::buildResult(null, null, $categoryArray);
    }

}
