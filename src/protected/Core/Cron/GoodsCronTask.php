<?php
/**
 * @author QiangYu
 *
 * 商品的定时操作
 *
 */

namespace Core\Cron;

use Core\Cache\ClearHelper;
use Core\Helper\Utility\Money;
use Core\Service\Goods\Goods as GoodsBasicService;

class GoodsCronTask implements ICronTask
{

    public static $task_name = 'goods';
    public static $actionDesc = array(
        'Online'   => '商品上架',
        'Offline'  => '商品下架',
        'setPrice' => '定时改价',
    );

    /**
     * 商品上架
     *
     * @param array $paramArray
     *
     * @return array
     */
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

    /**
     * 商品下架
     *
     * @param array $paramArray
     *
     * @return array
     */
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

    /**
     * 商品修改价格
     *
     * @param array $paramArray
     *
     * @return array
     */
    public function setPrice(array $paramArray)
    {
        $resultArray = array('code' => '-1', 'message' => '参数错误');

        $goods_id = abs(intval(@$paramArray['goods_id']));

        $goodsBasicService = new GoodsBasicService();
        $goods             = $goodsBasicService->loadGoodsById($goods_id);

        if ($goods->isEmpty()) {
            goto out;
        }

        // 更新商品字段
        $goodsUpdateFieldArray = @$paramArray['goods'];
        foreach ($goodsUpdateFieldArray as $field => $value) {

            if (in_array($field, array('goods_id'))) {
                // 安全考虑，一些字段不允许修改
                continue;
            }

            // 价格特殊处理
            if ('shop_price' == $field) {
                $goods->shop_price = Money::toStorage($value);
                continue;
            }
            $goods->$field = $value;
        }
        $goods->save();

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goods->goods_id);

        $resultArray = array('code' => '0', 'message' => '商品[' . $goods_id . ']属性修改成功');

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