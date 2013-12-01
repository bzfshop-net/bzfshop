<?php

use Core\Modal\SqlMapper as DataMapper;
use Core\Service\BaseService;

class CalculateGoodsBuyNumber implements \Clip\Command
{

    public function run(array $params)
    {
        global $f3;

        // 每次处理多少条记录
        $batchProcessCount = 100;

        $baseService     = new BaseService();
        $totalGoodsCount = $baseService->_countArray('goods', null);

        // 记录处理开始
        for ($offset = 0; $offset < $totalGoodsCount; $offset += $batchProcessCount) {
            $goodsArray = $baseService->_fetchArray(
                'goods',
                'goods_id',
                null,
                array('order' => 'goods_id asc'),
                $offset,
                $batchProcessCount
            );
            foreach ($goodsArray as $goodsItem) {
                $sql      = "update " . DataMapper::tableName('goods') . ' set '
                    . ' user_buy_number = (select sum(goods_number) from ' . DataMapper::tableName('order_goods')
                    . ' where goods_id = ? )'
                    . ' ,user_pay_number = (select sum(goods_number) from ' . DataMapper::tableName('order_goods')
                    . ' where goods_id = ? and order_goods_status > 0)'
                    . ' where goods_id = ? order by goods_id asc limit 1 ';
                $dbEngine = DataMapper::getDbEngine();
                $dbEngine->exec(
                    $sql,
                    array(1 => $goodsItem['goods_id'], $goodsItem['goods_id'], $goodsItem['goods_id'])
                );
            }
            unset($goodsArray);

            printLog('calculate goods buy number offset : ' . $offset);
        }

        printLog('calculate goods buy number finished , offset : ' . $offset);
    }

    public function help()
    {
        echo "Calculate buy_number, pay_number for all goods";
    }
}
