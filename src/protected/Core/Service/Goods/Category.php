<?php

/**
 * @author QiangYu
 *
 * 基本的商品分类处理，我们采用 Meta 来保存 Tree 结构
 *
 * 其中 meta_data 的数据结构为
 *
 * array(
 *      // 属性过滤 设置
 *      filterArray : array(
 *          array(typeId => 12, attrItemId => 20),
 *          array(typeId => 18, attrItemId => 21),
 *      )
 * )
 *
 * */

namespace Core\Service\Goods;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;
use Core\Service\Meta\Meta as MetaBasicService;

class Category extends MetaBasicService
{
    const META_TYPE = 'goods_category';

    /**
     * 取得分类自身的信息
     *
     * @return object 分类的信息
     *
     * @param int $categoryId 分类的 ID
     * @param int $ttl 缓存时间
     */
    public function loadCategoryById($categoryId, $ttl = 0)
    {
        $meta = $this->loadMetaById($categoryId, $ttl);

        // 检查 category 是否合法
        if (!$meta->isEmpty()) {
            if (self::META_TYPE != $meta['meta_type']) {
                throw new \InvalidArgumentException('categoryId[' . $categoryId . '] is illegal');
            }
        }

        return $meta;
    }

    /**
     * 新建或者更新一个商品分类，categoryId 为 0 则新建
     *
     * @param  int $categoryId
     * @param  int $parentId
     * @param  string $name
     * @param string $desc
     * @param string $data
     * @param int $sortOrder
     * @param int $status
     *
     * @return object
     */
    public function saveCategoryById(
        $categoryId,
        $parentId,
        $name,
        $desc = null,
        $data = null,
        $sortOrder = 0,
        $status = 1
    )
    {
        $meta                  = $this->loadCategoryById($categoryId);
        $meta->meta_type       = self::META_TYPE;
        $meta->parent_meta_id  = $parentId;
        $meta->meta_name       = $name;
        $meta->meta_desc       = $desc;
        $meta->meta_data       = $data;
        $meta->meta_sort_order = $sortOrder;
        $meta->meta_status     = $status;
        $meta->save();
        return $meta;
    }

    /**
     * 取得某个父分类下面的子分类，确实不取出不允许显示的分类
     *
     * @return array 分类列表，格式 array(array(列表详情), array(列表详情) ...)
     *
     * @param int $parentId 父类的 ID，用 0 表示取得顶级分类
     * @param bool $showAll 是否把不允许显示的分类也去取出来
     * @param int $ttl 缓存时间
     */
    public function fetchCategoryArray($parentId, $showAll = false, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('parentId' => $parentId));
        $parentId  = $validator->digits()->min(0)->validate('parentId');
        $this->validate($validator);

