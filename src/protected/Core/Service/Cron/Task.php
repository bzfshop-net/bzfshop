<?php
/**
 * @author QiangYu
 *
 * Cron Task 的存取
 *
 */

namespace Core\Service\Cron;

use Core\Cron\CronHelper;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;
use Core\Service\BaseService;

class Task extends BaseService
{

    /**
     * 通过 task_id 取得一个 cron 任务
     *
     * @param  int $task_id
     * @param int  $ttl
     *
     * @return object
     */
    public function loadCronTaskById($task_id, $ttl = 0)
    {
        return $this->_loadById('cron_task', 'task_id = ?', $task_id, $ttl);
    }

    /**
     * 删除一个 cron 任务，注意：已经运行完成的任务不允许删除
     *
     * @param int $task_id
     *
     * @return bool
     */
    public function removeCronTaskById($task_id)
    {
        // 首先做参数验证
        $validator = new Validator(array('task_id' => $task_id));
        $task_id   = $validator->required()->digits()->min(1)->validate('task_id');
        $this->validate($validator);

        $cronTask = $this->loadCronTaskById($task_id);
        if ($cronTask->isEmpty()) {
            return false;
        }

        //已经运行完成的任务不允许删除
        if (0 != $cronTask['task_run_time']) {
            return false;
        }

        // 删除任务
        $cronTask->erase();

        return true;
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
    public function addCronTask(
        $user_name,
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
        $dataMapper->user_name    = $user_name;
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
            array('task_run_time = 0'),
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