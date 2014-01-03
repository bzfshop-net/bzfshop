<?php

/**
 * @author QiangYu
 *
 * 普通的数据库实现
 *
 * */

namespace Core\Cloud\Local;

use Core\Cloud\ICloudDb;

class LocalDb implements ICloudDb
{

    public function initDb($isWrite = true)
    {
        return true;
    }

    public function getDb($isWrite = true)
    {
        global $f3;
        return new \Core\Modal\DbEngine($f3->get('sysConfig[db_pdo]'),
            $f3->get('sysConfig[db_username]'), $f3->get('sysConfig[db_password]'));
    }
}