        return $this->_fetchArray(
            'meta',
            '*',
            array(
                array(
                    'meta_type = ? and parent_meta_id = ? ' . ($showAll ? '' : 'and meta_status = 1'),
                    self::META_TYPE,
                    $parentId
                )
            ),
            array('order' => 'meta_sort_order desc, meta_id desc'),
            0,
            0,
            $ttl
        );
    }

    // 递归建立子节点
    public function buildChildArrayCategory(&$treeNodeArray, $parentIdToMetaArray)
    {
        foreach ($treeNodeArray as &$treeNodeItem) {

            if (isset($parentIdToMetaArray[$treeNodeItem['meta_id']])) {
                $treeNodeItem['child_list'] = $parentIdToMetaArray[$treeNodeItem['meta_id']];
            }

            if (isset($treeNodeItem['child_list'])) {
                $this->buildChildArrayCategory($treeNodeItem['child_list'], $parentIdToMetaArray);
            }
        }
    }

    /**
     * 取得父节点以下整个树形结构
     *
     * @param int $parentId
     * @param bool $showAll 是否取得所有节点？ 如果否，则只取允许显示的节点
     * @param int $ttl
     *
     * @return array
     */
    public function fetchCategoryTreeArray($parentId, $showAll = false, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('parentId' => $parentId));

        $parentId = $validator->digits()->min(0)->validate('parentId');
        $parentId = $parentId ? : 0;
        $this->validate($validator);

        // 获取整个树的节点
        $treeNodeArray = $this->_fetchArray(
            'meta',
            '*',
            array(
                array(
                    'meta_type = ? ' . ($showAll ? '' : 'and meta_status = 1'),
                    self::META_TYPE
                )
            ),
            array('order' => 'meta_sort_order desc, meta_id desc'),
            0,
            0,
            $ttl
        );

        if (empty($treeNodeArray)) {
            // 没有数据，返回空数组
            return array();
        }

        // 建立 parentId --> array(meta)
        $parentIdToMetaArray = array();
        foreach ($treeNodeArray as $treeNodeItem) {
            if (!isset($parentIdToMetaArray[$treeNodeItem['parent_meta_id']])) {
                $parentIdToMetaArray[$treeNodeItem['parent_meta_id']] = array();
            }
            $parentIdToMetaArray[$treeNodeItem['parent_meta_id']][] = $treeNodeItem;
        }

        if (empty($parentIdToMetaArray[$parentId])) {
            // 没有这一层级的数据，返回空数组
            return array();
        }

        $this->buildChildArrayCategory($parentIdToMetaArray[$parentId], $parentIdToMetaArray);

        out:
        return $parentIdToMetaArray[$parentId];
    }

    private function getCategoryId(&$categoryIdArray, $categoryArray, $maxLevel, $currentLevel)
    {
        if ($currentLevel >= $maxLevel) {
            return;
        }

        foreach ($categoryArray as $categoryItem) {
            $categoryIdArray[] = $categoryItem['meta_id'];
            if (!empty($categoryItem['child_list'])) {
                // 递归取得数据
                $this->getCategoryId($categoryIdArray, $categoryItem['child_list'], $maxLevel, $currentLevel + 1);
            }
        }
    }

    /**
     * 取得某个分类下面所有子分类的 ID 列表
     *
     * @return array 子分类 ID 列表，格式 array(10,13,15,...)
     *
     * @param int $parentId 父类的 ID，用 0 表示取得顶级分类
     * @param int $level 取得多少层，子分类有可能很深，我们只取有限层次
     * @param int $ttl 缓存时间
     */
    public function fetchCategoryChildrenIdArray($parentId, $level = 1, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('parentId' => $parentId, 'level' => $level, 'ttl' => $ttl));
        $parentId  = $validator->digits()->min(0)->validate('parentId');
        $level     = $validator->digits()->min(1)->validate('level');
        $ttl       = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        // 取得树形结构
        $categoryTreeArray = $this->fetchCategoryTreeArray($parentId, false, $ttl);
        $categoryIdArray   = array();

        // 递归取数据
        $this->getCategoryId($categoryIdArray, $categoryTreeArray, $level, 0);

        // 返回去除重复的数据
        return array_unique($categoryIdArray);
    }

    /**
     * 根据一组 category 的 id 值取得分类信息
     *
     * @param  array $categoryIdArray
     * @param int $ttl
     *
     * @return array
     */
    public function fetchCategoryArrayByIdArray($categoryIdArray, $ttl = 0)
    {

        return $this->_fetchArray(
            'meta',
            '*',
            array(
                array('meta_type = ? and meta_status = 1', self::META_TYPE),
                array(QueryBuilder::buildInCondition('meta_id', $categoryIdArray))
            ),
            null,
            0,
            0,
            $ttl
        );
    }

    /**
     * 取得分类下面对应的商品描述列表
     * 由于这是为分类列表用的，所以我们尽量取少的数据，不是整个商品详情都取
     * 少 select 一些字段有利于提高性能
     *
     * @return array 商品列表，格式 array(array(商品详情), array(商品详情) ...)
     *
     * @param int $categoryId 分类的ID
     * @param int $level 取得多少层，子分类有可能很深，我们只取有限层次
     * @param int $offset 从什么地方开始取商品
     * @param string $systemTag 系统标记
     * @param int $limit 限制一次取得多少个商品
     * @param int $ttl 缓存时间
     */
    public function fetchGoodsArray($categoryId, $level, $systemTag, $offset = 0, $limit = 10, $ttl = 0)
    {
        // 参数验证
        $validator  = new Validator(array(
            'categoryId' => $categoryId,
            'level'      => $level,
            'systemTag'  => $systemTag,
            'offset'     => $offset,
            'limit'      => $limit,
            'ttl'        => $ttl
        ));
        $categoryId = $validator->digits()->min(0)->validate('categoryId');
        $level      = $validator->required()->digits()->min(1)->validate('level');
        $systemTag  = $validator->validate('systemTag');
        $offset     = $validator->digits()->min(0)->validate('offset');
        $limit      = $validator->digits()->min(1)->validate('limit');
        $ttl        = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        $childrenIdArray   = $this->fetchCategoryChildrenIdArray($categoryId, $level, $ttl);
        $childrenIdArray[] = $categoryId; // 加入父节点

        $queryCondArray   = array();
        $queryCondArray[] = array('is_delete = 0 AND is_on_sale = 1 AND is_alone_sale = 1');
        // 构建 SQL 的 in 语句， cat_id in (100,20,30)
        $queryCondArray[] = array(QueryBuilder::buildInCondition('cat_id', $childrenIdArray, \PDO::PARAM_INT));
        if (!empty($systemTag)) {
            $queryCondArray[] = array('system_tag_list like ? ', '%' . Utils::makeTagString(array($systemTag)) . '%');
        }

        $dataMapper = new DataMapper('goods');
        return $dataMapper->select(
            ' goods_id, cat_id, goods_sn, goods_name, brand_id, goods_number, market_price, shop_price, promote_price, '
            . ' promote_start_date, promote_end_date, '
            . ' is_real, is_shipping, sort_order, goods_type, suppliers_id ',
            // fields
            QueryBuilder::buildAndFilter($queryCondArray), // filter
            array(
                'order'  => 'sort_order desc, goods_id desc',
                'offset' => $offset,
                'limit'  => $limit
            ), // options
            $ttl
        );
    }

    /**
     * 取得对应分类下面商品的总数，用于分页显示
     *
     * @return int 商品总数
     *
     * @param int $categoryId 分类的ID
     * @param int $level 取得多少层，子分类有可能很深，我们只取有限层次
     * @param string $systemTag 系统标记
     * @param int $ttl 缓存时间
     */
    public function countGoodsArray($categoryId, $level, $systemTag, $ttl = 0)
    {
        // 参数验证
        $validator  = new Validator(array(
            'categoryId' => $categoryId,
            'level'      => $level,
            'systemTag'  => $systemTag,
            'ttl'        => $ttl
        ));
        $categoryId = $validator->digits()->min(0)->validate('categoryId');
        $level      = $validator->required()->digits()->min(1)->validate('level');
        $systemTag  = $validator->validate('systemTag');
        $ttl        = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        $childrenIdArray   = $this->fetchCategoryChildrenIdArray($categoryId, $level, $ttl);
        $childrenIdArray[] = $categoryId; // 加入父节点

        $queryCondArray   = array();
        $queryCondArray[] = array('is_delete = 0 AND is_on_sale = 1 AND is_alone_sale = 1');
        // 构建 SQL 的 in 语句， cat_id in (100,20,30)
        $queryCondArray[] = array(QueryBuilder::buildInCondition('cat_id', $childrenIdArray, \PDO::PARAM_INT));
        if (!empty($systemTag)) {
            $queryCondArray[] = array('system_tag_list like ? ', '%' . Utils::makeTagString(array($systemTag)) . '%');
        }

        $dataMapper = new DataMapper('goods');
        return $dataMapper->count(
            QueryBuilder::buildAndFilter($queryCondArray), // filter
            null,
            $ttl
        );
    }

    /**
     * 把商品从一个分类全部转移到另外一个分类
     *
     * @param int $oldCategoryId
     * @param int $newCategoryId
     */
    public function transferGoodsToNewCategory($oldCategoryId, $newCategoryId)
    {
        // 参数验证
        $validator     = new Validator(array('oldCategoryId' => $oldCategoryId, 'newCategoryId' => $newCategoryId));
        $oldCategoryId = $validator->digits()->min(1)->validate('oldCategoryId');
        $newCategoryId = $validator->digits()->min(1)->validate('newCategoryId');

        // 更新商品的分类 id
        $dbEngine = DataMapper::getDbEngine();
        $dbEngine->exec(
            'update ' . DataMapper::tableName('goods') . ' set cat_id = ? where cat_id = ?',
            array(1 => $newCategoryId, $oldCategoryId)
        );
    }


    /**
     * 统计每个商品分类有多少商品，不计算子分类的商品数量（比如 A 下面有 B, C 分类，
     * 这里计算的 A 包含的商品数量不计算 B, C 的在内）
     *
     * @param int $ttl 缓存时间
     *
     * @return array
     * 格式  array(array(cat_id, goods_count), ...)
     */
    public function calcCategoryGoodsCount($ttl = 0)
    {
        $dbEngine = DataMapper::getDbEngine();
        return $dbEngine->exec(
            'select cat_id, count(1) as goods_count from ' . DataMapper::tableName('goods') . ' group by cat_id ',
            null,
            $ttl
        );
    }
}
