<?php

/**
 * @author QiangYu
 *
 * 我的订单控制器
 *
 * */

namespace Controller\My;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Request;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Plugin\ThemeHelper;
use Core\Search\SearchHelper;
use Core\Service\Cart\Cart as CartBasicService;
use Core\Service\Goods\Comment as GoodsCommentService;
use Core\Service\Order\Goods as OrderGoodsService;
use Core\Service\Order\Order as OrderBasicService;
use Core\Service\Order\Payment as OrderPaymentService;

class Order extends \Controller\AuthController
{
    private $searchExtraCondArray;

    public function __construct()
    {
        $currentThemeInstance = ThemeHelper::getCurrentSystemThemeInstance();

        // 我们只搜索特定的系统的订单
        $this->searchExtraCondArray = array(
            array(QueryBuilder::buildOrderFilterForSystem($currentThemeInstance->getOrderFilterSystemArray())),
        );
    }

    public function get($f3)
    {
        global $smarty;

        $myOrderTitleDesc = array(
            0 => '未付款订单',
            1 => '已付款订单',
        );

        // 构造查询条件
        $searchFormQuery = array();

        // 参数验证
        $validator   = new Validator($f3->get('GET'));
        $orderStatus = $f3->get('GET[orderStatus]');
        if (isset($orderStatus)) {
            $orderStatus = $searchFormQuery['order_status']
                = $validator->digits('订单状态非法')->min(0)->filter('ValidatorIntValue')->validate('orderStatus');
        }
        $pageNo   = $validator->digits()->min(0)->validate('pageNo');
        $pageSize = $validator->digits()->min(0)->validate('pageSize');

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        if (!$this->validate($validator)) {
            goto out_display;
        }

        $myOrderTitle = '全部订单';
        if (isset($orderStatus)) {
            $myOrderTitle = $myOrderTitleDesc[$orderStatus];
        }
        $smarty->assign('myOrderTitle', $myOrderTitle);

        $userInfo = AuthHelper::getAuthUser();

        // 查询订单
        $searchFormQuery['user_id'] = $userInfo['user_id'];

        // 合并查询参数
        $searchParamArray =
            array_merge(QueryBuilder::buildSearchParamArray($searchFormQuery), $this->searchExtraCondArray);

        $totalCount = SearchHelper::count(SearchHelper::Module_OrderInfo, $searchParamArray);
        if ($totalCount <= 0) { // 没订单，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/My/Order');
        }

        // 订单排序
        $orderByParam   = array();
        $orderByParam[] = array('order_id', 'desc');

        // 订单列表
        $orderInfoArray =
            SearchHelper::search(
                SearchHelper::Module_OrderInfo,
                '*',
                $searchParamArray,
                $orderByParam,
                $pageNo * $pageSize,
                $pageSize
            );

        foreach ($orderInfoArray as &$orderInfoItem) {
            $orderInfoItem['order_status_desc'] = OrderBasicService::$orderStatusDesc[$orderInfoItem['order_status']];
            $orderInfoItem['pay_status_desc']   = OrderBasicService::$payStatusDesc[$orderInfoItem['pay_status']];
        }
        unset($orderInfoItem);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('orderInfoArray', $orderInfoArray);

        out_display:
        $smarty->display('my_order.tpl', 'get');
    }

    public function post($f3)
    {
        $this->get($f3);
    }

    private function verifyOrderSystem($orderInfo)
    {
        $currentThemeInstance = ThemeHelper::getCurrentSystemThemeInstance();
        return in_array($orderInfo['system_id'], $currentThemeInstance->getOrderFilterSystemArray());
    }

    /**
     * 订单详情
     */
    public function Detail($f3)
    {
        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $order_id  = $validator->required('订单ID非法')->digits('订单ID非法')->min(1, true, '订单ID非法')->validate('order_id');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        $userInfo = AuthHelper::getAuthUser();

        // 查询订单
        $orderBasicService = new OrderBasicService();
        $orderInfo         = $orderBasicService->loadOrderInfoById($order_id, 10); // 缓存 10 秒钟

        if ($orderInfo->isEmpty() || $userInfo['user_id'] != $orderInfo['user_id']
            || !$this->verifyOrderSystem($orderInfo)
        ) {
            $this->addFlashMessage('订单ID非法');
            goto out_fail;
        }
        $orderInfo['order_status_desc'] = OrderBasicService::$orderStatusDesc[$orderInfo['order_status']];
        $orderInfo['pay_status_desc']   = OrderBasicService::$payStatusDesc[$orderInfo['pay_status']];
        $orderGoodsArray                = $orderBasicService->fetchOrderGoodsArray($order_id, 10); // 缓存 10 秒钟

        // 转换商品状态显示
        foreach ($orderGoodsArray as &$orderGoodsItem) {
            $orderGoodsItem['order_goods_status_desc'] =
                OrderGoodsService::$orderGoodsStatusDesc[$orderGoodsItem['order_goods_status']];
        }
        unset($orderGoodsItem);

        // 给模板赋值
        $smarty->assign('orderInfo', $orderInfo);
        $smarty->assign('orderGoodsArray', $orderGoodsArray);

        $smarty->display('my_order_detail.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/My/Order');
    }

