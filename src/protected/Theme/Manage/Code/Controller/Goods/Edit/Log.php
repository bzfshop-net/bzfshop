<?php

/**
 * @author QiangYu
 *
 * 商品的操作日志显示
 *
 * */

namespace Controller\Goods\Edit;

use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Log as GoodsLogService;

class Log extends \Controller\AuthController
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

        $goodsLogService = new GoodsLogService();
        $totalCount      = $goodsLogService->countGoodsLogArray($goods_id);
        $goodsLogArray   = $goodsLogService->fetchGoodsLogArray($goods_id, $pageNo * $pageSize, $pageSize);

        // 格式化内容的输出
        foreach ($goodsLogArray as &$goodsLog) {
            if (!empty($goodsLog['content'])) {
                $goodsLog['content'] = nl2br($goodsLog['content']);
            }
        }
        unset($goodsLog);

        // 给模板赋值
        $smarty->assign('goodsLogArray', $goodsLogArray);
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);

        $smarty->display('goods_edit_log.tpl');
        return;

        out_fail:
        RouteHelper::reRoute($this, '/Goods/Search');
    }

}
