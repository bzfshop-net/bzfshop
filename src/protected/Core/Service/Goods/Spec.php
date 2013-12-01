<?php

/**
 *
 * @author QiangYu
 *
 * 商品的规格属性
 *
 * */

namespace Core\Service\Goods;

use Core\Helper\Utility\Validator;

class Spec extends \Core\Service\BaseService
{

    // 商品选择的标题，比如 颜色、尺码
    private $goodsSpecNameArray = array();
    // 商品第一级选项目
    private $goodsSpecValue1Array = array();
    // 商品第二级选项
    private $goodsSpecValue2Array = array();
    // 商品第三级选项
    private $goodsSpecValue3Array = array();
    // 商品规格对应的库存，-1 表示无限库存，0 表示已经没有库存了
    private $goodsNumberArray = array();
    // 商品规格可以加价，这里是加价列表
    private $goodsSpecAddPriceArray = array();
    // 商品规格可以对应不同的货号，这里记录对应的货号
    private $goodsSnArray = array();
    // 每个规格可以对应一张头图，用户选择的时候可以自动切换头图，0 表示不对应任何头图
    private $imgIdArray = array();

    // 为了数据查找，用一个新的结构
    private $goodsSpecSearchArray = array();

    /**
     * @param string $specStr 商品的规格组合，用逗号分隔，比如 "红色,XL,男款"
     *         如果 $specStr == null 则返回所有规格数据
     *
     * @return array
     */
    public function getGoodsSpecDataArray($specStr = null)
    {
        // 构造查询结构
        if (empty($this->goodsSpecSearchArray)) {
            $goodsSpecValueCount = count($this->goodsSpecValue1Array);
            for ($index = 0; $index < $goodsSpecValueCount; $index++) {
                $key                              = implode(
                    ',',
                    array(
                         @$this->goodsSpecValue1Array[$index],
                         @$this->goodsSpecValue2Array[$index],
                         @$this->goodsSpecValue3Array[$index]
                    )
                );
                $key                              = rtrim($key, ',');
                $this->goodsSpecSearchArray[$key] = array(
                    'index'                => $index,
                    'goods_number'         => @$this->goodsNumberArray[$index],
                    'goods_spec_add_price' => @$this->goodsSpecAddPriceArray[$index],
                    'goods_sn'             => @$this->goodsSnArray[$index],
                    'img_id'               => @$this->imgIdArray[$index],
                );
            }
        }

        if (null == $specStr) {
            return $this->goodsSpecSearchArray;
        }

        if (array_key_exists($specStr, $this->goodsSpecSearchArray)) {
            return $this->goodsSpecSearchArray[$specStr];
        }

        return null;
    }

    /**
     * 设置商品规格对应的库存
     *
     * @param string $specStr
     * @param int    $goodsNumber
     */
    public function setGoodsSpecGoodsNumber($specStr, $goodsNumber)
    {
        // 参数验证
        $validator   = new Validator(array('specStr' => $specStr, 'goodsNumber' => $goodsNumber));
        $specStr     = $validator->required()->validate('specStr');
        $goodsNumber = $validator->digits()->min(0)->validate('goodsNumber');
        $this->validate($validator);

        $goodsSpecDataArray = $this->getGoodsSpecDataArray($specStr);
        if (empty($goodsSpecDataArray)) {
            return;
        }

        $this->goodsNumberArray[$goodsSpecDataArray['index']] = $goodsNumber;
    }

    /**
     * 初始化数据
     *
     * @param array $goodsSpecNameArray
     * @param array $goodsSpecValue1Array
     * @param array $goodsSpecValue2Array
     * @param array $goodsSpecValue3Array
     * @param array $goodsNumberArray
     * @param array $goodsSpecAddPriceArray
     * @param array $goodsSnArray
     * @param array $imgIdArray
     */
    public function initWithData(
        $goodsSpecNameArray,
        $goodsSpecValue1Array,
        $goodsSpecValue2Array,
        $goodsSpecValue3Array,
        $goodsNumberArray,
        $goodsSpecAddPriceArray,
        $goodsSnArray,
        $imgIdArray
    ) {
        $this->goodsSpecNameArray     = $goodsSpecNameArray ? : $this->goodsSpecNameArray;
        $this->goodsSpecValue1Array   = $goodsSpecValue1Array ? : $this->goodsSpecValue1Array;
        $this->goodsSpecValue2Array   = $goodsSpecValue2Array ? : $this->goodsSpecValue2Array;
        $this->goodsSpecValue3Array   = $goodsSpecValue3Array ? : $this->goodsSpecValue3Array;
        $this->goodsNumberArray       = $goodsNumberArray ? : $this->goodsNumberArray;
        $this->goodsSpecAddPriceArray = $goodsSpecAddPriceArray ? : $this->goodsSpecAddPriceArray;
        $this->goodsSnArray           = $goodsSnArray ? : $this->goodsSnArray;
        $this->imgIdArray             = $imgIdArray ? : $this->imgIdArray;
    }

