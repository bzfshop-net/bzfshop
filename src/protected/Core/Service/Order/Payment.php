<?php

/**
 *
 * @author QiangYu
 *
 * 订单的基本操作
 *
 * */

namespace Core\Service\Order;

use Core\Cache\ClearHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;
use Core\Service\User\AccountLog;
use Core\Service\User\Bonus;

class Payment extends \Core\Service\BaseService
{

    /**
     * 标记订单已经支付
     *
     * @param int    $orderId 订单数字ID
     * @param int    $payId   支付类型，比如 4 代表财付通，5代表支付宝
     * @param string $payType 支付类型，比如 alipay, tenpay
     * @param string $payNo   支付方返回的交易编号，比如支付宝返回的交易号
     */
    public function markOrderInfoPay($orderId, $payId, $payType, $payNo, $note = '', $username = '买家')
    {
        global $logger;

        // 参数验证
        $validator = new Validator(array('orderId' => $orderId, 'payId' => $payId));
        $orderId   = $validator->required()->digits()->min(1)->validate('orderId');
        $payId     = $validator->required()->digits()->min(1)->validate('payId');
        $this->validate($validator);

        // 订单操作，需要保证事务
        $dbEngine = DataMapper::getDbEngine();
        try {
            $dbEngine->begin();

            // 更新 order_info
            $orderBasicService = new Order();
            $orderInfo         = $orderBasicService->loadOrderInfoById($orderId);

            if ($orderInfo->isEmpty()) {
                $logger->addLogInfo(
                    \Core\Log\Base::ERROR,
                    'PAYMENT',
                    __CLASS__ . '-' . __FUNCTION__ . ' invalid order_id [' . $orderId . ']'
                );
                throw new \InvalidArgumentException('invalid order_id [' . $orderId . ']');
            }

            $currentGmTime           = Time::gmTime();
            $orderInfo->order_status = Order::OS_CONFIRMED;
            $orderInfo->pay_status   = Order::PS_PAYED;
            $orderInfo->update_time  = $currentGmTime;
            $orderInfo->confirm_time = $currentGmTime;
            $orderInfo->pay_time     = $currentGmTime;
            $orderInfo->pay_id       = $payId;
            $orderInfo->pay_type     = $payType;
            $orderInfo->pay_no       = $payNo;
            $orderInfo->money_paid   = $orderInfo->order_amount;

            $orderInfo->save();

            // 更新 order_goods
            $orderGoodsService = new Goods();
            $orderGoodsService->markOrderGoodsPay($orderInfo);

            if (empty($note)) {
                $note = '[' . $payType . ']付款确认';
            }

            //记录订单操作日志
            $orderActionService = new Action();
            $orderActionService->logOrderAction(
                $orderId,
                0,
                Order::OS_CONFIRMED,
                Order::PS_PAYED,
                Goods::OGS_PAY,
                $note,
                $username,
                0,
                $orderInfo['shipping_status']
            );

            // 提交事务
            $dbEngine->commit();

            // 记录成功日志
            $logger->addLogInfo(
                \Core\Log\Base::INFO,
                'PAYMENT',
                __CLASS__ . '-' . __FUNCTION__ . ' success order_id [' . $orderId . ']'
            );

        } catch (Exception $e) {
            // 记录异常日志
            $logger->addLogInfo(
                \Core\Log\Base::ERROR,
                'PAYMENT',
                print_r($e->getTrace(), true)
            );
            $dbEngine->rollback();
        }

        // 由于商品库存发生变化，我们需要清除商品缓存，显示新的库存
        // 注意： 这个操作绝对不能在前面的 Transaction 中操作，防止对数据库性能造成巨大影响
        $orderGoodsArray = $orderBasicService->fetchOrderGoodsArray($orderId);
        $goodsIdArray    = array();
        foreach ($orderGoodsArray as $orderGoodsItem) {
            $goodsIdArray[] = $orderGoodsItem['goods_id'];
        }
        $goodsIdArray = array_unique($goodsIdArray);
        foreach ($goodsIdArray as $goodsId) {
            // 清除商品的缓存，确保库存数据显示是正确的
            ClearHelper::clearGoodsCacheById($goodsId);
        }
    }

    /**
     * 取消还没有支付的订单，订单状态必须是 OS_UNCONFIRMED 才可以取消
     *
     * @return boolean
     *
     * @param int $orderid 订单ID
     */
    public function cancelUnpayOrderInfo($orderId)
    {
        // 参数验证
        $validator = new Validator(array('orderId' => $orderId));
        $orderId   = $validator->required()->digits()->min(1)->validate('orderId');
        $this->validate($validator);

        $orderBasicService = new Order();
        $orderInfo         = $orderBasicService->loadOrderInfoById($orderId);
        if ($orderInfo->isEmpty() || Order::OS_UNCONFIRMED != $orderInfo['order_status']) {
            return false;
        }

        if ($orderInfo->surplus > 0) {
            // 如果使用了余额支付，退还余额
            $accountLog = new AccountLog();
            $accountLog->logChange(
                $orderInfo['user_id'],
                $orderInfo->surplus,
                0,
                0,
                0,
                '退还余额，订单：' . $orderInfo['order_id'],
                AccountLog::ACT_OTHER
            );
            $orderInfo->order_amount += $orderInfo->surplus; // 修复 order_amount 的值
            $orderInfo->surplus = 0;
        }

        if ($orderInfo->bonus_id > 0) {
            // 使用了红包支付，退还红包
            $bonusService = new Bonus();
            $bonusService->unUseBonus($orderInfo->bonus_id);
            $orderInfo->order_amount += $orderInfo->bonus; // 修复 order_amount 的值
            $orderInfo->bonus_id = 0;
            $orderInfo->bonus    = 0;
        }

        $orderInfo->order_status = Order::OS_CANCELED; // 设置为取消订单
        $orderInfo->save();

        return true;
    }

}
