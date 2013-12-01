<?php

/**
 *
 * @author QiangYu
 *
 * 文章 操作
 *
 * */

namespace Core\Service\Article;

use Core\Helper\Utility\Validator;

class Article extends \Core\Service\BaseService
{

    /**
     * @param int $article_id  文章 ID
     * @param int $ttl
     *
     * @return object
     */
    public function loadArticleById($article_id, $ttl = 0)
    {
        return $this->_loadById('article', 'article_id = ?', $article_id, $ttl);
    }

}
