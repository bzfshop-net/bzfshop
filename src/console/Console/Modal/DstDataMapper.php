<?php

/**
 * @author QiangYu
 *
 * 数据迁移时候用于 目标数据
 *
 * */

namespace Console\Modal;

class DstDataMapper extends \Core\Modal\SqlMapper
{
    // 覆盖掉父类的定义
    protected
        //! 表名前缀
    static $tablePrefix;

    protected
        //! 数据库引擎
    static $dbEngine = null;

    public static function getDbEngine()
    {

        if (null == static::$dbEngine) {

            global $f3;
            // 初始化全站数据库
            static::$tablePrefix = $f3->get('sysConfig[dst_db_table_prefix]');
            static::$dbEngine    = new \Core\Modal\DbEngine($f3->get('sysConfig[dst_db_pdo]'),
                $f3->get('sysConfig[dst_db_username]'), $f3->get('sysConfig[dst_db_password]'));
        }

        return static::$dbEngine;
    }

}
