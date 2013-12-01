<?php
use Core\Search\SearchHelper;
use Core\Service\Goods\Goods;

/**
 *
 * 用户检查商品描述中的外链图片，结果保存在 TEMP/CheckGoodsInnerImageUrl.log 里面
 *
 */
class CheckGoodsInnerImageUrl implements \Clip\Command
{

    public function run(array $params)
    {
        global $f3;

        $outputFile = $f3->get('TEMP') . 'CheckGoodsInnerImageUrl.log';

        $imageHostAllow = array('img.bangzhufu.com');

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

                    $patternMatch = array();
                    preg_match_all(
                        '/<img(.*?)src="(.*?)"(.*?)\/?>/',
                        $goodsObj->goods_desc,
                        $patternMatch,
                        PREG_SET_ORDER
                    );

                    $isFirst = true;

                    // 处理每一个图片
                    foreach ($patternMatch as $matchItem) {
                        $imageUrl = $matchItem[2];

                        $urlInfo = parse_url($imageUrl);

                        if (!in_array(@$urlInfo['host'], $imageHostAllow)) {

                            if ($isFirst) {
                                $isFirst = false;
                                $f3->write($outputFile, "\n\ngoods_id:" . $goodsObj->goods_id . "\n", true);
                            }

                            $f3->write($outputFile, "\t" . $matchItem[2] . "\n", true);
                            printLog('illegal image url [' . $matchItem[2] . ']');
                        }
                    }

                    unset($patternMatch);
                }
                unset($goodsObj);
                printLog('end process goods [' . $goods_id . ']');
            }
        }

    }

    public function help()
    {
        echo "Check all Goods Inner Image Link";
    }
}
