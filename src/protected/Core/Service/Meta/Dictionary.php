<?php

/**
 *
 * @author QiangYu
 *
 * 字典服务，用于把一些词条转换成可以显示的内容
 *
 * 比如 TUAN360CPS  ----转换---> 360CPS 这样更利于显示
 *
 * */

namespace Core\Service\Meta;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Validator;

class Dictionary extends Meta
{
    const META_TYPE = 'dictionary';

    /**
     * 取得一个词的数据
     *
     * @param  string   $word
     * @param int       $ttl
     *
     * @return array
     *
     * 格式为 array('word' => $word, 'name' => '用于显示的名字', 'desc' => '解释描述', 'data' => '额外数据')
     *
     */
    public function getWord($word, $ttl = 0)
    {
        $meta = $this->loadMetaByTypeAndKey(Dictionary::META_TYPE, $word, $ttl);
        if ($meta->isEmpty()) {
            return array('word' => $word, 'name' => $word, 'desc' => $word, 'data' => '');
        }
        return array(
            'word' => $word,
            'name' => $meta->meta_name,
            'desc' => $meta->meta_desc,
            'data' => $meta->meta_data
        );
    }

    /**
     * 根据一组词返回一组词条
     *
     * @param  array   $wordArray   例如： array('360cps','yiqifacps',...)
     * @param int      $ttl
     *
     * @return array
     *
     * 格式为 array(
     *          array('word' => $word, 'name' => '用于显示的名字', 'desc' => '解释描述', 'data' => '额外数据'),
     *          array('word' => $word, 'name' => '用于显示的名字', 'desc' => '解释描述', 'data' => '额外数据'),
     *      )
     */
    public function getWordArray($wordArray, $ttl = 0)
    {
        $inCond      = QueryBuilder::buildInCondition('meta_key', $wordArray, \PDO::PARAM_STR);
        $queryResult = $this->_fetchArray(
            'meta',
            '*',
            array(array('meta_type = ? ', Dictionary::META_TYPE), array($inCond)),
            array('order' => 'meta_sort_order desc, meta_id desc'),
            0,
            0,
            $ttl
        );

        // 建立 word --> 记录 的倒查表
        $wordToMetaArray = array();
        foreach ($queryResult as $resultItem) {
            $wordToMetaArray[$resultItem['meta_key']] = $resultItem;
        }

        // 构造最后的查询结果
        $wordDict = array();
        foreach ($wordArray as $word) {
            if (array_key_exists($word, $wordToMetaArray)) {
                $meta       = $wordToMetaArray[$word];
                $wordDict[] = array(
                    'word' => $word,
                    'name' => $meta['meta_name'],
                    'desc' => $meta['meta_desc'],
                    'data' => $meta['meta_data']
                );
            } else {
                $wordDict[] = array('word' => $word, 'name' => $word, 'desc' => $word, 'data' => '');
            }
        }

        return $wordDict;
    }


    /**
     * 更新或者新建一个词条
     *
     * @param string $word
     * @param string $name
     * @param string $desc
     * @param string $data
     *
     * @return \Core\Modal\SqlMapper
     */
    public function saveWord($word, $name, $desc, $data)
    {
        $meta            = $this->loadMetaByTypeAndKey(Dictionary::META_TYPE, $word);
        $meta->meta_type = Dictionary::META_TYPE;
        $meta->meta_key  = $word;
        $meta->meta_name = $name;
        $meta->meta_desc = $desc;
        $meta->meta_data = $data;
        $meta->save();

        return $meta;
    }

    /**
     * 删除一个词条
     *
     * @param string $word
     */
    public function removeWord($word)
    {
        $meta = $this->loadMetaByTypeAndKey(Dictionary::META_TYPE, $word);
        if (!$meta->isEmpty()) {
            $meta->erase();
        }
    }
}
