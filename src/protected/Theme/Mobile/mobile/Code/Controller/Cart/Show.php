<?php

/**
 * @author QiangYu
 *
 * 购物车处理
 *
 * */

namespace Controller\Cart;

use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Cart\Cart as CartBasicService;
use Core\Service\User\Address as UserAddressService;

class Show extends \Controller\BaseController
{

    public function get($f3)
    {
        // 用户没有登陆，让用户去登陆
        if (!AuthHelper::isAuthUser()) {
            // 如果已经记录了一个回跳 URL ，则不要再覆盖这个记录了
            RouteHelper::reRoute($this, '/User/Login', !RouteHelper::hasRememberUrl());
        }

        global $smarty;

        $cartBasicService = new CartBasicService();
        $cartBasicService->loadFromStorage(); // 加载购物车的数据
        $cartContext = & $cartBasicService->getCartContextRef();
        if ($cartContext->isEmpty()) {
            goto out_display;
        }

        // 做一次购物车计算
        $cartBasicService->calcOrderPrice();

        // 先看购物车里面有没有保存已经提交过的地址
        $addressInfo = $cartContext->getAddressInfo();

        if (empty($addressInfo)) {
            // 从数据库取用户地址
            $userInfo           = AuthHelper::getAuthUser();
            $userAddressService = new UserAddressService();
            $addressInfo        = $userAddressService->loadUserFirstAddress($userInfo['user_id'], 10); // 地址缓存 10 秒钟
            $addressInfo        = $addressInfo->toArray();
        }

        // 如果购物车里面有错误消息，我们需要显示它
        if ($cartContext->hasError()) {
            $this->addFlashMessageArray($cartContext->getAndClearErrorMessageArray());
            $cartBasicService->syncStorage();
        }

        // 给模板赋值
        $smarty->assign('cartContext', $cartContext);
        $smarty->assign('orderGoodsArray', $cartContext->orderGoodsArray);
        $smarty->assign($addressInfo);
        $smarty->assign('postscript', $cartContext->getValue('postscript'));

        out_display:
        $smarty->display('cart_show.tpl');
    }

    public function post($f3)
    {

        // 用户没有登陆，让用户去登陆
        if (!AuthHelper::isAuthUser()) {
            // 如果已经记录了一个回跳 URL ，则不要再覆盖这个记录了
            RouteHelper::reRoute($this, '/User/Login', !RouteHelper::hasRememberUrl());
        }

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('POST'));

        $addressInfo              = array();
        $addressInfo['consignee'] = $validator->required('姓名不能为空')->validate('consignee');
        $addressInfo['address']   = $validator->required('地址不能为空')->validate('address');
        $addressInfo['mobile']    = $validator->required('手机号码不能为空')->digits('手机号码格式不正确')->validate('mobile');
        $addressInfo['tel']       = $validator->validate('tel');
        $addressInfo['zipcode']   = $validator->digits('邮编格式不正确')->validate('zipcode');
        $postScript               = $validator->validate('postscript'); // 订单附言

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        $cartBasicService = new CartBasicService();
        $cartBasicService->loadFromStorage(); // 加载购物车的数据
        $cartContext = & $cartBasicService->getCartContextRef();
        if ($cartContext->isEmpty()) {
            $this->addFlashMessage('购物车为空');
            goto out_fail;
        }

        // 做一次购物车计算
        $cartBasicService->calcOrderPrice();

        // 如果购物车里面有错误消息，我们需要显示它
        if ($cartContext->hasError()) {
            $this->addFlashMessageArray($cartContext->getAndClearErrorMessageArray());
        }

        // 更新用户的地址信息
        $userInfo           = AuthHelper::getAuthUser();
        $userAddressService = new UserAddressService();
        $userAddressService->updateUserFirstAddress($userInfo['user_id'], $addressInfo);

        // 地址信息放入购物车结构
        $cartContext->setAddressInfo($addressInfo);
        // 订单附言放入购物车
        $cartContext->setValue('postscript', $postScript);

        // 创建或者更新订单
        $orderInfo = $cartBasicService->saveOrder($userInfo['user_id'], '买家：' . $userInfo['user_name']);

        if (!$orderInfo || $orderInfo->isEmpty()) {
            //订单创建失败，报错
            $this->addFlashMessage('订单创建失败，请联系客服');
            goto out_fail;
        }

        //订单创建成功，清空购物车
        $cartBasicService->clearStorage();

        // 跳转到支付页面
        RouteHelper::reRoute(
            $this,
            RouteHelper::makeUrl('/Cart/Pay', array('order_id' => $orderInfo['order_id']), true)
        );
        return;

        out_fail:
        RouteHelper::reRoute($this, '/Cart/Show');
    }

    /**
     * 从购物车中删除某个商品
     */
    public function Remove($f3)
    {

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('GET'));

        $goods_id    =
            $validator->required('商品ID不能为空')->digits('商品ID非法')->min(1, true, '商品ID非法')->validate('goods_id');
        $specListStr = $validator->validate('specListStr');

        if (!$this->validate($validator)) {
            goto out;
        }

        $cartBasicService = new CartBasicService();
        $cartBasicService->loadFromStorage(); // 加载购物车的数据
        $cartContext = & $cartBasicService->getCartContextRef();
        $cartContext->removeGoods($goods_id, $specListStr);
        $cartBasicService->syncStorage(); // 重新保存数据

        out:

        // ajax 操作
        if ($f3->get('GET[isAjax]')) {
            Ajax::header();
            if ($validator->hasErrors()) {
                $errorMessage = implode('|', $this->flashMessageArray);
                echo Ajax::buildResult(-1, $errorMessage, null);
                return;
            }

            echo Ajax::buildResult(null, null, null);
            return;
        }

        // 网页操作
        RouteHelper::reRoute($this, '/Cart/Show');
    }

    /**
     * 清空整个购物车
     */
    public function Clear()
    {
        $cartBasicService = new CartBasicService();
        $cartBasicService->clearStorage();

        RouteHelper::reRoute($this, '/Cart/Show');
    }

}
