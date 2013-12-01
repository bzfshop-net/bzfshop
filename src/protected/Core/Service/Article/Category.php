<?php

/**
 *
 * @author QiangYu
 *
 * 文章的分类，采用 meta 实现
 *
 * */

namespace Core\Service\Article;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Validator;
use Core\Service\Meta\Meta;

class Category extends Meta
{
    const META_TYPE = 'article_category';

    /**
     * @param  int $meta_id   分类的 id
     * @param int  $ttl
     *
     * @return object
     * @throws \InvalidArgumentException
     */
    public function loadArticleCategoryById($meta_id, $ttl = 0)
    {
        $meta = $this->loadMetaById($meta_id, $ttl);

        // 检查 category 是否合法
        if (!$meta->isEmpty()) {
            if (self::META_TYPE != $meta['meta_type']) {
                throw new \InvalidArgumentException('categoryId[' . $meta_id . '] is illegal');
            }
        }

        return $meta;
    }

    /**
     * 取得所有的文章分类
     *
     * @param int $ttl
     *
     * @return array
     */
    public function fetchArticleCategoryArray($ttl = 0)
    {
        return $this->_fetchArray(
            'meta',
            '*',
            array(
                 array('meta_type = ?', self::META_TYPE)
            ),
            array('order' => 'meta_sort_order desc, meta_id desc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 根据一组ID取得对应的分类
     *
     * @param  array $categoryIdArray
     * @param int    $ttl
     *
     * @return array
     */
    public function fetchCategoryArrayByIdArray($categoryIdArray, $ttl = 0)
    {
        return $this->_fetchArray(
            'meta',
            '*',
            array(
                 array('meta_type = ?', self::META_TYPE),
                 array(QueryBuilder::buildInCondition('meta_id', $categoryIdArray))
            ),
            null,
            0,
            0,
            $ttl
        );
    }

}
