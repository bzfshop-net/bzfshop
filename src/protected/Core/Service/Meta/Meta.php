<?php

/**
 *
 * @author QiangYu
 *
 * meta 表的操作
 *
 * meta 表用于存放一些自定义的类别或者词条，比如快递公司的列表
 *
 * */

namespace Core\Service\Meta;

use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;

class Meta extends \Core\Service\BaseService
{

    /**
     * 取得 meta 记录
     *
     * @param  int $id
     * @param int  $ttl
     *
     * @return object
     */
    public function loadMetaById($id, $ttl = 0)
    {
        return $this->_loadById('meta', 'meta_id = ?', $id, $ttl);
    }

    /**
     * 根据 ID 删除一个 记录
     *
     * @param int $id
     */
    public function removeMetaById($id)
    {
        $meta = $this->loadMetaById($id);
        if (!$meta->isEmpty()) {
            $meta->erase();
        }
    }

    /**
     * 通过 meta_key 取得 meta 数据
     *
     * @param string $meta_type
     * @param string $meta_key
     * @param int    $ttl  缓存时间
     *
     */
    public function loadMetaByTypeAndKey($meta_type, $meta_key, $ttl = 0)
    {

        if (empty($meta_type) || empty($meta_key)) {
            throw new \InvalidArgumentException('meta_type, meta_key can not be empty');
        }

        $dataMapper = new DataMapper('meta');
        $dataMapper->loadOne(array('meta_type = ? and meta_key = ?', $meta_type, $meta_key), null, $ttl);
        return $dataMapper;
    }

    /**
     * 取得一组 meta 值
     *
     * @param  string $meta_type
     * @param  string $meta_key
     * @param int     $ttl
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function fetchMetaArrayByTypeAndKey($meta_type, $meta_key, $ttl = 0)
    {
        if (empty($meta_type) || empty($meta_key)) {
            throw new \InvalidArgumentException('meta_type, meta_key can not be empty');
        }

        return $this->_fetchArray(
            'meta',
            '*',
            array(array('meta_type = ? and meta_key = ?', $meta_type, $meta_key)),
            array('order' => 'sort_order desc, meta_id asc'),
            0,
            0,
            $ttl
        );
    }

    /**
     * 删除一组 meta 记录
     *
     * @param string $meta_type
     * @param string $meta_key
     *
     * @throws \InvalidArgumentException
     */
    public function removeMetaArrayByTypeAndKey($meta_type, $meta_key)
    {
        if (empty($meta_type) || empty($meta_key)) {
            throw new \InvalidArgumentException('meta_type, meta_key can not be empty');
        }

        $dataMapper = new DataMapper('meta');
        $dataMapper->erase(array('meta_type = ? and meta_key = ?', $meta_type, $meta_key));
        unset($dataMapper);
    }

    /**
     * 通过 meta_name 取得 meta 数据
     *
     * @param string $meta_type
     * @param string $meta_name
     * @param int    $ttl  缓存时间
     *
     */
    public function loadMetaByTypeAndName($meta_type, $meta_name, $ttl = 0)
    {

        if (empty($meta_type) || empty($meta_name)) {
            throw new \InvalidArgumentException('meta_type, meta_name can not be empty');
        }

        $dataMapper = new DataMapper('meta');
        $dataMapper->loadOne(array('meta_type = ? and meta_name = ?', $meta_type, $meta_name), null, $ttl);
        return $dataMapper;
    }

    /**
     * 根据 meta_type 和 meta_id 取得唯一的值
     *
     * @param  string $meta_type
     * @param  int    $meta_id
     * @param int     $ttl
     *
     * @return DataMapper
     * @throws \InvalidArgumentException
     */
    public function loadMetaByTypeAndId($meta_type, $meta_id, $ttl = 0)
    {
        $meta_id = abs(intval($meta_id));
        if (empty($meta_type)) {
            throw new \InvalidArgumentException('meta_type can not be empty');
        }

        $dataMapper = new DataMapper('meta');
        $dataMapper->loadOne(array('meta_type = ? and meta_id = ?', $meta_type, $meta_id), null, $ttl);
        return $dataMapper;
    }
}
