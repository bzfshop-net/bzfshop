<?php

use Core\Helper\Utility\Route;
use Core\Modal\SqlMapper as DataMapper;
use Core\Service\Goods\Goods as GoodsBasicService;

class Test implements \Clip\Command
{

    public function run(array $params)
    {

        global $f3;

        $goodsBasicService = new GoodsBasicService();
        $goods             = $goodsBasicService->loadGoodsById('20');

        $sql      = 'update ' . DataMapper::tableName('goods') . ' set goods_after_service = ? ';
        $dbEngine = DataMapper::getDbEngine();
        $dbEngine->exec(
            $sql,
            array(1 => $goods['goods_after_service'])
        );
    }

    public function help()
    {
        echo "Test Program";
    }
}
