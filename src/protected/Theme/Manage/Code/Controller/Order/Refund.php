<?php

/**
 * @author QiangYu
 *
 * 订单的退款处理
 *
 * */

namespace Controller\Order;


use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Search\SearchHelper;
use Core\Service\Order\Action as OrderActionService;
use Core\Service\Order\Goods as OrderGoodsService;
use Core\Service\Order\Order as OrderBasicService;
use Core\Service\User\User as UserBasicService;

class Refund extends \Controller\AuthController
{

    public function Search($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_refund_search');

        /**
         * 我们使用搜索模块做搜索操作
         */
        $searchFieldSelector = 'og.*, oi.user_id, oi.system_id';

        global $smarty;
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        // shippingStatus, 0 全部，1 已发货，2 未发货
        $shippingStatus = $validator->digits()->min(1)->validate('shippingStatus');

        //订单表单查询
        $searchFormQuery                  = array();
        $searchFormQuery['og.order_id']   =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('order_id');
        $searchFormQuery['og.rec_id']     =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('rec_id');
        $searchFormQuery['og.goods_id']   =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('goods_id');
        $searchFormQuery['og.goods_name'] = $validator->validate('goods_name');
        $searchFormQuery['oi.order_sn']   = $validator->validate('order_sn');
        $searchFormQuery['oi.pay_no']     = $validator->validate('pay_no');

        $order_goods_status =
            $validator->min(0)->filter('ValidatorIntValue')->validate('order_goods_status');

        if (null == $order_goods_status) {
            // 选择 请求退款、退款中、退款完成 状态的订单
            $searchFormQuery['og.order_goods_status'] = array('>', OrderGoodsService::OGS_PAY);
        } else {
            $searchFormQuery['og.order_goods_status'] = $order_goods_status;
        }

        $searchFormQuery['oi.consignee']  = $validator->validate('consignee');
        $searchFormQuery['oi.mobile']     = $validator->validate('mobile');
        $searchFormQuery['oi.address']    = $validator->validate('address');
        $searchFormQuery['oi.postscript'] = $validator->validate('postscript');
        $searchFormQuery['og.memo']       = $validator->validate('memo');

        //退款时间
        $refundTimeStartStr                = $validator->validate('refund_time_start');
        $refundTimeStart                   = Time::gmStrToTime($refundTimeStartStr) ? : null;
        $refundTimeEndStr                  = $validator->validate('refund_time_end');
        $refundTimeEnd                     = Time::gmStrToTime($refundTimeEndStr) ? : null;
        $searchFormQuery['og.refund_time'] = array($refundTimeStart, $refundTimeEnd);

        //完成时间
        $refundFinishTimeStartStr                 = $validator->validate('refund_finish_time_start');
        $refundFinishTimeStart                    = Time::gmStrToTime($refundFinishTimeStartStr) ? : null;
        $refundFinishTimeEndStr                   = $validator->validate('refund_finish_time_end');
        $refundFinishTimeEnd                      = Time::gmStrToTime($refundFinishTimeEndStr) ? : null;
        $searchFormQuery['og.refund_finish_time'] = array($refundFinishTimeStart, $refundFinishTimeEnd);

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 构造查询条件
        $searchParamArray = array();

        // 用户取消的订单商品我们就不再显示了
        $searchParamArray[] = array(
            QueryBuilder::buildNotInCondition(
                'oi.order_status',
                array(OrderBasicService::OS_CANCELED, OrderBasicService::OS_INVALID),
                \PDO::PARAM_INT
            )
        );

        if (1 == $shippingStatus) {
            // 1 未发货
            $searchParamArray[] = array('og.shipping_id = 0');
        } elseif (2 == $shippingStatus) {
            // 2 已发货
            $searchParamArray[] = array('og.shipping_id <> 0');
        } else {
            // do nothing
        }

        // 用户查询，目前支持 用户名、邮箱 查询
        $user_name = $validator->validate('user_name');
        $email     = $validator->validate('email');
        if (!empty($user_name) || !empty($email)) {
            $userQuery              = array();
            $userQuery['user_name'] = $user_name;
            $userQuery['email']     = $email;
            $userBasicService       = new UserBasicService();
            $queryUserArray         = $userBasicService->_fetchArray(
                'users',
                'user_id',
                QueryBuilder::buildQueryCondArray($userQuery),
                array('order' => 'user_id desc'),
                0,
                1000
            );
            unset($userBasicService);

            if (empty($queryUserArray)) {
                $this->addFlashMessage('搜索的用户不存在');
            } else {
                $userIdArray = array();
                foreach ($queryUserArray as $queryUser) {
                    $userIdArray[] = $queryUser['user_id'];
                }
                if (!empty($userIdArray)) {
                    $searchParamArray[] =
                        array(QueryBuilder::buildInCondition('oi.user_id', $userIdArray, \PDO::PARAM_INT));
                }
                unset($userIdArray);
                unset($queryUserArray);
            }
        }

        // 表单查询
        $searchParamArray = array_merge($searchParamArray, QueryBuilder::buildSearchParamArray($searchFormQuery));

        // 使用哪个搜索模块
        $searchModule = SearchHelper::Module_OrderGoodsOrderInfo;

        // 查询订单列表
        $totalCount = SearchHelper::count($searchModule, $searchParamArray);
        if ($totalCount <= 0) { // 没订单，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Order/Refund/Search');
        }

        // 查询订单列表
        $orderGoodsArray = SearchHelper::search(
            $searchModule,
            $searchFieldSelector,
            $searchParamArray,
            array(array('og.order_id', 'desc')),
            $pageNo * $pageSize,
            $pageSize
        );

        // 订单的支付状态显示
        foreach ($orderGoodsArray as &$orderGoodsItem) {
            $orderGoodsItem['order_goods_status_desc'] =
                OrderGoodsService::$orderGoodsStatusDesc[$orderGoodsItem['order_goods_status']];
        }
        // 前面用了引用，这里一定要清除，防止影响后面的数据
        unset($orderGoodsItem);

        // 取得用户 id 列表
        $userIdArray = array();
        foreach ($orderGoodsArray as $orderGoodsItem) {
            $userIdArray[] = $orderGoodsItem['user_id'];
        }
        $userIdArray = array_unique($userIdArray);

        //取得用户信息
        $userBasicService = new UserBasicService();
        $userArray        = $userBasicService->fetchUserArrayByUserIdArray($userIdArray);

        // 建立 user_id --> user 的反查表，方便快速查询
        $userIdToUserArray = array();
        foreach ($userArray as $user) {
            $userIdToUserArray[$user['user_id']] = $user;
        }

        // 放入用户信息
        foreach ($orderGoodsArray as &$orderGoodsItem) {
            if (isset($userIdToUserArray[$orderGoodsItem['user_id']])) {
                // 很老的订单，用户可能被删除了
                $orderGoodsItem['user_name'] = @$userIdToUserArray[$orderGoodsItem['user_id']]['user_name'];
                $orderGoodsItem['email']     = @$userIdToUserArray[$orderGoodsItem['user_id']]['email'];
            }
        }
        // 前面用了引用，这里一定要清除，防止影响后面的数据
        unset($orderGoodsItem);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('orderGoodsArray', $orderGoodsArray);

        out_display:
        $smarty->display('order_refund_search.tpl');
    }


