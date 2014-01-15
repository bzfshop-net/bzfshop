<?php
/**
 * @author QiangYu
 *
 * Cron 任务的 Helper 操作类，方便你加入一个 Cron Task
 *
 */

namespace Core\Cron;

use Core\Cache\ShareCache;
use Core\Log\Base as BaseLog;
use Core\Service\Cron\Task as CronTaskService;

class CronHelper
{
    /**
     * 为了减少数据库的读写，我们把下一个要执行的 cronTask 做了缓存
     */
    private static $cronTaskCacheKey = null;

    /**
     * 如果没有任务，我们用这个数据替代
     */
    private static $cronTaskNoTaskMagic = 'NOTASK';

    private static function getCronTaskCacheKey()
    {

        if (!self::$cronTaskCacheKey) {
            self::$cronTaskCacheKey = md5(__FILE__);
        }

        return self::$cronTaskCacheKey;
    }

    /**
     * 根据 task_class 参数加载并实例化 Cron 任务对象
     *
     * @param string $task_class
     *
     * @return ICronTask 对象
     */
    public static function loadTaskClass($task_class)
    {
        if (!class_exists($task_class)) {
            printLog('class [' . $task_class . '] does not exist', __CLASS__, BaseLog::ERROR);
            return null;
        }

        $taskInstance = new $task_class();
        if (!is_a($taskInstance, '\\Core\\Cron\\ICronTask')) {
            printLog('class [' . $task_class . '] does not implement ICronTask', __CLASS__, BaseLog::ERROR);
            unset($taskInstance);
            return null;
        }

        return $taskInstance;
    }

    /**
     * 添加一个 Cron Task 到执行任务列表中
     *
     * @param string $user_name    哪个用户添加的
     * @param string $task_name    任务名
     * @param string $task_desc    任务描述
     * @param string $task_class   实现 ICronTask 接口的类名称（全名，包括 namespace）
     * @param int    $task_time    任务执行的时间，GMT 时间
     * @param array  $paramArray   任务执行需要的参数
     * @param string $search_param 用于任务的搜索
     */
    public static function addCronTask(
        $user_name,
        $task_name,
        $task_desc,
        $task_class,
        $task_time,
        array $paramArray,
        $search_param = null
    ) {
        $cronTaskService = new CronTaskService();
        $cronTaskService->addCronTask(
            $user_name,
            $task_name,
            $task_desc,
            $task_class,
            $task_time,
            $paramArray,
            $search_param
        );

        // 清除缓存
        ShareCache::clear(self::getCronTaskCacheKey());
    }

    /**
     * 取得当前时间需要执行的一个 Cron Task，注意这里是 load 方法，所以返回的结果是可以做 CRUD 操作的s
     *
     * @param int $currentTime 当前时间
     *
     * @return \Core\Service\Cron\DataMapper|null
     */
    public static function loadNextUnRunCronTask($currentTime)
    {
        $cronTaskInfo = ShareCache::get(self::getCronTaskCacheKey());

        // 缓存表明目前没有任务，所以我们也就没必要再去查询数据库了
        if (self::$cronTaskNoTaskMagic === $cronTaskInfo) {
            return null;
        }

        // 当前任务还没到执行的时候
        if (is_array($cronTaskInfo) && $cronTaskInfo['task_time'] > $currentTime) {
            return null;
        }

        // 从数据库中取得任务
        $cronTaskService = new CronTaskService();
        $cronTask        = $cronTaskService->loadNextUnRunCronTask($currentTime);

        // 数据库里面也没有任务，设置缓存为“无任务”就不用老去查找数据库了
        if ($cronTask->isEmpty()) {
            ShareCache::set(self::getCronTaskCacheKey(), self::$cronTaskNoTaskMagic, 3600);
            return null;
        }

        // 这个任务还没到执行的时间，放到缓存里面
        if ($cronTask->task_time > $currentTime) {
            ShareCache::set(self::getCronTaskCacheKey(), $cronTask->toArray(), 3600);
            return null;
        }

        // 清除缓存
        ShareCache::clear(self::getCronTaskCacheKey());
        // 返回需要执行的任务
        return $cronTask;
    }
} 