    /**
     * 订单商品评价
     *
     * @param $f3
     */
    public function GoodsComment($f3)
    {
        global $smarty;

        $errorMessage = '';

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $rec_id    = $validator->required()->digits()->min(1)->validate('rec_id');

        if (!$this->validate($validator)) {
            $errorMessage = '订单ID非法';
            goto out_fail;
        }

        $orderBasicService = new OrderBasicService();
        // 查询 order_goods
        $orderGoods = $orderBasicService->loadOrderGoodsById($rec_id, 10); // 缓存 10 秒钟
        if ($orderGoods->isEmpty()) {
            $errorMessage = '订单ID非法';
            goto out_fail;
        }

        // 查询 order_info
        $orderInfo = $orderBasicService->loadOrderInfoById($orderGoods['order_id'], 10); // 缓存 10 秒钟

        // 权限检查，用户只能查看自己的订单
        $userInfo = AuthHelper::getAuthUser();
        if ($orderInfo->isEmpty() || $userInfo['user_id'] != $orderInfo['user_id']
            || !$this->verifyOrderSystem($orderInfo)
        ) {
            $errorMessage = '订单ID非法';
            goto out_fail;
        }

        // 加载订单评论
        $goodsCommentService = new GoodsCommentService();
        $goodsComment        = $goodsCommentService->loadGoodsCommentByOrderGoodsRecId($rec_id, 1); // 缓存1秒
        if ($goodsComment->isEmpty() || $goodsComment['user_id'] != $userInfo['user_id']) {
            $errorMessage = '无法评论此订单';
            goto out_fail;
        }

        // post 请求
        if (Request::isRequestPost()) {
            goto do_post;
        }

        // 赋值评论信息
        $smarty->assign('goodsComment', $goodsComment->toArray());

        out_fail: // GET 从这里退出
        $smarty->assign('errorMessage', $errorMessage);
        $smarty->display('my_order_goodscomment.tpl');
        return;

        do_post: // 这里处理 post 请求

        // 用户评论缺省不显示，需要等管理员审核通过才能显示
        $goodsComment->is_show      = 0;
        $goodsComment->comment_time = Time::gmTime();

        // 过滤用户提交的数据
        unset($validator);
        $validator                  = new Validator($f3->get('POST'));
        $goodsComment->comment_rate = $validator->filter('ValidatorIntValue')->validate('comment_rate');
        $goodsComment->comment      = $validator->validate('comment');

        $goodsComment->save();

        $this->addFlashMessage('评论发表成功，请等待管理员审核通过才能显示');

        // 回到前面的页面
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    /**
     * 取消订单
     */
    public function Cancel($f3)
    {
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $order_id  = $validator->required('订单ID非法')->digits('订单ID非法')->min(1, true, '订单ID非法')->validate('order_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        $userInfo = AuthHelper::getAuthUser();

        // 查询订单
        $orderBasicService = new OrderBasicService();
        $orderInfo         = $orderBasicService->loadOrderInfoById($order_id, 1); // 缓存 1 秒钟

        if ($orderInfo->isEmpty() || $userInfo['user_id'] != $orderInfo['user_id']
            || OrderBasicService::OS_UNCONFIRMED != $orderInfo['order_status']
            || !$this->verifyOrderSystem($orderInfo)
        ) {
            $this->addFlashMessage('订单ID非法');
            goto out;
        }

        // 取消订单
        $orderPaymentService = new OrderPaymentService();
        if ($orderPaymentService->cancelUnpayOrderInfo($order_id)) {
            // 重新加载 authUser，因为退款可能导致用户 余额 之类发生变化
            //AuthHelper::reloadAuthUser();
        }

        $this->addFlashMessage('成功取消订单');

        out: // 从这里退出
        RouteHelper::reRoute($this, '/My/Order');
    }

    /**
     * 把订单加载到购物车
     */
    public function Cart($f3)
    {
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $order_id  = $validator->required('订单ID非法')->digits('订单ID非法')->min(1, true, '订单ID非法')->validate('order_id');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        $userInfo = AuthHelper::getAuthUser();

        // 查询订单
        $orderBasicService = new OrderBasicService();
        $orderInfo         = $orderBasicService->loadOrderInfoById($order_id, 10); // 缓存 10 秒钟

        if ($orderInfo->isEmpty() || $userInfo['user_id'] != $orderInfo['user_id']
            || OrderBasicService::OS_UNCONFIRMED != $orderInfo['order_status']
            || !$this->verifyOrderSystem($orderInfo)
        ) {
            $this->addFlashMessage('订单ID非法');
            goto out_fail;
        }

        //加载订单
        $cartBasicService = new CartBasicService();
        // 加载订单到购物车里
        if (!$cartBasicService->loadFromOrderInfo($order_id)) {
            $this->addFlashMessage('订单加载失败');
            goto out_fail;
        }

        $cartContext = & $cartBasicService->getCartContextRef();
        if ($cartContext->isEmpty()) {
            $this->addFlashMessage('订单为空，不能支付');
            goto out_fail;
        }

        // 保存购物车
        $cartBasicService->syncStorage();

        RouteHelper::reRoute($this, '/Cart/Show');
        return; // 成功从这里返回

        out_fail: // 从这里退出
        RouteHelper::reRoute($this, '/My/Order');
    }

}
