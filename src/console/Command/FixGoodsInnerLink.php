<?php

use Core\Search\SearchHelper;
use Core\Service\Goods\Goods;

class FixGoodsInnerLink implements \Clip\Command
{

    public function run(array $params)
    {
        $pageSize = 500;

        $goodsBasicService = new Goods();

        $totalGoodsCount = SearchHelper::count(SearchHelper::Module_Goods, array());

        for ($pageNo = 0; $pageNo * $pageSize < $totalGoodsCount; $pageNo++) {
            // 查询商品
            $goodsArray = SearchHelper::search(
                SearchHelper::Module_Goods,
                'g.goods_id',
                array(),
                array(array('g.goods_id', 'asc')),
                $pageNo * $pageSize,
                $pageSize
            );

            foreach ($goodsArray as $goodsItem) {
                $goods_id = $goodsItem['goods_id'];
                printLog('begin process goods [' . $goods_id . ']');
                $goodsObj = $goodsBasicService->loadGoodsById($goods_id);
                if ($goodsObj->isEmpty()) {
                    printLog('goods [' . $goods_id . '] is empty');
                } else {

                    $goodsObj->goods_desc =
                        str_replace('tuan.bangzhufu.com', 'www.bangzhufu.com', $goodsObj->goods_desc);

                    $goodsObj->goods_desc =
                        str_replace('cdn.bzfshop.net', 'img.bangzhufu.com', $goodsObj->goods_desc);

                    $goodsObj->goods_desc =
                        preg_replace(
                            '!/Goods/View/goods_id~([0-9]+).html!',
                            '/Goods/View/goods_id-\1.html',
                            $goodsObj->goods_desc
                        );

                    $goodsObj->update_time = \Core\Helper\Utility\Time::gmTime();

                    $goodsObj->save();
                }
                unset($goodsObj);
                printLog('end process goods [' . $goods_id . ']');
            }
        }

    }

    public function help()
    {
        echo "fix all Goods Inner Link";
    }
}
