<?php
/**
 * @author QiangYu
 *
 * 云平台的数据库实现，从这里获得数据库引擎
 *
 */

namespace Core\Cloud;


interface ICloudDb
{

    /**
     * 初始化数据库引擎
     *
     * @param boolean $isWrite 是否写操作，用于数据库的读写分离
     *
     * @return mixed
     */
    public function initDb($isWrite = true);


    /**
     * 获取数据库查询引擎
     *
     * * @param boolean $isWrite 是否写操作，用于数据库的读写分离
     *
     * @return mixed
     */
    public function getDb($isWrite = true);

} 