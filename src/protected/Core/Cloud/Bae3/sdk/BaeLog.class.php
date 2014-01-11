<?php
/***************************************************************************
 * 
 * Copyright (c) 2011 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file BaeLog.class.php
 * @author duxi(duxi@baidu.com)
 * @date 2011/03/29 14:00:36
 * @brief 
 *  
 **/

require_once(dirname(__FILE__).'/netlog/NetLog.php');

class BaeLog
{
    private static $instance = null;
    private $netLog;
    private $secret;
    private $loglevel;
    private $tag;
    
    // 这块要修改为打开thrift连接的方式
    private function openServer()
    {
        $this->netLog = new NetLog($this->secret);
        if($this->netLog->netlog_open())
        {
            return true;
        }else{
            return false;
        }
    }

    public function __construct($secret)
    {
        $this->secret = $secret;
        $this->loglevel = 4;    // 默认是notice
    }

    public function setLogLevel($level)
    {
        $this->loglevel = $level; 
        return true;
    }

    public function setLogTag($tag)
    {
        $this->tag = $tag; 
        return true;
    }
    
    /**
     * @brief 获取日志对象实例
     *
     * @return  BaeLog or null
     * @retval  成功的话是BaeLog对象，失败返回null 
     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public static function getInstance($secret)
    {
        // secret can't be null
        if($secret === null || !isset($secret['user']) || !isset($secret['passwd']))
        {
            return null;
        }
        
        if (self::$instance === null)
        {
            self::$instance = new BaeLog($secret);
        }

        if (self::$instance !== null)
        {
            $retrytimes = 0;
            while ($retrytimes < 3 && !self::$instance->openServer()) 
            {
                $retrytimes++;
            }
        }
    
        return self::$instance;
    }

    /*
    *获取上次发送的错误码，非0为失败
    */
    public function getResultCode()
    {
        return $this->netLog->netlog_code();
    }
    
    /**
     * @brief 打印一条致命错误日志
     *
     * @param $logmsg string 日志内容
     * @return  打印日志的长度或者FALSE
     * @retval  �     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function Fatal($logmsg)
    {
        $ret = $this->netLog->netlog_send(1 , $logmsg, $this->tag);
        return $ret;
    }

    /**
     * @brief 打印一条警告日志
     *
     * @param $logmsg string 日志内容
     * @return  打印日志的长度或者FALSE
     * @retval  �     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function Warning($logmsg)
    {
        if ($this->loglevel < 2) return;
        $ret = $this->netLog->netlog_send(2 , $logmsg, $this->tag);
        return $ret;
    }

    /**
     * @brief 打印一条Notice日志
     *
     * @param $logmsg string 日志内容
     * @return  打印日志的长度或者FALSE
     * @retval  �     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function Notice($logmsg)
    {
        if ($this->loglevel < 4) return;
        $ret = $this->netLog->netlog_send(4 , $logmsg, $this->tag);
        return $ret;
    }

    /**
     * @brief 打印一条Trace日志�     *
     * @param $logmsg string 日志内容
     * @return  打印日志的长度或者FALSE
     * @retval  �     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function Trace($logmsg)
    {
        if ($this->loglevel < 8) return;
        $ret = $this->netLog->netlog_send(8 , $logmsg, $this->tag);
        return $ret;
    }

    /**
     * @brief 打印一条Debug日志
     *
     * @param $logmsg string 日志内容
     * @return  打印日志的长度或者FALSE
     * @retval  �     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function Debug($logmsg)
    {
        if ($this->loglevel < 16) return;
        $ret = $this->netLog->netlog_send(16 , $logmsg, $this->tag);
        return $ret;
    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
