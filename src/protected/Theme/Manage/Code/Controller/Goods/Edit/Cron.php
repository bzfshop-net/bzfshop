<?php

/**
 * @author QiangYu
 *
 * 商品的定时任务操作，比如定时上线、下线
 *
 * */

namespace Controller\Goods\Edit;

use Core\Cron\CronHelper;
use Core\Cron\GoodsCronTask;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Service\Cron\Task as CronTaskService;

class Cron extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_get');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));

        $goods_id = $validator->required('商品ID不能为空')->digits()->min(1)->validate('goods_id');
        $pageNo   = $validator->digits()->min(0)->validate('pageNo');
        $pageSize = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 20;

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        //查询条件
        $searchFormQuery                 = array();
        $searchFormQuery['task_name']    = array('=', GoodsCronTask::$task_name);
        $searchFormQuery['search_param'] = array('=', $goods_id);

        // 建立查询条件
        $searchParamArray = QueryBuilder::buildQueryCondArray($searchFormQuery);

        $cronTaskService = new CronTaskService();
        $totalCount      = $cronTaskService->countCronTaskArray($searchParamArray);
        if ($totalCount <= 0) { // 没任务，可以直接退出了
            goto out_display;
        }

        $cronTaskArray = $cronTaskService->fetchCronTaskArray($searchParamArray, $pageNo * $pageSize, $pageSize);

        $smarty->assign('cronTaskArray', $cronTaskArray);

        out_display:
        $smarty->display('goods_edit_cron.tpl');
        return;

        out_fail:
        RouteHelper::reRoute($this, '/Goods/Search');
    }

    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_get');

        // 参数验证
        $validator = new Validator($f3->get('POST'));

        $goods_id = $validator->required('商品ID不能为空')->validate('goods_id');
        $action   = $validator->required('操作不能为空')->validate('action');

        //任务时间
        $taskTimeStr = $validator->required('必须选择时间')->validate('task_time');
        $taskTime    = Time::gmStrToTime($taskTimeStr) ? : null;

        if (!$this->validate($validator)) {
            goto out;
        }

        // 添加 Cron 任务
        CronHelper::addCronTask(
            GoodsCronTask::$task_name,
            @GoodsCronTask::$actionDesc[$action] . '[' . $goods_id . ']',
            '\\Core\Cron\\GoodsCronTask',
            $taskTime,
            array('goods_id' => $goods_id, 'action' => $action),
            $goods_id
        );

        $this->addFlashMessage('成功添加定时任务');

        out:
        RouteHelper::reRoute(
            $this,
            RouteHelper::makeUrl('/Goods/Edit/Cron', array('goods_id' => $goods_id), true)
        );
    }
}