    /**
     * 设置订单为 退款中 状态
     *
     * 即：确认已经收到用户的退货了，告知财务这个订单可以给用户退款了
     *
     * @param $f3
     */
    public function SetRefund($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_goods_update_set_refund');

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $rec_id    = $validator->required()->digits()->min(1)->validate('rec_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        $orderBasicService = new OrderBasicService();
        $orderGoods        = $orderBasicService->loadOrderGoodsById($rec_id);
        if ($orderGoods->isEmpty()
            || OrderGoodsService::OGS_ASKREFUND != $orderGoods->order_goods_status
        ) {
            $this->addFlashMessage('订单ID非法');
            goto out;
        }

        $orderInfo = $orderBasicService->loadOrderInfoById($orderGoods['order_id']);
        if ($orderInfo->isEmpty() || $orderInfo->pay_status != OrderBasicService::PS_PAYED) {
            $this->addFlashMessage('订单ID非法');
            goto out;
        }

        // 设置订单状态为  退款中
        $orderGoods->order_goods_status = OrderGoodsService::OGS_REFUNDING;
        $orderGoods->save();

        // 更新 order_info 的 update_time 字段
        $orderInfo->update_time = Time::gmTime();
        $orderInfo->save();

        $authAdminUser = AuthHelper::getAuthUser();

        $action_note = '设置为退款中' . "\n";
        $action_note .= '操作人：[' . $authAdminUser['user_id'] . ']' . $authAdminUser['user_name'] . "\n";

        // 添加订单操作日志
        $orderActionService = new OrderActionService();
        $orderActionService->logOrderAction(
            $orderGoods['order_id'],
            $orderGoods['rec_id'],
            $orderInfo['order_status'],
            $orderInfo['pay_status'],
            $orderGoods['order_goods_status'],
            $action_note,
            $authAdminUser['user_name'],
            0,
            $orderInfo['shipping_status']
        );

        $this->addFlashMessage('订单状态设置为[退款中]');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    /**
     * 确认给用户退款
     *
     * @param $f3
     */
    public function Confirm($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_refund_confirm');

        // 参数验证
        $validator          = new Validator($f3->get('POST'));
        $rec_id             = $validator->required()->digits()->min(1)->validate('rec_id');
        $refund_finish_note = $validator->required()->validate('refund_finish_note');

        if (!$this->validate($validator)) {
            goto out;
        }

        $orderBasicService = new OrderBasicService();
        $orderGoods        = $orderBasicService->loadOrderGoodsById($rec_id);
        if ($orderGoods->isEmpty()
            || OrderGoodsService::OGS_REFUNDING != $orderGoods->order_goods_status
        ) {
            $this->addFlashMessage('订单ID非法');
            goto out;
        }

        $orderInfo = $orderBasicService->loadOrderInfoById($orderGoods['order_id']);
        if ($orderInfo->isEmpty() || $orderInfo->pay_status != OrderBasicService::PS_PAYED) {
            $this->addFlashMessage('订单ID非法');
            goto out;
        }

        // 标记订单为已经退款
        $orderGoods->order_goods_status = OrderGoodsService::OGS_REFUND;
        $orderGoods->refund_finish_time = Time::gmTime();
        $orderGoods->refund_finish_note = $refund_finish_note;
        $orderGoods->save();

        // 更新 order_info 的 update_time 字段
        $orderInfo->update_time = Time::gmTime();
        $orderInfo->save();

        $authAdminUser = AuthHelper::getAuthUser();

        $action_note = '确认退款' . "\n";
        $action_note .= '操作人：[' . $authAdminUser['user_id'] . ']' . $authAdminUser['user_name'] . "\n";
        $action_note .= '备注：' . $refund_finish_note . "\n";

        // 添加订单操作日志
        $orderActionService = new OrderActionService();
        $orderActionService->logOrderAction(
            $orderGoods['order_id'],
            $orderGoods['rec_id'],
            $orderInfo['order_status'],
            $orderInfo['pay_status'],
            $orderGoods['order_goods_status'],
            $action_note,
            $authAdminUser['user_name'],
            0,
            $orderInfo['shipping_status']
        );

        $this->addFlashMessage('确认退款成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    /**
     * 拒绝退款请求
     *
     * @param $f3
     */
    public function Refuse($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_refund_confirm');

        // 参数验证
        $validator          = new Validator($f3->get('POST'));
        $rec_id             = $validator->required()->digits()->min(1)->validate('rec_id');
        $refund_finish_note = $validator->required()->validate('refund_finish_note');

        if (!$this->validate($validator)) {
            goto out;
        }

        $orderBasicService = new OrderBasicService();
        $orderGoods        = $orderBasicService->loadOrderGoodsById($rec_id);
        if ($orderGoods->isEmpty()
            || OrderGoodsService::OGS_REFUNDING != $orderGoods->order_goods_status
        ) {
            $this->addFlashMessage('订单ID非法');
            goto out;
        }

        $orderInfo = $orderBasicService->loadOrderInfoById($orderGoods['order_id']);
        if ($orderInfo->isEmpty() || $orderInfo->pay_status != OrderBasicService::PS_PAYED) {
            $this->addFlashMessage('订单ID非法');
            goto out;
        }

        // 标记订单为 付款
        $orderGoods->order_goods_status = OrderGoodsService::OGS_PAY;
        // 清除退款记录
        $orderGoods->refund             = 0; // 我们给顾客退款
        $orderGoods->refund_time        = 0;
        $orderGoods->refund_finish_time = Time::gmTime();
        $orderGoods->refund_finish_note = $refund_finish_note;
        $orderGoods->suppliers_refund   = 0; // 供货商给我们退款
        $orderGoods->save();

        // 更新 order_info 的 update_time 字段
        $orderInfo->update_time = Time::gmTime();
        $orderInfo->save();

        $authAdminUser = AuthHelper::getAuthUser();

        $action_note = '拒绝退款' . "\n";
        $action_note .= '操作人：[' . $authAdminUser['user_id'] . ']' . $authAdminUser['user_name'] . "\n";
        $action_note .= '备注：' . $refund_finish_note . "\n";

        // 添加订单操作日志
        $orderActionService = new OrderActionService();
        $orderActionService->logOrderAction(
            $orderGoods['order_id'],
            $orderGoods['rec_id'],
            $orderInfo['order_status'],
            $orderInfo['pay_status'],
            $orderGoods['order_goods_status'],
            $action_note,
            $authAdminUser['user_name'],
            0,
            $orderInfo['shipping_status']
        );

        $this->addFlashMessage('拒绝退款成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }
}
