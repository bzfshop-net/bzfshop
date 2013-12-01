<?php
/**
 * @author QiangYu
 *
 * OrderRefer 的一个基类，可以继承这个类然后重载你需要的方法
 *
 */

namespace Core\OrderRefer;


class AbstractOrderRefer implements IRefer
{

    public function setOrderRefer($f3, &$orderRefer, &$duration)
    {
        return false;
    }

    public function getOrderReferNotifyUrl($f3, $orderRefer)
    {
        return null;
    }

    public function notifyOrderRefer($f3, $orderRefer)
    {
        // do nothing
    }

    public function getCpsParam($orderRefer, $orderInfo, $orderGoods)
    {
        return false;
    }
}