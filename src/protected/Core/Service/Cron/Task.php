<?php
/**
 * @author QiangYu
 *
 * Cron Task 的存取
 *
 */

namespace Core\Service\Cron;

use Core\Cron\CronHelper;
use Core\Modal\SqlMapper as DataMapper;
use Core\Service\BaseService;

class Task extends BaseService
{
    /**
     * 添加一个 Cron Task 到执行任务列表中
     *
     * @param string $task_name    任务名
     * @param string $task_desc    任务描述
     * @param string $task_class   实现 ICronTask 接口的类名称（全名，包括 namespace）
     * @param int    $task_time    任务执行的时间，GMT 时间
     * @param array  $paramArray   任务执行需要的参数
     * @param string $search_param 用于任务的搜索
     */
    public function addCronTask(
        $task_name,
        $task_desc,
        $task_class,
        $task_time,
        array $paramArray,
        $search_param = null
    ) {

        // 验证任务是否可以添加
        if (!CronHelper::loadTaskClass($task_class)) {
            throw new \InvalidArgumentException('class [' . $task_class . '] illegal');
        }

        $dataMapper               = new DataMapper('cron_task');
        $dataMapper->task_name    = $task_name;
        $dataMapper->task_desc    = $task_desc;
        $dataMapper->task_class   = $task_class;
        $dataMapper->task_time    = $task_time;
        $dataMapper->task_param   = json_encode($paramArray);
        $dataMapper->search_param = $search_param;

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
            array('task_run_time = 0 and task_time <= ?', $task_time),
            array('order' => 'task_time asc, task_id asc', 'limit' => '1')
        );
        return $dataMapper;
    }

    /**
     * 取得 Cron 任务列表
     *
     * @return array 格式 array(array('key'=>'value', 'key'=>'value', ...))
     *
     * @param array $condArray 查询条件
     * @param int   $offset    用于分页的开始 >= 0
     * @param int   $limit     每页多少条
     * @param int   $ttl       缓存多少时间
     */
    public function fetchCronTaskArray(array $condArray, $offset = 0, $limit = 10, $ttl = 0)
    {
        return $this->_fetchArray(
            'cron_task',
            '*', // table , fields
            $condArray,
            array('order' => 'task_id desc'),
            $offset,
            $limit,
            $ttl
        );
    }

    /**
     * 取得 Cron 任务 总数，可用于分页
     *
     * @return int
     *
     * @param array $condArray 查询条件
     * @param int   $ttl       缓存多少时间
     */
    public function countCronTaskArray(array $condArray, $ttl = 0)
    {
        return $this->_countArray('cron_task', $condArray, null, $ttl);
    }

} 