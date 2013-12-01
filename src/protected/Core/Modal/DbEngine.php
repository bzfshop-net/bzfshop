<?php

/**
 * @author QiangYu
 *
 * 数据库连接引擎
 *
 * */

namespace Core\Modal;

use Core\Log\Base;

class DbEngine extends \DB\SQL
{

    function __construct($dsn, $user = null, $pw = null, array $options = null)
    {
        parent::__construct($dsn, $user, $pw, $options);
    }

    function exec($cmds, $args = null, $ttl = 0, $log = true)
    {
        global $logger;

        $result = parent::exec($cmds, $args, $ttl, $log);

        // 把执行的 SQL 加入到日志信息中去
        $logger->addLogInfo(Base::DEBUG, 'SQL', $this->sql(''));

        return $result;
    }

}