    /**
     * 从 json 串中初始化数据
     *
     * @param string $jsonStr
     */
    public function initWithJson($jsonStr)
    {

        $jsonArray = json_decode($jsonStr, true);
        if (empty($jsonArray)) {
            return;
        }

        $this->goodsSpecNameArray     = @$jsonArray['goodsSpecNameArray'] ? : $this->goodsSpecNameArray;
        $this->goodsSpecValue1Array   = @$jsonArray['goodsSpecValue1Array'] ? : $this->goodsSpecValue1Array;
        $this->goodsSpecValue2Array   = @$jsonArray['goodsSpecValue2Array'] ? : $this->goodsSpecValue2Array;
        $this->goodsSpecValue3Array   = @$jsonArray['goodsSpecValue3Array'] ? : $this->goodsSpecValue3Array;
        $this->goodsNumberArray       = @$jsonArray['goodsNumberArray'] ? : $this->goodsNumberArray;
        $this->goodsSpecAddPriceArray = @$jsonArray['goodsSpecAddPriceArray'] ? : $this->goodsSpecAddPriceArray;
        $this->goodsSnArray           = @$jsonArray['goodsSnArray'] ? : $this->goodsSnArray;
        $this->imgIdArray             = @$jsonArray['imgIdArray'] ? : $this->imgIdArray;
    }

    /**
     * 取得数组数据
     *
     * @return array
     */
    public function getData()
    {
        return array(
            'goodsSpecNameArray'     => $this->goodsSpecNameArray,
            'goodsSpecValue1Array'   => $this->goodsSpecValue1Array,
            'goodsSpecValue2Array'   => $this->goodsSpecValue2Array,
            'goodsSpecValue3Array'   => $this->goodsSpecValue3Array,
            'goodsNumberArray'       => $this->goodsNumberArray,
            'goodsSpecAddPriceArray' => $this->goodsSpecAddPriceArray,
            'goodsSnArray'           => $this->goodsSnArray,
            'imgIdArray'             => $this->imgIdArray,
        );
    }

    /**
     * 清除数组中某些 index 的值
     *
     * @param array $array
     * @param array $removeIndexArray
     *
     * @return array
     */
    private function clearArray(array $array, array $removeIndexArray)
    {
        $newArray = array();
        foreach ($array as $index => $value) {
            if (!in_array($index, $removeIndexArray)) {
                $newArray[] = $value;
            }
        }
        return $newArray;
    }

    /**
     * 返回能够购买的商品规格（有库存），不能购买的就不返回了
     *
     * @return array
     */
    public function getBuyableData()
    {
        $removeIndexArray = array();
        // 找出 库存为 0 的商品规格
        foreach ($this->goodsNumberArray as $index => $goodsNumber) {
            if ($goodsNumber <= 0) {
                $removeIndexArray[] = $index;
            }
        }

        return array(
            'goodsSpecNameArray'     => $this->goodsSpecNameArray,
            'goodsSpecValue1Array'   => $this->clearArray($this->goodsSpecValue1Array, $removeIndexArray),
            'goodsSpecValue2Array'   => $this->clearArray($this->goodsSpecValue2Array, $removeIndexArray),
            'goodsSpecValue3Array'   => $this->clearArray($this->goodsSpecValue3Array, $removeIndexArray),
            'goodsNumberArray'       => $this->clearArray($this->goodsNumberArray, $removeIndexArray),
            'goodsSpecAddPriceArray' => $this->clearArray($this->goodsSpecAddPriceArray, $removeIndexArray),
            'goodsSnArray'           => $this->clearArray($this->goodsSnArray, $removeIndexArray),
            'imgIdArray'             => $this->clearArray($this->imgIdArray, $removeIndexArray),
        );
    }

    /**
     * 把数据打包成 json 串
     *
     * @return string
     */
    public function getJsonStr()
    {
        if (empty($this->goodsSpecValue1Array)) {
            return '';
        }

        return json_encode(
            $this->getData()
        );
    }

    /**
     * 保存 goods_spec 信息
     *
     * @param int $goods_id
     *
     * @return bool
     */
    public function saveGoodsSpec($goods_id)
    {
        // 参数验证
        $validator = new Validator(array('goods_id' => $goods_id));
        $goods_id  = $validator->required()->digits()->min(1)->validate('goods_id');
        $this->validate($validator);

        $goodsService = new Goods();
        $goods        = $goodsService->loadGoodsById($goods_id);
        if ($goods->isEmpty()) {
            return false;
        }

        // 保存 goods_spec
        $goods->goods_spec = $this->getJsonStr();
        $goods->save();
        return true;
    }

    /**
     * 从商品中加载 goods_spec 信息
     *
     * @param int $goods_id
     *
     * @return bool
     */
    public function loadGoodsSpec($goods_id, $ttl = 0)
    {
        // 参数验证
        $validator = new Validator(array('goods_id' => $goods_id));
        $goods_id  = $validator->required()->digits()->min(1)->validate('goods_id');
        $this->validate($validator);

        $goodsService = new Goods();
        $goods        = $goodsService->loadGoodsById($goods_id, $ttl);
        if ($goods->isEmpty()) {
            return false;
        }

        $this->initWithJson($goods->goods_spec);
        return true;
    }

    /**
     * 清除 商品规格 关联 的图片选择
     */
    public function clearGoodsSpecImgIdArray()
    {
        $this->imgIdArray = array();
        $specValueCount   = count($this->goodsSpecValue1Array);
        for ($index = 0; $index < $specValueCount; $index++) {
            $this->imgIdArray[] = '0';
        }
    }
}

