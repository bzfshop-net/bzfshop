<?php

/**
 * @author QiangYu
 *
 * 购物车的业务逻辑实现
 */

namespace Core\Service\Cart;

use Core\Base\PipelineHelperAnd;
use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;
use Core\OrderRefer\ReferHelper;
use Core\Service\Order\Action as OrderActionService;
use Core\Service\Order\Goods as OrderGoodsService;
use Core\Service\Order\Order as OrderBasicService;
use Core\Service\Order\Refer as OrderReferService;
use Core\Service\User\AccountLog;
use Core\Service\User\Bonus;

/*********************************** 定义购物车使用的计算 Pipeline ********************************************/

// 1. 购物为计算做准备，比如加载计算数据，用于后面的计算
abstract class CartPrepareCalcHelper extends PipelineHelperAnd
{
    // 所有 instance 类型必须都是 IPipeline 接口的实现
    protected static $instanceType = '\Core\Base\IPipeline';

    // 注册 class 对应的 name --> initParam
    protected static $registerClassArray = array(
        '\Core\Service\Cart\ResetCalcPipeline'     => array(), // 清除上一次计算的结果
        '\Core\Service\Cart\LoadDataPipeline'      => array(), // 加载计算数据
        '\Core\Service\Cart\ValidateGoodsPipeline' => array(), // 验证商品是否可以购买
    );
}

// 2. 确定商品的单价
//    比如：根据用户选择商品的不同属性（比如：红色XL码），计算这个属性的价格
//    比如：用于特殊商品促销，比如这个商品标记为“搭配购买便宜xxx元"，这里可以计算单品的“特殊单价”
abstract class CartGoodsSpecPriceCalcHelper extends PipelineHelperAnd
{
    // 所有 instance 类型必须都是 IPipeline 接口的实现
    protected static $instanceType = '\Core\Base\IPipeline';

    // 注册 class 对应的 name --> initParam
    protected static $registerClassArray = array(
        '\Core\Service\Cart\GoodsSpecPipeline' => array(), // 计算商品不同的选择对应的不同价格（比如：红色XL码贵2元钱）
    );
}

// 3. 计算单个商品的总价，比如这个商品买了 2 个，总价 = 单价 x 数量
abstract class CartGoodsOnePriceCalcHelper extends PipelineHelperAnd
{
    // 所有 instance 类型必须都是 IPipeline 接口的实现
    protected static $instanceType = '\Core\Base\IPipeline';

    // 注册 class 对应的 name --> initParam
    protected static $registerClassArray = array(
        '\Core\Service\Cart\GoodsOnePipeline'             => array(), //同一个商品的不同组合只收一份邮费，计算单个商品的总价格
        '\Core\Service\Cart\ShippingFreeNumberPipeline'   => array(), //商品购买超过 shipping_free_number 则免邮费
        '\Core\Service\Cart\SameSupplierShippingPipeline' => array(), //同一个供货商提供的商品只收一份邮费
    );
}

// 4. 计算整个订单的价格
abstract class CartOrderTotalPriceCalcHelper extends PipelineHelperAnd
{
    // 所有 instance 类型必须都是 IPipeline 接口的实现
    protected static $instanceType = '\Core\Base\IPipeline';

    // 注册 class 对应的 name --> initParam
    protected static $registerClassArray = array(
        '\Core\Service\Cart\TotalPricePipeline' => array(), //计算总商品价格
    );
}

// 5. 计算整个订单实际应该支付的金额
abstract class CartOrderPaymentCalcHelper extends PipelineHelperAnd
{
    // 所有 instance 类型必须都是 IPipeline 接口的实现
    protected static $instanceType = '\Core\Base\IPipeline';

    // 注册 class 对应的 name --> initParam
    protected static $registerClassArray = array(
        '\Core\Service\Cart\CreditPayPipeline' => array(), //使用了余额、红包 等支付手段的计算
    );
}

/*********************************** /定义购物车使用的计算 Pipeline ********************************************/

class Cart extends \Core\Service\BaseService
{

    /**
     * 用于记录订单是来自于哪个系统的，比如 团购 Groupon，手机端 Mobile，...
     */
    public static $cartSystemId = null;

    // 购物车数据结构存储的 Key
    public static $cartContextStorageKey = 'SESSION[CartContextInfo_FWGFWGF242543Trgg]';

    // 购物车里面有多少商品的 Key
    public static $cartGoodsCountStorageKey = 'SESSION[CartGoodsCount_FWGFWGF242543Trgg]';

    // 购物车数据结构
    private $cartContext;

    public function __construct()
    {
        $this->cartContext = new CartContext();
    }

    public static function getCartOrderGoodsNumber()
    {
        global $f3;
        return $f3->get(Cart::$cartGoodsCountStorageKey);
    }

