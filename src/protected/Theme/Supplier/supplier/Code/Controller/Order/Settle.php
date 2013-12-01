<?php

/**
 * @author QiangYu
 *
 * 订单商品结算
 *
 * */

namespace Controller\Order;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Service\Order\Goods as OrderGoodsService;
use Core\Service\Order\Settle as OrderSettleService;

class Settle extends \Controller\AuthController
{
    /**
     * 列出结算列表
     *
     * @param $f3
     */
    public function ListSettle($f3)
    {
        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        // 表单查询
        $formQuery = array();
        //结算时间
        $settleTimeStartStr       = $validator->validate('settle_time_start');
        $settleTimeStart          = Time::gmStrToTime($settleTimeStartStr) ? : null;
        $settleTimeEndStr         = $validator->validate('settle_time_end');
        $settleTimeEnd            = Time::gmStrToTime($settleTimeEndStr) ? : null;
        $formQuery['create_time'] = array($settleTimeStart, $settleTimeEnd);

        //是否已经付款
        $is_pay = $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('is_pay');
        switch ($is_pay) {
            case 1:
                $formQuery['pay_time'] = 0;
                break;
            case 2:
                $formQuery['pay_time'] = array($is_pay, null);
                break;
            default:
                break;
        }

        // 供货商只能查看自己的结算历史
        $authSupplierUser          = AuthHelper::getAuthUser();
        $formQuery['suppliers_id'] = $authSupplierUser['suppliers_id'];

        // 构建查询条件
        $condArray = null;
        if (!empty($formQuery)) {
            $condArray = QueryBuilder::buildQueryCondArray($formQuery);
        }

        // 查询结算列表
        $orderSettleService = new OrderSettleService();
        $totalCount         = $orderSettleService->countOrderSettleArray($condArray);
        if ($totalCount <= 0) { // 没商品，可以直接退出了
            goto out;
        }

        // 页数超过最大值，返回
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Order/Settle/ListSettle');
        }

        // 结算列表
        $orderSettleArray = $orderSettleService->fetchOrderSettleArray($condArray, $pageNo * $pageSize, $pageSize);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('orderSettleArray', $orderSettleArray);

        out:
        $smarty->display('order_settle_listsettle.tpl');
    }

    /**
     * 列出结算对应的订单明细
     *
     * @param $f3
     */
    public function ListOrderGoods($f3)
    {
        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $settle_id = $validator->required('结算ID非法')->digits('结算ID非法')->min(1, true, '结算ID非法')->validate('settle_id');
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 做权限检查
        $authSupplierUser = AuthHelper::getAuthUser();

        $orderSettleService = new OrderSettleService();
        $orderSettle        = $orderSettleService->loadOrderSettleBySettleId($settle_id);

        if ($orderSettle->isEmpty() || $authSupplierUser['suppliers_id'] !== $orderSettle['suppliers_id']) {
            $this->addFlashMessage('结算ID非法');
            goto out_fail;
        }

        $orderGoodsService = new OrderGoodsService();

        $totalCount = $orderGoodsService->countOrderGoodsArray(array(array('settle_id = ?', $settle_id)));
        if ($totalCount <= 0) { // 没订单，可以直接退出了
            goto out;
        }

        // 页数超过最大值，返回结算列表
        if ($pageNo * $pageSize >= $totalCount) {
            goto out_fail;
        }

        $orderGoodsArray = $orderGoodsService->fetchOrderGoodsArray(
            array(array('settle_id = ?', $settle_id)),
            $pageNo * $pageSize,
            $pageSize
        );

        // 转换状态显示
        foreach ($orderGoodsArray as &$orderGoodsItem) {
            $orderGoodsItem['order_goods_status_desc'] =
                OrderGoodsService::$orderGoodsStatusDesc[$orderGoodsItem['order_goods_status']];
        }
        unset($orderGoodsItem);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('orderGoodsArray', $orderGoodsArray);

        out:
        $smarty->display('order_settle_listordergoods.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Order/Settle/ListSettle');
    }

    /**
     * 显示结算记录详情
     *
     * @param $f3
     */
    public function ajaxDetail($f3)
    {
        global $smarty;

        $errorMessage = '';
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $settle_id = $validator->required()->digits()->min(1)->validate('settle_id');
        if (!$this->validate($validator)) {
            $errorMessage = '结算ID非法';
            goto out;
        }

        // 加载 order_settle 记录
        $orderSettleService = new OrderSettleService();
        $orderSettle        = $orderSettleService->loadOrderSettleBySettleId($settle_id);

        // 做权限检查
        $authSupplierUser = AuthHelper::getAuthUser();

        if ($orderSettle->isEmpty() || $authSupplierUser['suppliers_id'] !== $orderSettle['suppliers_id']) {
            $errorMessage = '结算ID非法';
            goto out;
        }

        $smarty->assign('settle_id', $settle_id);
        $smarty->assign('orderSettle', $orderSettle);

        out:
        $smarty->assign('errorMessage', $errorMessage);
        $smarty->display('order_settle_ajaxdetail.tpl');
    }

}
