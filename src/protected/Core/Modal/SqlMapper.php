<?php

/**
 * @author QiangYu
 *
 * 数据模型的封装，支持数据表名前缀
 *
 * */

namespace Core\Modal;

use Core\Cloud\CloudHelper;
use Core\Helper\Utility\Utils;

class SqlMapper extends \DB\SQL\Mapper
{

    protected
        //! 表名前缀
    static $tablePrefix;

    protected
        //! 数据库引擎
    static $dbEngine = null;

    public static function setTablePrefix($tablePrefix)
    {
        static::$tablePrefix = $tablePrefix;
    }

    /**
     * 主动设置额外的数据库引擎
     *
     * @param $dbEngine
     */
    public static function setDbEngine($dbEngine)
    {
        static::$dbEngine = $dbEngine;
    }

    /**
     * 取得数据库引擎，如果数据库引擎没有初始化，我们这里会做初始化
     */
    public static function getDbEngine()
    {

        if (null == static::$dbEngine) {

            global $f3;
            // 初始化全站数据库
            static::$tablePrefix = $f3->get('sysConfig[db_table_prefix]');
            // 获得云平台的数据库引擎
            $cloudModuleDb    = CloudHelper::getCloudModule(CloudHelper::CLOUD_MODULE_DB);
            static::$dbEngine = $cloudModuleDb->getDb();
        }

        return static::$dbEngine;
    }

    public static function tableName($tableName)
    {
        $tableName = trim($tableName);

        if (strstr($tableName, ' ')) {
            // 表名不应该有空格，如果有空格，则为特殊的查询，比如  tableA left join tableB ，就不能做任何处理
            return $tableName;
        }

        // 这里确保 DbEngine 初始化，这样我们才能取得 tablePrefix
        static::getDbEngine();
        return static::$tablePrefix . $tableName;
    }

    /**
     * 判断是否查询到了有效数据
     *
     * @return boolean
     *
     * */
    public function isEmpty()
    {
        return $this->dry();
    }

    /**
     * 把结果转化成数组形式
     *
     * @return 返回标准的 php 数组
     *
     * */
    public function toArray()
    {
        return $this->cast();
    }

    /**
     *  取得刚才运行的 Sql，方便调试
     *
     * @return 返回 SQL 日志
     *
     * */
    public function getSql()
    {
        $dbEngine = static::getDbEngine();
        return $dbEngine->log();
    }

    /**
     *  有时候我们需要做这样的查询  $dstTable->select('max(zone_id) as mvalue', null, null, 0);
     *  这个时候我们需要取得  mvalue 的值就需要用这个方法
     */
    public function getAdhocValue($key)
    {
        return $this->adhoc[$key]['value'];
    }

    /**
     * 返回当前的表名，注意：这里只适用于单表查询
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * 返回数据库，可以用于执行 exec()
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * 只返回一条记录
     *
     * @return 符合查询条件的第一条记录
     *
     * @param array $filter  查询条件，比如 array('name=? and age >?', 'xxx',18)
     * @param array $options 选项条件，比如 array('group'=>NULL,'order'=>NULL,'limit'=>0,    'offset'=>0)
     * @param int   $ttl     缓存时间
     * */
    function loadOne($filter = null, array $options = null, $ttl = 0)
    {
        if (!isset($options)) {
            $options = array();
        }

        /** 按照主键排序，确保每次的查询都是稳定的
         *
         *  一些数据库，比如 PostgreSql ，记录每更新一次，这条记录就会被移动到表的尾部，
         *  如果你 select * from table where ... limit 1
         *  比如你有 10 条记录，即使你的查询条件是一样的，但是每次返回的结果可能是不一样的，取决于哪条记录在表的前面
         *
         *  所以我们这里加上按照主键排序，确保每次查询结果都是一样的
         *
         * */
        if (!isset($options['order'])) {
            $orderBy = '';
            foreach ($this->fields as $key => $field) {
                if ($field['pkey']) {
                    $orderBy .= ($orderBy ? ' , ' : '') . $key . ' asc ';
                }
            }
            if ($orderBy) {
                $options += array('order' => $orderBy);
            }
        }

        $options += array('limit' => 1);
        return parent::load($filter, $options, $ttl);
    }