    /**
     * 取得整个购物车的数据
     *
     * @return object
     */
    public function getCartContext()
    {
        return $this->cartContext;
    }

    /**
     * 返回购物车数据 reference，你可以在你的程序里面修改购物的数据结构
     * 使用这个函数要小心，不然会破坏整个购物车的数据结构
     *
     * @return object cartContext 对象 购物车内部数据的引用
     */
    public function &getCartContextRef()
    {
        return $this->cartContext;
    }

    /**
     * 同步购物车到存储系统，确保数据都保存了，目前我们使用 Session 来存储购物车数据
     */
    public function syncStorage()
    {
        global $f3;
        $cartContext = $this->cartContext;

        //清除一些不需要的数据，减小保存的数据量
        foreach ($cartContext->orderGoodsArray as &$orderGoodsItem) {
            $orderGoodsItem->goods = null;
        }
        unset($orderGoodsItem);

        $f3->set(Cart::$cartContextStorageKey, serialize($cartContext));

        // 计算用户买了多少个商品
        $totalGoodsNumber = 0;
        foreach ($cartContext->orderGoodsArray as $orderGoodsItem) {
            $totalGoodsNumber += $orderGoodsItem->getValue('goods_number');
        }
        $f3->set(Cart::$cartGoodsCountStorageKey, $totalGoodsNumber);
    }

    /**
     * 从保存购物车信息的地方把数据加载上来，目前购物车数据都保存在 Session 中
     */
    public function loadFromStorage()
    {
        global $f3;
        $cartContextStr = $f3->get(Cart::$cartContextStorageKey);
        if (!empty($cartContextStr)) {
            $this->cartContext = unserialize($cartContextStr);
        }
    }

    /**
     * 清除购物车自身的数据
     */
    public function clear()
    {
        return $this->cartContext->clear();
    }

    /**
     * 清除购物车自身的数据以及保存的数据
     */
    public function clearStorage()
    {
        global $f3;
        $this->clear();
        $f3->set(Cart::$cartContextStorageKey, null);
        $f3->set(Cart::$cartGoodsCountStorageKey, 0);
    }


    /**
     * 计算订单的金额，这里不包括支付
     */
    public function calcOrderPrice()
    {
        CartPrepareCalcHelper::execute($this) // 加载计算资源
        && CartGoodsSpecPriceCalcHelper::execute($this) // 计算商品的单价
        && CartGoodsOnePriceCalcHelper::execute($this) // 计算商品的总价
        && CartOrderTotalPriceCalcHelper::execute($this);
        // 计算整个订单的总价
    }

    /**
     * 计算订单实际需要支付的金额，比如：用户如果使用了积分、红包支付，这里支付金额就少了
     */
    public function calcOrderPayment()
    {
        // 计算实际需要支付的金额
        CartOrderPaymentCalcHelper::execute($this);
    }

