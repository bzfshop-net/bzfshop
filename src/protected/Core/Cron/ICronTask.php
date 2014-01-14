<?php
/**
 * @author QiangYu
 *
 * Cron 任务的基本实现，你可以实现你自己的接口用于执行你自己的 Cron 任务
 *
 */

namespace Core\Cron;


interface ICronTask
{

    /**
     * 执行 Cron 任务
     *
     * @param array $paramArray
     *
     * @return array('code' => '0表示成功其它为失败'， 'message' => '返回的消息限128个字符')
     */
    public function run(array $paramArray);
} 