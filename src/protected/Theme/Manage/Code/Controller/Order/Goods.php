<?php

/**
 * @author QiangYu
 *
 * 订单对应的 order_goods 表操作
 *
 * */

namespace Controller\Order;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Money;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Search\SearchHelper;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Meta\Express as ExpressService;
use Core\Service\Order\Action as OrderActionService;
use Core\Service\Order\Goods as OrderGoodsService;
use Core\Service\Order\Order as OrderBasicService;
use Core\Service\Order\Refer as OrderReferService;
use Core\Service\User\Supplier as SupplierUserService;
use Core\Service\User\User as UserBasicService;

class Goods extends \Controller\AuthController
{

    public function Search($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_goods_search');

        /**
         * 我们使用搜索模块做搜索操作
         */
        $searchFieldSelector = 'og.*, oi.user_id, oi.system_id, oi.kefu_user_id, oi.kefu_user_name, oi.kefu_user_rate';

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
            $validator->min(-1)->filter('ValidatorIntValue')->validate('order_goods_status');

        if ($order_goods_status >= 0) {
            $searchFormQuery['og.order_goods_status'] = $order_goods_status;
        }

        $searchFormQuery['oi.consignee']  = $validator->validate('consignee');
        $searchFormQuery['oi.mobile']     = $validator->validate('mobile');
        $searchFormQuery['oi.address']    = $validator->validate('address');
        $searchFormQuery['oi.postscript'] = $validator->validate('postscript');

        $searchFormQuery['og.memo']                = $validator->validate('memo');
        $searchFormQuery['oi.kefu_user_id']        = $validator->filter('ValidatorIntValue')->validate('kefu_user_id');
        $searchFormQuery['og.goods_admin_user_id'] =
            $validator->filter('ValidatorIntValue')->validate('goods_admin_user_id');

        //下单时间
        $createTimeStartStr                = $validator->validate('create_time_start');
        $createTimeStart                   = Time::gmStrToTime($createTimeStartStr) ? : null;
        $createTimeEndStr                  = $validator->validate('create_time_end');
        $createTimeEnd                     = Time::gmStrToTime($createTimeEndStr) ? : null;
        $searchFormQuery['og.create_time'] = array($createTimeStart, $createTimeEnd);

        //付款时间
        $payTimeStartStr                = $validator->validate('pay_time_start');
        $payTimeStart                   = Time::gmStrToTime($payTimeStartStr) ? : null;
        $payTimeEndStr                  = $validator->validate('pay_time_end');
        $payTimeEnd                     = Time::gmStrToTime($payTimeEndStr) ? : null;
        $searchFormQuery['oi.pay_time'] = array($payTimeStart, $payTimeEnd);

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

        $utmSource = $validator->validate('utm_source');
        if ('SELF' == $utmSource) {
            $searchParamArray[] = array('orf.utm_source is null');
        } else {
            $searchFormQuery['orf.utm_source'] = array('=', $utmSource);
        }

        $utmMedium = $validator->validate('utm_medium');
        if ('SELF' == $utmMedium) {
            $searchParamArray[] = array('orf.utm_medium is null');
        } else {
            $searchFormQuery['orf.utm_medium'] = array('=', $utmMedium);
        }

        // 表单查询
        $searchParamArray = array_merge($searchParamArray, QueryBuilder::buildSearchParamArray($searchFormQuery));

        // 使用哪个搜索模块
        $searchModule = SearchHelper::Module_OrderGoodsOrderInfo;
        if (!empty($utmSource) || !empty($utmMedium)) {
            $searchModule = SearchHelper::Module_OrderGoodsOrderInfoOrderRefer;
        }

        // 查询订单列表
        $totalCount = SearchHelper::count($searchModule, $searchParamArray);
        if ($totalCount <= 0) { // 没订单，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Order/Goods/Search');
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
                $orderGoodsItem['user_name'] = $userIdToUserArray[$orderGoodsItem['user_id']]['user_name'];
                $orderGoodsItem['email']     = $userIdToUserArray[$orderGoodsItem['user_id']]['email'];
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
        $smarty->display('order_goods_search.tpl');
    }


    /**
     * 显示订单详情
     */
    public function Detail($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_goods_detail');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $rec_id    = $validator->required()->digits()->min(1)->validate('rec_id');
        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 取 order_goods
        $orderBasicService = new OrderBasicService();
        $orderGoods        = $orderBasicService->loadOrderGoodsById($rec_id);
        if ($orderGoods->isEmpty() || $orderGoods['order_id'] <= 0) {
            $this->addFlashMessage('订单ID非法');
            goto out_display;
        }

        // 转换状态显示
        $orderGoods['order_goods_status_desc'] =
            OrderGoodsService::$orderGoodsStatusDesc[$orderGoods['order_goods_status']];

        // 额外优惠允许的最大金额
        $allowExtraDiscount = $orderGoods['goods_price'] + $orderGoods['shipping_fee'] - $orderGoods['discount'];
        $maxExtraDiscount   =
            round($allowExtraDiscount * $f3->get('sysConfig[max_order_goods_extra_discount_rate]'), 2);
        $maxExtraDiscount   = max($maxExtraDiscount, $f3->get('sysConfig[max_order_goods_extra_discount_value]'));
        $maxExtraDiscount   = min($maxExtraDiscount, $allowExtraDiscount);

        // 退款允许退的最大金额
        $maxRefund = $orderGoods['goods_price'] + $orderGoods['shipping_fee'] - $orderGoods['discount']
            - $orderGoods['extra_discount'];

        // 取商品信息
        $goodsBasicService = new GoodsBasicService();
        $goods             = $goodsBasicService->loadGoodsById($orderGoods['goods_id'], 300); // 缓存 300 秒

        // 取 order_info
        $orderInfo = $orderBasicService->loadOrderInfoById($orderGoods['order_id']);
        if ($orderInfo->isEmpty()) {
            $this->addFlashMessage('订单ID非法');
            goto out_display;
        }
        $orderInfo['order_status_desc'] = OrderBasicService::$orderStatusDesc[$orderInfo['order_status']];
        $orderInfo['pay_status_desc']   = OrderBasicService::$payStatusDesc[$orderInfo['pay_status']];

        // 取订单来源信息
        $orderReferService = new OrderReferService();
        $orderRefer        = $orderReferService->loadOrderReferByOrderId($orderInfo['order_id'], 300); //缓存5分钟

        // 取用户账户
        $userBasicService = new UserBasicService();
        $userInfo         = $userBasicService->loadUserById($orderInfo['user_id']);

        // 取得订单的操作日志
        $orderActionService = new OrderActionService();
        $orderLogArray      = $orderActionService->fetchOrderLogArray($orderGoods['order_id'], $orderGoods['rec_id']);

        // 状态字段转换成可以显示的字符串
        foreach ($orderLogArray as &$orderLog) {
            $orderLog['order_status']       = OrderBasicService::$orderStatusDesc[$orderLog['order_status']];
            $orderLog['pay_status']         = OrderBasicService::$payStatusDesc[$orderLog['pay_status']];
            $orderLog['order_goods_status'] = OrderGoodsService::$orderGoodsStatusDesc[$orderLog['order_goods_status']];
            $orderLog['action_note']        = nl2br($orderLog['action_note']);
        }
        unset($orderLog);

        // 查询供货商信息
        $supplierUserService = new SupplierUserService();
        $supplierInfo        = $supplierUserService->loadSupplierById($orderGoods['suppliers_id']);

        // 给模板赋值
        $smarty->assign('orderGoods', $orderGoods);
        $smarty->assign('goods', $goods);
        $smarty->assign('maxExtraDiscount', $maxExtraDiscount);
        $smarty->assign('maxRefund', $maxRefund);
        $smarty->assign('orderInfo', $orderInfo);
        $smarty->assign('orderRefer', $orderRefer);
        $smarty->assign('userInfo', $userInfo);
        $smarty->assign('supplierInfo', $supplierInfo);
        $smarty->assign('orderLogArray', $orderLogArray);

        out_display:
        $smarty->display('order_goods_detail.tpl');
    }


    /**
     * 设置订单的信息，注意：这个方法里面糅合了很多功能，通过 action="xxxx" 来区分
     *
     * @param $f3
     */
    public function Update($f3)
    {
        // 验证 action
        $validator = new Validator($f3->get('GET'));
        $action    =
            $validator->required()
                ->oneOf(
                    array(
                         'set_extra_discount',
                         'set_suppliers_price',
                         'set_shipping_no',
                         'set_memo',
                         'set_refund',
                         'set_extra_refund'
                    ),
                    '非法操作'
                )
                ->validate('action');

        if (!$this->validate($validator)) {
            goto out;
        }

        // 验证提交上来的参数
        $validator        = new Validator($f3->get('POST'));
        $updateValueArray = array();
        $rec_id           = $validator->required()->digits()->min(1)->validate('rec_id');

        // 针对不同的 action  做不同的验证
        switch ($action) {
            case 'set_extra_discount':
                // 权限检查
                $this->requirePrivilege('manage_order_goods_update_set_extra_discount');
                $updateValueArray['extra_discount']      = Money::toStorage($validator->validate('extra_discount'));
                $updateValueArray['extra_discount_note'] = $validator->required()->validate('extra_discount_note');
                break;
            case 'set_suppliers_price':
                // 权限检查
                $this->requirePrivilege('manage_order_goods_update_set_suppliers_price');
                $updateValueArray['suppliers_price']        = Money::toStorage($validator->validate('suppliers_price'));
                $updateValueArray['suppliers_shipping_fee'] =
                    Money::toStorage($validator->validate('suppliers_shipping_fee'));
                break;
            case 'set_shipping_no':
                // 权限检查
                $this->requirePrivilege('manage_order_goods_update_set_shipping_no');
                $updateValueArray['shipping_id'] = $validator->digits()->min(1)->validate('shipping_id');
                $updateValueArray['shipping_no'] = $validator->validate('shipping_no');
                break;
            case 'set_memo':
                // 权限检查
                $this->requirePrivilege('manage_order_goods_update_set_memo');
                $updateValueArray['memo'] = $validator->validate('memo');
                break;
            case 'set_refund':
                // 权限检查
                $this->requirePrivilege('manage_order_goods_update_set_refund');
                $updateValueArray['refund']                = Money::toStorage($validator->validate('refund'));
                $updateValueArray['refund_note']           = $validator->required()->validate('refund_note');
                $updateValueArray['refund_time']           = Time::gmTime();
                $updateValueArray['suppliers_refund']      = Money::toStorage($validator->validate('suppliers_refund'));
                $updateValueArray['suppliers_refund_note'] = $validator->required()->validate('suppliers_refund_note');
                break;
            case 'set_extra_refund':
                // 权限检查
                $this->requirePrivilege('manage_order_goods_update_set_extra_refund');
                $updateValueArray['extra_refund']      = Money::toStorage($validator->validate('extra_refund'));
                $updateValueArray['extra_refund_note'] = $validator->required()->validate('extra_refund_note');
                $updateValueArray['extra_refund_time'] = Time::gmTime();
                break;
            default:
                // 非法的 action
                goto out;
        }

        if (!$this->validate($validator)) {
            goto out;
        }

        // 取 order_goods
        $orderBasicService = new OrderBasicService();
        $orderGoods        = $orderBasicService->loadOrderGoodsById($rec_id);
        if ($orderGoods->isEmpty()) {
            $this->addFlashMessage('非法订单');
            goto out_fail;
        }

        // 取得 orderInfo
        $orderInfo = $orderBasicService->loadOrderInfoById($orderGoods['order_id']);

        // 针对不同的 action  做额外不同的工作
        $action_note = '';
        switch ($action) {

            case 'set_extra_discount':
                // 商品只有是未付款状态才可以设置额外优惠
                if (OrderGoodsService::OGS_UNPAY != $orderGoods['order_goods_status']) {
                    $this->addFlashMessage('只有未付款订单才可以给予额外优惠');
                    goto out;
                }

                // 额外优惠允许的最大金额
                $allowExtraDiscount =
                    $orderGoods['goods_price'] + $orderGoods['shipping_fee'] - $orderGoods['discount'];
                $maxExtraDiscount   =
                    intval($allowExtraDiscount * $f3->get('sysConfig[max_order_goods_extra_discount_rate]'));
                $maxExtraDiscount   =
                    max($maxExtraDiscount, $f3->get('sysConfig[max_order_goods_extra_discount_value]'));
                $maxExtraDiscount   = min($maxExtraDiscount, $allowExtraDiscount);

                // 额外优惠不能超过商品本身的金额
                if ($updateValueArray['extra_discount'] > $maxExtraDiscount) {
                    $this->addFlashMessage('额外优惠不能超过商品总金额 ' . $maxExtraDiscount);
                    goto out;
                }
                // 设置额外余额，需要重新计算 order_info 中的值
                $diffDiscount = 0;
                if ($orderGoods->extra_discount != $updateValueArray['extra_discount']) {
                    $diffDiscount = $updateValueArray['extra_discount'] - $orderGoods->extra_discount;
                }
                $orderInfo->extra_discount += $diffDiscount;
                $orderInfo->order_amount -= $diffDiscount;

                $action_note .= '额外优惠：' . Money::toSmartyDisplay($updateValueArray['extra_discount']) . "，";
                $action_note .= '优惠说明：' . $updateValueArray['extra_discount_note'] . "\n";
                break;

            case 'set_suppliers_price':
                $action_note .= '供货价：' . Money::toSmartyDisplay($updateValueArray['suppliers_price']) . "，";
                $action_note .= '供货快递费：' . Money::toSmartyDisplay($updateValueArray['suppliers_shipping_fee']) . "\n";
                break;

            case 'set_shipping_no':
                if ($updateValueArray['shipping_id'] > 0) {
                    //取得快递信息
                    $expressService = new ExpressService();
                    $expressInfo    = $expressService->loadMetaById($updateValueArray['shipping_id']);
                    if ($expressInfo->isEmpty() || ExpressService::META_TYPE != $expressInfo['meta_type']) {
                        $this->addFlashMessage('快递ID非法');
                        goto out;
                    }
                    $updateValueArray['shipping_name'] = $expressInfo['meta_name'];
                } else {
                    $updateValueArray['shipping_name'] = null;
                }
                $action_note .= '快递公司：' . $updateValueArray['shipping_name'] . "\n";
                $action_note .= '快递单号：' . $updateValueArray['shipping_no'] . "\n";
                break;

            case 'set_memo':
                $action_note .= '客服备注：' . $updateValueArray['memo'] . "\n";
                break;

            case 'set_refund':
                // 检查订单状态
                if (!in_array(
                    $orderGoods['order_goods_status'],
                    array(
                         OrderGoodsService::OGS_PAY,
                         OrderGoodsService::OGS_ASKREFUND
                    )
                )
                ) {
                    $this->addFlashMessage('订单状态非法，不能退款');
                    goto out;
                }

                if ($orderGoods['settle_id'] > 0) {
                    $this->addFlashMessage('已经结算的订单不能退款');
                    goto out;
                }

                // 订单设置为 申请退款
                $updateValueArray['order_goods_status'] = OrderGoodsService::OGS_ASKREFUND;

                // 同步更新 order_info 中的 refund 字段
                $diffRefund = 0;
                if ($orderGoods->refund != $updateValueArray['refund']) {
                    $diffRefund = $updateValueArray['refund'] - $orderGoods->refund;
                }
                $orderInfo->refund += $diffRefund;

                // 检查金额，对一些常见错误提出警告
                if (0 == $updateValueArray['refund']) {
                    $this->addFlashMessage(
                        '警告：你确定给顾客退款金额设置为 ' . Money::toSmartyDisplay($updateValueArray['refund']) . ' ?'
                    );
                }

                if (0 == $updateValueArray['suppliers_refund']) {
                    $this->addFlashMessage(
                        '警告：你确定供货商给我们退款金额为 ' . Money::toSmartyDisplay($updateValueArray['refund']) . ' ?'
                    );
                }

                if ($updateValueArray['refund'] <= $updateValueArray['suppliers_refund']) {
                    $this->addFlashMessage('警告：给顾客退款金额 &lt;= 供货商给我们的退款金额');
                }

                // 日志信息记录
                $action_note .= '申请退款' . "\n";
                $action_note .= '顾客金额：' . Money::toSmartyDisplay($updateValueArray['refund']) . "，";
                $action_note .= '顾客说明：' . $updateValueArray['refund_note'] . "\n";
                $action_note .= '供货商金额：' . Money::toSmartyDisplay($updateValueArray['suppliers_refund']) . "，";
                $action_note .= '供货商说明：' . $updateValueArray['suppliers_refund_note'] . "\n";
                break;

            case 'set_extra_refund':
                // 检查订单状态
                if (OrderGoodsService::OGS_UNPAY == $orderGoods['order_goods_status']) {
                    $this->addFlashMessage('订单状态非法，不能退款');
                    goto out;
                }

                $action_note .= '额外退款：' . Money::toSmartyDisplay($updateValueArray['extra_refund']) . "，";
                $action_note .= '退款说明：' . $updateValueArray['extra_refund_note'] . "\n";
                break;

            default:
                // 非法的 action
                goto out;
        }


        // 更新订单信息
        $orderGoods->copyFrom($updateValueArray);
        $orderGoods->update_time = Time::gmTime();
        $orderGoods->save();

        // 更新 order_info 的 update_time 字段
        $orderInfo->update_time = Time::gmTime();
        $orderInfo->save();

        // 添加订单操作日志
        $authAdminUser      = AuthHelper::getAuthUser();
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

        $this->addFlashMessage('订单信息保存成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::makeUrl('/Order/Goods/Detail', array('rec_id' => $rec_id), true));
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Order/Goods/Search', false);
    }

}