    /**
     * 把购物车中的信息保存到数据库订单中，如果订单已经存在，则更新订单数据，
     * 注意：这个方法必须在购物车计算完成之后调用，否则无法正确保存计算之后的数据
     *
     * @return mixed 失败返回 false，成功返回订单记录
     *
     * @param int    $userId   用户的数字 ID
     * @param string $username 用户名
     */
    public function saveOrder($userId, $username)
    {

        // 参数验证
        $validator = new Validator(array('userId' => $userId));
        $userId    = $validator->required()->digits()->min(1)->validate('userId');
        $this->validate($validator);

        $orderBasicService = new OrderBasicService();

        $orderId = isset($this->cartContext->orderInfo) ? $this->cartContext->orderInfo['order_id'] : null;

        // 不允许创建空订单
        if (!$orderId && $this->cartContext->isEmpty()) {
            return false;
        }

        // 是否需要新建订单
        $isNewOrder = false;

        //检查这个 order_info 是否合法
        if ($orderId) {
            $orderInfo = $orderBasicService->loadOrderInfoById($orderId);
            // 不存在的 order_info 或者 已经付款了的订单不能再用了
            if ($orderInfo->isEmpty() || OrderBasicService::PS_UNPAYED != $orderInfo->pay_status) {
                $orderId    = null;
                $isNewOrder = true;
            }
        } else {
            $isNewOrder = true;
        }

        // 创建 order_info 记录
        $orderInfoArray                 = $this->cartContext->orderInfoValue;
        $orderInfoArray['user_id']      = $userId;
        $orderInfoArray['order_status'] = OrderBasicService::OS_UNCONFIRMED;
        $orderInfoArray['pay_status']   = OrderBasicService::PS_UNPAYED;

        if (!$orderId) { // 新订单，生成 add_time 和 order_sn
            $orderInfoArray['add_time']  = Time::gmTime();
            $orderInfoArray['order_sn']  = OrderBasicService::generateOrderSn();
            $orderInfoArray['system_id'] = Cart::$cartSystemId; // 记录订单来自于哪个系统
        } elseif ($this->cartContext->isOrderAmountChange()) {
            // 去支付网关支付，如果订单金额发生变化都需要重新生成一次 order_sn ，因为订单金额如果发生变化，支付网关会拒绝支付
            $orderInfoArray['order_sn'] = OrderBasicService::generateOrderSn();
        } else {
            // do nothing
        }

        // 我们需要数据库事务的保障
        $dbEngine = DataMapper::getDbEngine();
        $dbEngine->begin();

        // 记录余额支付
        $oldSurplus = ($this->cartContext->orderInfo) ? $this->cartContext->orderInfo['surplus'] : 0;
        $newSurplus = ($this->cartContext->getValue('surplus') > 0) ? $this->cartContext->getValue('surplus') : 0;

        // 记录使用红包
        $oldBonusId = ($this->cartContext->orderInfo) ? $this->cartContext->orderInfo['bonus_id'] : 0;
        $newBonusId = ($this->cartContext->getValue('bonus_id') > 0) ? $this->cartContext->getValue('bonus_id') : 0;

        // 创建订单或者更新 order_info
        $orderInfo = $orderBasicService->saveOrderInfo($orderId, $orderInfoArray);

        // 创建订单失败，返回 false
        if ($orderInfo->isEmpty()) {
            goto out_fail;
        }

        // 处理余额支付
        if ($oldSurplus != $newSurplus) { // 前后发生了支付余额的改变
            $accountLog = new AccountLog();
            if ($oldSurplus > 0) {
                // 把之前的余额退还
                $accountLog->logChange(
                    $userId,
                    $oldSurplus,
                    0,
                    0,
                    0,
                    '退还余额，订单：' . $orderInfo['order_id'],
                    AccountLog::ACT_OTHER
                );
            }
            if ($newSurplus > 0) {
                // 使用余额
                $accountLog->logChange(
                    $userId,
                    (-1) * $newSurplus,
                    0,
                    0,
                    0,
                    '使用余额，订单：' . $orderInfo['order_id'],
                    AccountLog::ACT_OTHER
                );
            }
            // 由于修改了用户信息，需要 reload 用户数据
            AuthHelper::reloadAuthUser();
        }

        // 处理红包支付
        if ($oldBonusId != $newBonusId) { // 红包支付前后发生了变化
            $bonusService = new Bonus();
            if ($oldBonusId > 0) {
                //退还之前使用的红包
                $bonusService->unUseBonus($oldBonusId);
            }
            if ($newBonusId > 0) {
                // 使用新的红包
                $bonusService->useBonus($newBonusId, $orderInfo['order_id']);
            }
        }

        // 创建 orderRefer 对象，记录订单的来源
        $orderReferService = new OrderReferService();
        $orderRefer        = $orderReferService->loadOrderReferByOrderId($orderInfo['order_id']);

        // order_refer 记录创建一次之后就不会再修改了
        if ($orderRefer->isEmpty()) {
            // 新的 order_refer 记录，设置 order_id 值
            $orderRefer->order_id    = $orderInfo['order_id'];
            $orderRefer->create_time = Time::gmTime();
            $orderRefer->login_type  = AuthHelper::getLoginType();

            global $f3;
            $orderReferArray = ReferHelper::parseOrderRefer($f3);

            if (!empty($orderReferArray)) {
                unset($orderReferArray['refer_id']); //清除掉危险字段
                unset($orderReferArray['order_id']); //清除掉危险字段
                $orderRefer->copyFrom($orderReferArray);
            }
            $orderRefer->save(); // 保存 order_refer 记录
        }


        // 取得数据库中已经存在的 order_goods 列表
        $dbOrderGoodsArray = array();
        if ($orderId > 0) {
            $dbOrderGoodsArray = $orderBasicService->fetchOrderGoodsArray($orderId);
        }
        $stillExistOrderGoodsIdArray = array(); // 在购物车中仍然存在的 orderGoods 对象

        // 创建 orderInfo 对应的 order_goods 对象
        foreach ($this->cartContext->orderGoodsArray as $orderGoodsItem) {
            $orderGoodsValueArray             = array();
            $orderGoodsValueArray['order_id'] = $orderInfo['order_id'];
            //从 goods 中复制属性
            $orderGoodsValueArray['goods_id']               = $orderGoodsItem->goods['goods_id'];
            $orderGoodsValueArray['goods_admin_user_id']    = $orderGoodsItem->goods['admin_user_id'];
            $orderGoodsValueArray['goods_admin_user_name']  = $orderGoodsItem->goods['admin_user_name'];
            $orderGoodsValueArray['goods_name']             = $orderGoodsItem->goods['goods_name_short'];
            $orderGoodsValueArray['goods_sn']               = $orderGoodsItem->goods['goods_sn'];
            $orderGoodsValueArray['warehouse']              = $orderGoodsItem->goods['warehouse'];
            $orderGoodsValueArray['shelf']                  = $orderGoodsItem->goods['shelf'];
            $orderGoodsValueArray['market_price']           = $orderGoodsItem->goods['market_price'];
            $orderGoodsValueArray['shop_price']             = $orderGoodsItem->goods['shop_price'];
            $orderGoodsValueArray['shipping_fee']           = $orderGoodsItem->goods['shipping_fee'];
            $orderGoodsValueArray['is_real']                = $orderGoodsItem->goods['is_real'];
            $orderGoodsValueArray['extension_code']         = $orderGoodsItem->goods['extension_code'];
            $orderGoodsValueArray['suppliers_id']           = $orderGoodsItem->goods['suppliers_id'];
            $orderGoodsValueArray['suppliers_price']        = $orderGoodsItem->goods['suppliers_price'];
            $orderGoodsValueArray['suppliers_shipping_fee'] = $orderGoodsItem->goods['suppliers_shipping_fee'];

            // 合并一些因为计算而覆盖的值
            $orderGoodsValueArray = array_merge($orderGoodsValueArray, $orderGoodsItem->orderGoodsValue);

            $orderGoodsId = null;
            if (!$isNewOrder && $orderGoodsItem->orderGoods) {
                // 如果不是新订单，并且已经存在的 order_goods，做更新
                $orderGoodsId                  = $orderGoodsItem->orderGoods['rec_id'];
                $stillExistOrderGoodsIdArray[] = $orderGoodsId;
            } else {
                // 新建的 order_goods，我们需要取得它的 CPS 信息，CPS 信息只在一开始加入，后面不会再被修改了
                $cpsArray = ReferHelper::getCpsParam($orderRefer, $orderInfo, $orderGoodsValueArray);
                if (!empty($cpsArray)) {
                    $orderGoodsValueArray['cps_rate']    = isset($cpsArray['cps_rate']) ? $cpsArray['cps_rate'] : 0;
                    $orderGoodsValueArray['cps_fix_fee'] =
                        isset($cpsArray['cps_fix_fee']) ? $cpsArray['cps_fix_fee'] : 0;
                }
            }

            $orderGoods =
                $orderBasicService->saveOrderGoods($orderGoodsId, $orderInfo['order_id'], $orderGoodsValueArray);
            if ($orderGoods->isEmpty()) {
                //创建失败
                goto out_fail;
            }
        }

        // 删除已经被用户删除的 order_goods
        foreach ($dbOrderGoodsArray as $dbOrderGoodsItem) {
            // 这个 order_goods 已经不存在了，删除它
            if (!in_array($dbOrderGoodsItem['rec_id'], $stillExistOrderGoodsIdArray)) {
                $orderBasicService->removeOrderGoods($dbOrderGoodsItem['rec_id']);
            }
        }

        // 记录订单操作日志
        $orderActionService = new OrderActionService();
        $orderActionService->logOrderAction(
            $orderInfo['order_id'],
            0,
            OrderBasicService::OS_UNCONFIRMED,
            OrderBasicService::PS_UNPAYED,
            OrderGoodsService::OGS_UNPAY,
            '订单创建或更新',
            $username,
            0,
            0
        );

        $dbEngine->commit();
        return $orderInfo; // 返回创建的订单

        out_fail:
        $dbEngine->rollback();
        return false;
    }

