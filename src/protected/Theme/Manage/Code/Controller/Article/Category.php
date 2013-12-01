<?php

/**
 * @author QiangYu
 *
 * 文章分类 管理
 *
 * */

namespace Controller\Article;

use Core\Helper\Utility\Request;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Article\Category as ArticleCategoryService;

class Category extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;

        $articleCategoryService = new ArticleCategoryService();
        $articleCateogryArray   = $articleCategoryService->fetchArticleCategoryArray();

        $smarty->assign('articleCateogryArray', $articleCateogryArray);

        $smarty->display('article_category.tpl');
    }

    public function Edit($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_article_category_edit');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id   = $validator->digits()->validate('meta_id');
        $meta_id   = $meta_id ? : 0;

        //  加载 分类信息
        $articleCategoryService = new ArticleCategoryService();
        $articleCategory        = $articleCategoryService->loadArticleCategoryById($meta_id);

        if (Request::isRequestGet()) {
            goto out_assign;
        }

        // 安全性检查
        if ($meta_id > 0) {
            if ($articleCategory->isEmpty()
                || ArticleCategoryService::META_TYPE != $articleCategory->meta_type
            ) {
                $this->addFlashMessage('非法ID[' . $meta_id . ']');
                goto out;
            }
        }

        unset($validator);
        $validator                     = new Validator($f3->get('POST'));
        $inputArray                    = array();
        $inputArray['meta_type']       = ArticleCategoryService::META_TYPE;
        $inputArray['meta_name']       = $validator->required()->validate('meta_name');
        $inputArray['meta_sort_order'] = $validator->digits()->validate('meta_sort_order');
        $inputArray['meta_desc']       = $validator->validate('meta_desc');

        if (!$this->validate($validator)) {
            goto out;
        }

        // 保存
        $articleCategory->copyFrom($inputArray);
        $articleCategory->save();

        $this->addFlashMessage('分类信息保存成功');

        // POST 成功从这里退出
        RouteHelper::reRoute(
            $this,
            RouteHelper::makeUrl('/Article/Category/Edit', array('meta_id' => $articleCategory->meta_id), true)
        );
        return;

        out_assign:
        $smarty->assign($articleCategory->toArray());

        out:
        $smarty->display('article_category_edit.tpl');
    }
}
