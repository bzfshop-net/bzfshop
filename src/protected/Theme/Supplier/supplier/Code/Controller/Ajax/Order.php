<?php

/**
 * @author QiangYu
 *
 * 订单对应的操作
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Service\Order\Goods as OrderGoodsService;

class Order extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    /******** 输出 order_goods_status 的状态列表 ********/
    public function ListOrderGoodsStatus($f3)
    {
        $outputArray = array();

        foreach (OrderGoodsService::$orderGoodsStatusDesc as $index => $desc) {
            $outputArray[] = array('id' => $index, 'desc' => $desc);
        }

        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $outputArray);
    }

}
