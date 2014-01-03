<?php

/**
 * @author QiangYu
 *
 * Sae 的数据库实现
 *
 * */

namespace Core\Cloud\Sae;

use Core\Cloud\ICloudDb;

class SaeDb implements ICloudDb
{

    public function initDb($isWrite = true)
    {
        return true;
    }

    public function getDb($isWrite = true)
    {
        $pdo = 'mysql:host=' . SAE_MYSQL_HOST_M . ';port=' . SAE_MYSQL_PORT . ';dbname=' . SAE_MYSQL_DB;
        return new \Core\Modal\DbEngine($pdo, SAE_MYSQL_USER, SAE_MYSQL_PASS);
    }
}