    /**
     * 支持从 数组 中复制相应字段
     *
     * @param mixed $src     可以是字符串，比如 'POST'，那么 $f3 会自动从 $_POST[] 复制，
     *                       也可以是 array(...)，自动从这个数组中复制
     * */
    public function copyFrom($src, $func = null)
    {
        global $f3;

        $randomKeyName = $src;
        if (is_array($src)) {
            $randomKeyName = md5(time() . 'copyFrom' . $this->table);
            $f3->set($randomKeyName, $src);
        }

        parent::copyfrom($randomKeyName, $func);

        if ($src != $randomKeyName) {
            $f3->clear($randomKeyName);
        }
    }

    /**
     * 多表查询的复杂方式
     *
     * @return array 普通的 PHP 数组
     *
     * @param array  $tableArray 表名列表，格式为 array('user','credit') 或者 array('user'=>'u','credit')
     * @param string $fields     字段列表，比如 'u.name, credit.money' 这里 u 和前面的 $tableArray 中的值对应
     * @param array  $filter     查询条件，比如 array('u.user = ?','xxx')
     * @param array  $options    比如 array('group' => 'gender', 'order'=>'age desc','limit' => 10)
     * @param int    $ttl        查询结果缓存多少时间
     */
    function selectComplex(array $tableArray, $fields = '*', $filter = null, array $options = null, $ttl = 0)
    {
        if (!$options) {
            $options = array();
        }
        $options += array(
            'group'  => null,
            'order'  => null,
            'limit'  => 0,
            'offset' => 0
        );
        $sql = 'SELECT ' . $fields . ' FROM ';

        // build table
        $tableNameArray = array();
        foreach ($tableArray as $key => $value) {
            if (is_string($key)) {
                $tableNameArray[] = static::tableName($key) . ' ' . $value;
                continue;
            }
            $tableNameArray[] = static::tableName($value);
        }
        $sql .= implode(',', $tableNameArray);

        $args = array();
        if ($filter) {
            if (is_array($filter)) {
                $args = isset($filter[1]) && is_array($filter[1])
                    ?
                    $filter[1]
                    :
                    array_slice($filter, 1, null, true);
                $args = is_array($args) ? $args : array(1 => $args);
                list($filter) = $filter;
            }
            $sql .= ' WHERE ' . $filter;
        }
        if ($options['group']) {
            $sql .= ' GROUP BY ' . $options['group'];
        }
        if ($options['order']) {
            $sql .= ' ORDER BY ' . $options['order'];
        }
        if ($options['limit']) {
            $sql .= ' LIMIT ' . $options['limit'];
        }
        if ($options['offset']) {
            $sql .= ' OFFSET ' . $options['offset'];
        }

        $dbEngine = static::getDbEngine();
        return $dbEngine->exec($sql . ';', $args, $ttl);
    }

    /**
     * 多表查询的复杂方式，返回记录数
     *
     * @return int 返回记录数
     *
     * @param array $tableArray 表名列表，格式为 array('user','credit') 或者 array('user'=>'u','credit')
     * @param array $filter     查询条件，比如 array('u.user = ?','xxx')
     * @param array $options    目前不支持 having 查询，留待以后扩展
     * @param int   $ttl        查询结果缓存多少时间
     */
    public function selectCount(array $tableArray, $filter = null, $options = null, $ttl = 0)
    {
        list($result) = $this->selectComplex($tableArray, ' count(1) as rows ', $filter, $options, $ttl);
        $out = $result['rows'];
        unset($this->adhoc['rows']);
        return $out;
    }

    /**
     *
     * @param mixed $table 数据表的名字，如果是单个表，可以是 'user'，多个表可以是 array('user', 'user_info' => 'ui')
     * @param int   $ttl   缓存多少秒
     *
     * */
    function __construct($table, $ttl = 600)
    {
        $dbEngine = static::getDbEngine();

        // 检查数据库连接
        if (Utils::isEmpty($dbEngine)) {
            throw new \InvalidArgumentException('can not find dbEngine');
        }

        if (null == $table) {
            return parent::__construct($dbEngine, null, null, $ttl);
        }

        // 多表联合查询
        if (is_array($table)) {
            $tableArray = array();
            foreach ($table as $key => $value) {
                if (is_string($key)) {
                    $tableArray[] = static::tableName($key);
                    continue;
                }
                $tableArray[] = static::tableName($value);
            }
            return parent::__construct($dbEngine, $tableArray, null, $ttl);
        }

        // 单表查询
        if (is_string($table)) {
            parent::__construct($dbEngine, static::tableName($table), null, $ttl);
        }

    }

}
