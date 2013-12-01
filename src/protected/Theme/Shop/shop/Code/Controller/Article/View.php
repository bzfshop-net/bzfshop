<?php

/**
 * @author QiangYu
 *
 * 查看文章内容
 *
 * */

namespace Controller\Article;

use Core\Cache\GoodsGalleryCache;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Service\Article\Article as ArticleBasicService;

class View extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        // 首先做参数合法性验证
        $validator  = new Validator($f3->get('GET'));
        $article_id =
            $validator->required('文章id不能为空')->digits('文章id非法')->min(1, true, '文章id非法')->validate('article_id');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 生成 smarty 的缓存 id
        $smartyCacheId = 'Article|' . $article_id . '|View';

        // 开启并设置 smarty 缓存时间
        enableSmartyCache(true, bzf_get_option_value('smarty_cache_time_article_view'));

        if ($smarty->isCached('article_view.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // 查询文章信息
        $articleService = new ArticleBasicService();
        $articleInfo    = $articleService->loadArticleById($article_id);

        // 文章不存在，退出
        if ($articleInfo->isEmpty() || !$articleInfo->is_open) {
            $this->addFlashMessage('文章 [' . $article_id . '] 不存在');
            goto out_fail;
        }

        // 设置文章页面的 SEO 信息
        $smarty->assign(
            'seo_title',
            $articleInfo['title'] . ',' . $f3->get('sysConfig[site_name]')
        );
        $smarty->assign('seo_description', $articleInfo['description']);
        $smarty->assign('seo_keywords', $articleInfo['seo_keyword']);

        // 给模板赋值
        $smarty->assign('articleInfo', $articleInfo);

        out_display:
        $smarty->display('article_view.tpl', $smartyCacheId);
        return;

        out_fail: // 失败从这里返回
        RouteHelper::reRoute($this, '/'); // 返回首页        
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
