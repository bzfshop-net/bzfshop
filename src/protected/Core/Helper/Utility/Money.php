<?php

/**
 * @author QiangYu
 *
 * 用于操作 商品金额，我们系统里面所有的商品金额都使用 “整数千分位” ，23.15 表示为 23150
 *
 * 为什么要用 千分位 ？
 *
 * 1. 小数的计算有精度的问题，海量数据累加的误差足以导致错误
 * 2. 小数不能做等于比较  1.2 == 1.2 经常返回 false
 * 3. 整数操作效率高
 * 4. 千分位 多一位，用于提高计算精度
 *
 * */

namespace Core\Helper\Utility;

final class Money
{

    /**
     * 把价格装换成存储的 千分位 整数
     *
     * @param string $price         用于转化的价格
     * @param bool   $keepLastDigit 是否保留最后一位的数字
     *
     * @return int
     */
    public static function toStorage($price, $keepLastDigit = false)
    {
        if (empty($price)) {
            return 0;
        }

        if (!$keepLastDigit) {
            return 10 * intval(bcmul($price, '100', 0));
        }

        return intval(bcmul($price, '1000', 0));
    }

    /**
     * @param int $price  千分位 整数价格
     * @param int $scale  保留几位小数，缺省保留 3 位小数
     *
     * @return string 浮点数价格，缺省带 3 位小数（方便转化回 千分位）
     */
    public static function toDisplay($price, $scale = 3)
    {
        $price  = strval(intval($price));
        $result = bcdiv($price, '1000', 3);
        if ($scale >= 3) {
            return $result;
        }
        return number_format(round($result, $scale), $scale, '.', '');
    }

    /**
     * 转化成智能显示格式，比如  18.10 转化为 18.1
     *
     * @param int $price  千分位 整数价格
     *
     * @return int|string
     */
    public static function toSmartyDisplay($price)
    {
        $floatNumber = self::toDisplay($price, 2);

        $absIntNumber   = abs(intval($floatNumber));
        $absFloatNumber = abs($floatNumber);

        // 有小数
        if ($absIntNumber < $absFloatNumber) {
            return rtrim(strval($floatNumber), '0'); // 去掉右侧多余的0
        }

        return strval(intval($floatNumber));
    }

    /**
     * 把存储的千分位显示的金额转换成 分 显示， 比如  1230 = 123 分
     *
     * @param int $storagePrice
     *
     * @return int
     */
    public static function storageToCent($storagePrice)
    {
        return intval(round(intval($storagePrice) / 10));
    }

    /**
     * 把用于显示的金额转换为 分 ， 比如   12.23 元 = 1223 分
     *
     * @param string $displayPrice
     *
     * @return int
     */
    public static function displayToCent($displayPrice)
    {
        return self::storageToCent(self::toStorage($displayPrice, true));
    }
}