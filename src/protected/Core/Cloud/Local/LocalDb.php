<?php

/**
 * @author QiangYu
 *
 * 普通的数据库实现
 *
 * */

namespace Core\Cloud\Local;

use Core\Cloud\ICloudDb;

class LocalDb extends \Prefab implements ICloudDb
{

    private static $dbEngine = null;

    public function initDb($isWrite = true)
    {
        return true;
    }

    public function getDb($isWrite = true)
    {
        if (!self::$dbEngine) {
            global $f3;
            self::$dbEngine = new \Core\Modal\DbEngine($f3->get('sysConfig[db_pdo]'),
                $f3->get('sysConfig[db_username]'), $f3->get('sysConfig[db_password]'));
        }

        return self::$dbEngine;
    }
}