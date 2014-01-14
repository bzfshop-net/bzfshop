<?php
/**
 * @author QiangYu
 *
 * 一个 Cron 缺省的 Controller 实现，我们在网页上调用这个 controller 驱动 cron 去完成各种任务
 *
 */

namespace Core\Cron;


use Core\Controller\BaseController;
use Core\Helper\Utility\Time;

class CronController extends BaseController
{

    /**
     * 每次 Controller 调用最多执行几个 Cron 任务
     */
    public static $maxRunTaskCount = 10;

    protected $isHtmlController = false;

    /**
     * 驱动 Cron 任务执行
     *
     * @param $f3
     */
    public function Run($f3)
    {
        $taskCount = 0;

        while (($cronTask = CronHelper::loadNextUnRunCronTask(Time::gmTime()))
            && $taskCount++ < self::$maxRunTaskCount) {

            $cronResult = array('code' => -1, 'message' => '任务内部错误，请查看日志');

            // 加载 Task Class
            $taskInstance = CronHelper::loadTaskClass($cronTask['task_class']);

            // 无法实例化 task class，打印错误日志
            if (null == $taskInstance) {
                printLog(
                    'can not instantiate task ' . json_encode($cronTask->toArray()),
                    __CLASS__,
                    \Core\Log\Base::ERROR
                );
            } else {
                // 执行任务
                $cronResult =
                    call_user_func_array(
                        array($taskInstance, 'run'),
                        array(json_decode($cronTask['task_param'], true))
                    );
            }

            // 释放内存
            unset($taskInstance);

            // 更新任务执行状态
            $cronTask->task_run_time  = Time::gmTime();
            $cronTask->return_code    = @$cronResult['code'];
            $cronTask->return_message = @$cronResult['message'];
            $cronTask->save();
            unset($cronTask);
        }
    }

} 