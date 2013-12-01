<?php

/**
 * @author QiangYu
 *
 * 文章列表、编辑
 *
 * */

namespace Controller\Article;

use Core\Cache\ClearHelper;
use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Request;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Search\SearchHelper;
use Core\Service\Article\Article as ArticleService;
use Core\Service\Article\Category as ArticleCategoryService;

class Article extends \Controller\AuthController
{

    public function Search($f3)
    {
        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        // 搜索参数数组
        $searchFormQuery                    = array();
        $searchFormQuery['a.article_id']    =
            $validator->digits()->min(0)->filter('ValidatorIntValue')->validate('article_id');
        $searchFormQuery['a.title']         = $validator->validate('title');
        $searchFormQuery['a.description']   = $validator->validate('description');
        $searchFormQuery['a.cat_id']        =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('cat_id');
        $searchFormQuery['a.admin_user_id'] =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('admin_user_id');
        $searchFormQuery['a.is_open']       =
            $validator->digits()->min(0)->filter('ValidatorIntValue')->validate('is_open');

        if (!$this->validate($validator)) {
            goto out;
        }

        // 建立查询条件
        $searchParamArray = QueryBuilder::buildSearchParamArray($searchFormQuery);

        // 查询商品列表
        $totalCount = SearchHelper::count(SearchHelper::Module_Article, $searchParamArray);
        if ($totalCount <= 0) { // 没数据，可以直接退出了
            goto out;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Article/Article/Search');
        }

        // 文章列表
        $articleArray = SearchHelper::search(
            SearchHelper::Module_Article,
            '*',
            $searchParamArray,
            array(array('a.article_id', 'desc')),
            $pageNo * $pageSize,
            $pageSize
        );

        // 取得 文章分类 id
        $categoryIdArray = array();
        foreach ($articleArray as $articleItem) {
            $categoryIdArray[] = $articleItem['cat_id'];
        }

        $categoryIdArray = array_unique($categoryIdArray);

        // 取得分类信息
        $articleCategoryService = new ArticleCategoryService();
        $categoryArray          = $articleCategoryService->fetchCategoryArrayByIdArray($categoryIdArray);

        // 建立 cat_id  ---> cateogry 信息的反查表
        $categoryIdToCategoryArray = array();
        foreach ($categoryArray as $categoryItem) {
            $categoryIdToCategoryArray[$categoryItem['meta_id']] = $categoryItem;
        }

        // 放入分类信息
        foreach ($articleArray as &$articleItem) {
            if (isset($categoryIdToCategoryArray[$articleItem['cat_id']])) {
                // 很老的商品，分类信息可能已经不存在了
                $articleItem['cat_name'] = $categoryIdToCategoryArray[$articleItem['cat_id']]['meta_name'];
            }
        }
        unset($articleItem);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('articleArray', $articleArray);

        out:
        $smarty->display('article_article_search.tpl');
    }

    public function Edit($f3)
    {
        global $smarty;

        // 参数验证
        $validator  = new Validator($f3->get('GET'));
        $article_id = $validator->digits()->min(0)->filter('ValidatorIntValue')->validate('article_id');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 取得文章
        $articleService = new ArticleService();
        $article        = $articleService->loadArticleById($article_id);

        if ($article_id > 0 && $article->isEmpty()) {
            $this->addFlashMessage('文章ID[' . $article_id . ']非法');
            goto out_fail;
        }

        // 只是显示文章内容而已
        if (Request::isRequestGet()) {
            $smarty->assign('article', $article->toArray());
            goto out_get;
        }

        // 权限检查
        $this->requirePrivilege('manage_article_article_edit');

        // 从这里开始是修改文章内容
        unset($validator);
        $articleInfoArray = $f3->get('POST[article]');
        $validator        = new Validator($articleInfoArray);

        // 获得修改数据
        $inputArray                = array();
        $inputArray['title']       = $validator->required()->validate('title');
        $inputArray['seo_keyword'] = $validator->validate('seo_keyword');
        $inputArray['cat_id']      = $validator->validate('cat_id');
        $inputArray['is_open']     = $validator->validate('is_open');
        $inputArray['description'] = $validator->validate('description');
        $inputArray['content']     = $articleInfoArray['content']; // 不要过滤 html

        if (!$this->validate($validator)) {
            goto out_get;
        }

        $authAdminUser = AuthHelper::getAuthUser();

        // 新建文章
        if ($article_id <= 0) {
            $inputArray['admin_user_id']   = $authAdminUser['user_id'];
            $inputArray['admin_user_name'] = $authAdminUser['user_name'];
            $inputArray['add_time']        = Time::gmTime();
        }

        // 文章更新
        $inputArray['update_user_id']   = $authAdminUser['user_id'];
        $inputArray['update_user_name'] = $authAdminUser['user_name'];
        $inputArray['update_time']      = Time::gmTime();

        // 保存修改
        $article->copyFrom($inputArray);
        $article->save();

        // 清除文章缓存
        ClearHelper::clearArticleCacheById($article->article_id);

        $this->addFlashMessage('文章保存成功');

        RouteHelper::reRoute(
            $this,
            RouteHelper::makeUrl('/Article/Article/Edit', array('article_id' => $article->article_id), true)
        );
        return; // POST 从这里退出

        out_get: // GET 从这里退出
        $smarty->display('article_article_edit.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Article/Article/Search');
    }

    public function Create($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_article_article_edit');

        global $smarty;

        $articleService = new ArticleService();
        $article        = $articleService->loadArticleById(0);

        $smarty->assign('article', $article->toArray());
        $smarty->display('article_article_create.tpl');
    }
}