    /**
     * 加载未支付的订单到购物车中，方便用于计算
     *
     * @return boolean
     *
     * @param int $orderId     订单 ID 号
     *
     */
    public function loadFromOrderInfo($orderId)
    {
        // 参数验证
        $validator = new Validator(array('orderId' => $orderId));
        $orderId   = $validator->required()->digits()->min(1)->validate('orderId');
        $this->validate($validator);

        $orderBasicService = new OrderBasicService();
        $orderInfo         = $orderBasicService->loadOrderInfoById($orderId);

        if ($orderInfo->isEmpty() || OrderBasicService::OS_UNCONFIRMED != $orderInfo['order_status']) {
            return false;
        }

        $orderGoodsArray = $orderBasicService->fetchOrderGoodsArray($orderId);
        if (empty($orderGoodsArray)) {
            // 空订单，不加载
            return false;
        }

        // 清除购物车中的数据
        $this->cartContext->clear();

        // 把订单商品加载到购物车中
        foreach ($orderGoodsArray as $orderGoodsItem) {
            $orderGoods                           = new OrderGoods();
            $orderGoods->orderGoods               = $orderGoodsItem;
            $this->cartContext->orderGoodsArray[] = $orderGoods;
        }

        // 订单加载
        $this->cartContext->orderInfo = $orderInfo->toArray();
        return true;
    }

}
