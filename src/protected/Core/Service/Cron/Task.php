<?php
/**
 * @author QiangYu
 *
 * Cron Task 的存取
 *
 */

namespace Core\Service\Cron;

use Core\Modal\SqlMapper as DataMapper;

class Task
{
    /**
     * 添加一个 Cron Task 到执行任务列表中
     *
     * @param string $task_name  任务名
     * @param string $task_desc  任务描述
     * @param string $task_class 实现 ICronTask 接口的类名称（全名，包括 namespace）
     * @param int    $task_time  任务执行的时间，GMT 时间
     * @param array  $paramArray 任务执行需要的参数
     */
    public function addCronTask($task_name, $task_desc, $task_class, $task_time, array $paramArray)
    {

        // 验证任务是否可以添加
        if (!self::loadTaskClass($task_class)) {
            throw new \InvalidArgumentException('class [' . $task_class . '] illegal');
        }

        $dataMapper             = new DataMapper('cron_task');
        $dataMapper->task_name  = $task_name;
        $dataMapper->task_desc  = $task_desc;
        $dataMapper->task_class = $task_class;
        $dataMapper->task_time  = $task_time;
        $dataMapper->task_param = json_encode($paramArray);

        $dataMapper->save();
        unset($dataMapper);
    }

    /**
     * 取得下一个还没有执行的 Cron Task
     *
     * @param int $task_time
     *
     * @return DataMapper
     */
    public function loadNextUnRunCronTask($task_time)
    {
        $dataMapper = new DataMapper('cron_task');
        $dataMapper->load(
            array('task_run_time = 0 and task_time >= ?', $task_time),
            array('order' => 'task_time asc, task_id asc', 'limit' => '1')
        );
        return $dataMapper;
    }
} 