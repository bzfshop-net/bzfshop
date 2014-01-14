<?php
/**
 * @author QiangYu
 *
 * 商品的定时操作
 *
 */

namespace Core\Cron;

use Core\Cache\ClearHelper;
use Core\Service\Goods\Goods as GoodsBasicService;

class GoodsCronTask implements ICronTask
{

    public static $task_name = '商品定时操作';

    public static $actionDesc = array(
        'Online'  => '商品上架',
        'Offline' => '商品下架',
    );

    public function Online(array $paramArray)
    {
        $resultArray = array('code' => '-1', 'message' => '参数错误');

        $goods_id = abs(intval(@$paramArray['goods_id']));

        $goodsBasicService = new GoodsBasicService();
        $goods             = $goodsBasicService->loadGoodsById($goods_id);

        if ($goods->isEmpty()) {
            goto out;
        }

        $goods->is_on_sale = 1;
        $goods->save();

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goods->goods_id);

        $resultArray = array('code' => '0', 'message' => '商品[' . $goods_id . ']成功上架');

        out:
        return $resultArray;
    }

    public function Offline(array $paramArray)
    {
        $resultArray = array('code' => '-1', 'message' => '参数错误');

        $goods_id = abs(intval(@$paramArray['goods_id']));

        $goodsBasicService = new GoodsBasicService();
        $goods             = $goodsBasicService->loadGoodsById($goods_id);

        if ($goods->isEmpty()) {
            goto out;
        }

        $goods->is_on_sale = 0;
        $goods->save();

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goods->goods_id);

        $resultArray = array('code' => '0', 'message' => '商品[' . $goods_id . ']成功下架');

        out:
        return $resultArray;
    }

    public function run(array $paramArray)
    {
        $resultArray = array('code' => '-1', 'message' => '不认识的操作');

        if (!@$paramArray['action']) {
            goto out;
        }

        if (method_exists($this, $paramArray['action'])) {
            $resultArray = call_user_func_array(array($this, $paramArray['action']), array($paramArray));
        }

        out:
        return $resultArray;
    }
}