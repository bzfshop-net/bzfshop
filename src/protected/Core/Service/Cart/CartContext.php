<?php

/**
 * @author QiangYu
 *
 * 包含了整个购物车的数据结构
 * 购物车的上下文执行环境，用于 pipeline 计算
 *
 */

namespace Core\Service\Cart;

class OrderGoods
{

    // 数据库中对应的商品信息，方便做计算，这里只是保存了一个简单的 array()，
    // 不是 PDO 对象，所以你无法用这里的数据做 $goods->save() 这样的操作
    public $goods = null;
    // 数据库中 order_goods 记录
    public $orderGoods = null;
    // 计算出来的 order_goods 的值
    public $orderGoodsValue = array();

    public function setValue($key, $value)
    {
        $this->orderGoodsValue[$key] = $value;
    }

    public function getValue($key)
    {
        if (isset($this->orderGoodsValue[$key])) {
            return $this->orderGoodsValue[$key];
        }
        if ($this->orderGoods && isset($this->orderGoods[$key])) {
            return $this->orderGoods[$key];
        }
        if ($this->goods && isset($this->goods[$key])) {
            return $this->goods[$key];
        }
        return null;
    }

}

class CartContext
{

    // 数据库中对应的 order_info 信息，方便做计算，这里只是保存了一个简单的 array()，
    // 不是 PDO 对象，所以你无法用这里的数据做 $goods->save() 这样的操作
    public $orderInfo = null;
    // 计算出来的 order_info 对应的值
    public $orderInfoValue = array();
    // 上面的 class OrderGoods 对象数组
    public $orderGoodsArray = array();

    // 放入出错信息，简单的字符串
    private $errorMessage = array();

    /**
     * 放入一个出错信息
     *
     * @param string $message
     */
    public function addErrorMessage($message)
    {
        $this->errorMessage[] = $message;
    }

    /**
     * 是否存在错误
     *
     * @return bool
     */
    public function hasError()
    {
        return !empty($this->errorMessage);
    }

    /**
     * 返回错误消息
     *
     * @return array
     */
    public function getAndClearErrorMessageArray()
    {
        $errorMessageArray  = $this->errorMessage;
        $this->errorMessage = array();
        return $errorMessageArray;
    }

    public function setValue($key, $value)
    {
        $this->orderInfoValue[$key] = $value;
    }

    public function getValue($key)
    {
        if (isset($this->orderInfoValue[$key])) {
            return $this->orderInfoValue[$key];
        }
        if ($this->orderInfo && isset($this->orderInfo[$key])) {
            return $this->orderInfo[$key];
        }
        return null;
    }

    /**
     * 判断订单总金额是否发生变化
     *
     * @return boolean
     */
    public function isOrderAmountChange()
    {
        // 浮点数不好做 == 比较，转化成字符串然后比较
        $oldValueStr = number_format($this->orderInfo['order_amount'], 2, '.', '');
        $newValueStr = number_format($this->getValue('order_amount'), 2, '.', '');
        return $newValueStr != $oldValueStr;
    }

    /**
     * 返回购物车保存的地址信息
     * @return array 地址信息
     */
    public function getAddressInfo()
    {
        $address = $this->getValue('address');
        if (empty($address)) {
            return null;
        }

        $addressInfo              = array();
        $addressInfo['consignee'] = $this->getValue('consignee');
        $addressInfo['address']   = $this->getValue('address');
        $addressInfo['mobile']    = $this->getValue('mobile');
        $addressInfo['tel']       = $this->getValue('tel');
        $addressInfo['zipcode']   = $this->getValue('zipcode');
        return $addressInfo;
    }

    /**
     * 设置地址信息
     *
     * @param array $addressInfo 地址信息
     *
     */
    public function setAddressInfo($addressInfo)
    {
        $this->orderInfoValue = array_merge($this->orderInfoValue, $addressInfo);
    }

    /**
     * 判断购物车是否为空
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->orderGoodsArray);
    }

    /**
     * 清空购物车
     */
    public function clear()
    {
        $this->orderInfo       = null;
        $this->orderInfoValue  = array();
        $this->orderGoodsArray = array();
    }

    /**
     * 从购物车中删除一个商品
     *
     * @param int    $goodsId   商品ID
     * @param string $specStr   商品组合列表，比如 "红色,XL,男款"
     */
    public function removeGoods($goodsId, $specStr = null)
    {
        $removeIndexArray = array();
        // 查找商品
        foreach ($this->orderGoodsArray as $index => $orderGoodsItem) {
            if ($goodsId == $orderGoodsItem->getValue('goods_id')) {
                if (!empty($specStr) && $specStr != $orderGoodsItem->getValue('goods_attr')) {
                    continue;
                }
                $removeIndexArray[] = $index; // 加入删除队列
            }
        }
        // 删除记录
        foreach ($removeIndexArray as $index) {
            unset($this->orderGoodsArray[$index]);
        }
    }

    /**
     * 往购物车里面增加一个商品
     *
     * @param int    $goodsId    商品ID
     * @param int    $buyCount   购买数量
     * @param string $specStr    商品组合列表 比如 "白色,ML码,男款"，用逗号分隔
     * @param string $goodsSn    商品的 sn 号
     */
    public function addGoods($goodsId, $buyCount, $specStr = null, $goodsSn = null)
    {
        // 先删除旧的记录
        $this->removeGoods($goodsId, $specStr);
        // 添加商品
        $orderGoods = new OrderGoods();
        $orderGoods->setValue('goods_id', $goodsId);
        $orderGoods->setValue('goods_number', $buyCount);
        $orderGoods->setValue('goods_attr', $specStr);
        if (!empty($goodsSn)) {
            $orderGoods->setValue('goods_sn', $goodsSn);
        }
        $this->orderGoodsArray[] = $orderGoods;

        // 对商品做排序
        $orderIdMapArray = array();
        foreach ($this->orderGoodsArray as $orderGoodsItem) {
            if (!isset($orderIdMapArray[$orderGoodsItem->getValue('goods_id')])) {
                $orderIdMapArray[$orderGoodsItem->getValue('goods_id')] = array();
            }
            $orderIdMapArray[$orderGoodsItem->getValue('goods_id')][] = $orderGoodsItem;
        }

        $this->orderGoodsArray = array();
        foreach ($orderIdMapArray as $array) {
            $this->orderGoodsArray = array_merge($this->orderGoodsArray, $array);
        }
        unset($orderIdMapArray);
    }

}