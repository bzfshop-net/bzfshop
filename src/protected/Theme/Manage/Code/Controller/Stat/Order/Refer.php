<?php

/**
 * @author QiangYu
 *
 * 订单来源的统计
 *
 * */

namespace Controller\Stat\Order;


use Core\Helper\Utility\Money;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Search\SearchHelper;
use Core\Service\Order\Goods as OrderGoodsService;
use Core\Service\Order\Order as OrderBasicService;

class Refer extends \Controller\AuthController
{
    // 过滤数据，Excel 只显示这里定义的列
    private $filterArray = array(

        // 子订单显示的数据
        'utm_source'              => '来源渠道(主)',
        'utm_medium'              => '来源渠道(次)',
        'login_type'              => '登陆方式',
        'order_id'                => '订单ID号',
        'rec_id'                  => '子订单ID号',
        'order_goods_status_desc' => '订单状态',
        'add_time'                => '下单时间',
        'pay_time'                => '支付时间',
        'goods_id'                => '商品ID号',
        'goods_number'            => '购买数量',
        'goods_price'             => '商品金额',
        'shipping_fee'            => '快递费',
        'discount'                => '商品优惠',
        'extra_discount'          => '额外优惠',
        'refund'                  => '退款',
        'refund_note'             => '退款说明',
        'extra_refund'            => '额外退款',
        'extra_refund_note'       => '额外退款说明',
        'cps_rate'                => 'CPS费率',
        // 订单的数据
        'surplus'                 => '余额支付',
        'bonus'                   => '红包支付',
        'cps_amount'              => 'CPS结算金额',
        'cps_fee'                 => 'CPS佣金',
        'cps_extra'               => 'CPS额外',
    );

    /**
     * 在这里做数据库查询
     *
     * @param $f3
     *
     * @return array
     */
    private function doQuery($f3)
    {
        // 返回结果
        $orderGoodsArray = array();

        // 参数验证
        $validator = new Validator($f3->get('GET'));

        //订单表单查询
        $searchFormQuery = array();

        //下单时间
        $addTimeStartStr                = $validator->validate('add_time_start');
        $addTimeStart                   = Time::gmStrToTime($addTimeStartStr) ? : null;
        $addTimeEndStr                  = $validator->validate('add_time_end');
        $addTimeEnd                     = Time::gmStrToTime($addTimeEndStr) ? : null;
        $searchFormQuery['oi.add_time'] = array($addTimeStart, $addTimeEnd);

        //付款时间
        $payTimeStartStr                = $validator->validate('pay_time_start');
        $payTimeStart                   = Time::gmStrToTime($payTimeStartStr) ? : null;
        $payTimeEndStr                  = $validator->validate('pay_time_end');
        $payTimeEnd                     = Time::gmStrToTime($payTimeEndStr) ? : null;
        $searchFormQuery['oi.pay_time'] = array($payTimeStart, $payTimeEnd);

        if (!($addTimeStart || $addTimeEnd || $payTimeStart || $payTimeEnd)) {
            $this->addFlashMessage('必须最少选择一个时间');
            goto out;
        }

        $searchFormQuery['orf.login_type'] = array('=', $validator->validate('login_type'));
        $searchFormQuery['oi.system_id']   = array('=', $validator->validate('system_id'));

        // 构造查询条件
        $searchParamArray = array();

        $utmSource = $validator->validate('utm_source');
        if ('SELF' == $utmSource) {
            $searchParamArray[] = array('orf.utm_source is null');
        } else {
            $searchFormQuery['orf.utm_source'] = array('=', $utmSource);
        }

        $utmMedium = $validator->validate('utm_medium');
        if ('SELF' == $utmMedium) {
            $searchParamArray[] = array('orf.utm_medium is null');
        } else {
            $searchFormQuery['orf.utm_medium'] = array('=', $utmMedium);
        }

        if (!$this->validate($validator)) {
            goto out;
        }

        // 表单查询
        $searchParamArray = array_merge($searchParamArray, QueryBuilder::buildSearchParamArray($searchFormQuery));

        $orderGoodsArray = SearchHelper::search(
            SearchHelper::Module_OrderGoodsOrderInfoOrderRefer,
            'og.rec_id, og.order_id, og.goods_id, og.order_goods_status, og.goods_number, og.goods_price, og.shipping_fee'
            . ', og.shipping_fee, og.suppliers_price, og.suppliers_shipping_fee, og.discount, og.extra_discount, og.refund'
            . ',og.extra_refund, og.refund_note, og.extra_refund_note, og.suppliers_refund, og.cps_rate, og.cps_fix_fee'
            . ',oi.pay_status, oi.surplus, oi.bonus, oi.pay_time, oi.add_time, orf.utm_source, orf.utm_medium, orf.login_type',
            // fields
            $searchParamArray,
            array(array('og.order_id', 'asc'), array('og.rec_id', 'asc')),
            0,
            $f3->get('sysConfig[max_query_record_count]')
        );

        // 每个订单就只有一个 surplus, bonus ，我们清除掉多余的值
        $lastOrderId = 0;
        foreach ($orderGoodsArray as &$orderGoodsItem) {
            if ($lastOrderId != $orderGoodsItem['order_id']) {
                $lastOrderId = $orderGoodsItem['order_id']; // 记住操作的订单 id
                continue;
            }

            // 同一个订单的 order_goods ，清除多余的 surplus, bonus
            $orderGoodsItem['surplus'] = 0;
            $orderGoodsItem['bonus']   = 0;
        }
        unset($orderGoodsItem);

        out: // 这里返回结果
        return $orderGoodsArray;
    }

    /**
     * 对两个数组的结果进行数值累加
     *
     * @param $arrayLeft
     * @param $arrayRight
     */
    private function arrayAdd(&$arrayLeft, $arrayRight)
    {
        foreach ($arrayLeft as $key => $value) {
            if (!is_numeric($value)) {
                continue;
            }

            // 我们只对数值进行累加
            if (array_key_exists($key, $arrayRight)) {
                $arrayLeft[$key] += $arrayRight[$key];
            }
        }
    }

    /**
     * 对一个订单进行计算
     *
     * @param $orderId
     * @param $index
     * @param $orderGoodsArray
     *
     * @return array|null
     */
    private function calculateOrder($orderId, &$index, $orderGoodsArray)
    {
        $orderGoodsArrayCount = count($orderGoodsArray);

        if ($index >= $orderGoodsArrayCount) {
            return null;
        }

        $orderGoodsItem = $orderGoodsArray[$index];

        // 单个订单的计算
        $statResult                           = array();
        $statResult['surplus']                = 0; // 余额支付金额
        $statResult['bonus']                  = 0; // 红包支付金额
        $statResult['goods_price']            = 0; // 支付的商品金额
        $statResult['shipping_fee']           = 0; // 快递金额
        $statResult['discount']               = 0; // 折扣金额
        $statResult['extra_discount']         = 0; // 额外折扣
        $statResult['refund']                 = 0; // 退款金额
        $statResult['extra_refund']           = 0; // 额外退款金额
        $statResult['suppliers_price']        = 0; // 供货金额
        $statResult['suppliers_shipping_fee'] = 0; // 供货快递费
        $statResult['suppliers_refund']       = 0; // 供货商给我们的退款

        $statResult['utm_source'] = $orderGoodsItem['utm_source'] ? : 'SELF'; // CPS 来源
        $statResult['utm_medium'] = $orderGoodsItem['utm_medium'] ? : 'SELF'; // CPS 来源
        $statResult['cps_amount'] = 0; // CPS 计算的本金
        $statResult['cps_fee']    = 0; // CPS 应支付金额

        $statResult['order_goods_count']        = 0; // order_goods 数量
        $statResult['order_goods_pay_count']    = 0; // order_goods 付款数量
        $statResult['order_goods_refund_count'] = 0; // order_goods 退款数量

        // 付款的订单才需要记录这些信息
        if (OrderBasicService::PS_PAYED == $orderGoodsItem['pay_status']) {
            $statResult['surplus'] = $orderGoodsItem['surplus']; // 余额支付金额
            $statResult['bonus']   = $orderGoodsItem['bonus']; // 红包支付金额
        }

        // CPS 计算需要
        $couponLeft = $orderGoodsItem['surplus'] + $orderGoodsItem['bonus'];

        // 循环处理 order 中每一个 order_goods
        for (; $index < $orderGoodsArrayCount; $index++) {
            $orderGoodsItem = $orderGoodsArray[$index];
            if ($orderId != $orderGoodsItem['order_id']) {
                // 已经不是同一个订单了，退出计算
                break;
            }

            $statResult['order_goods_count']++;

            if (OrderBasicService::PS_PAYED != $orderGoodsItem['pay_status']) {
                continue; // 没付款的订单没什么好统计的
            }

            // TODO: 临时加的列，用于亿起发结算
            $statResult['cps_extra'] = 30; // 亿起发QQ彩贝登陆需要额外支付 3 分钱，注意钱一律使用 千分位

            // 付款了订单累加金额
            unset($orderGoodsItem['surplus']);
            unset($orderGoodsItem['bonus']);
            unset($orderGoodsItem['utm_source']);
            unset($orderGoodsItem['utm_medium']);

            $orderGoodsItem['suppliers_price'] = $orderGoodsItem['suppliers_price'] * $orderGoodsItem['goods_number'];

            // 值累加
            $this->arrayAdd($statResult, $orderGoodsItem);

            if (OrderGoodsService::OGS_PAY == $orderGoodsItem['order_goods_status']) {
                $statResult['order_goods_pay_count']++;
            } elseif (OrderGoodsService::OGS_PAY < $orderGoodsItem['order_goods_status']) {
                $statResult['order_goods_refund_count']++;
            } else {
                // do nothing
            }

            // CPS 计算
            $itemAmount =
                $orderGoodsItem['goods_price'] - $orderGoodsItem['discount'] - $orderGoodsItem['extra_discount']
                - $orderGoodsItem['refund'] - $orderGoodsItem['extra_refund']; //商品金额
            $itemAmount = ($itemAmount > 0) ? $itemAmount : 0;
            $itemCoupon = min($couponLeft, $itemAmount); //商品消耗掉的优惠券
            $couponLeft -= $itemCoupon;
            $couponLeft = ($couponLeft > 0) ? $couponLeft : 0;
            //商品佣金
            $cpsRate = floatval($orderGoodsItem['cps_rate']);
            $statResult['cps_amount'] += ($itemAmount - $itemCoupon); // CPS 应计算的本金
            $itemCommission = ($itemAmount - $itemCoupon) * $cpsRate;
            $statResult['cps_fee'] += $itemCommission; // CPS 应付费用
        }

        return $statResult;
    }


    public function get($f3)
    {

        // 权限检查
        $this->requirePrivilege('manage_stat_order_refer_get');

        global $smarty;

        if (!$f3->get('GET')) {
            // 没有任何查询，直接显示空页面
            goto out_display;
        }

        $orderGoodsArray = $this->doQuery($f3);

        // 没有数据，不需要计算
        if (empty($orderGoodsArray)) {
            goto out_display;
        }

        $utmSourceMediumStatArray = array();

        // 按照订单一个一个计算
        $orderGoodsArrayCount = count($orderGoodsArray);

        for ($index = 0; $index < $orderGoodsArrayCount;) {
            $orderGoodsItem  = $orderGoodsArray[$index];
            $orderStatResult = $this->calculateOrder($orderGoodsItem['order_id'], $index, $orderGoodsArray);

            if (null == $orderStatResult) {
                break;
            }

            if (!$orderStatResult['utm_source'] && !$orderStatResult['utm_medium']) {
                $utmSourceMediumStatKey = 'SELF';
            } else {
                $utmSourceMediumStatKey = '' . $orderStatResult['utm_source'] . '-' . $orderStatResult['utm_medium'];
            }

            if (!array_key_exists($utmSourceMediumStatKey, $utmSourceMediumStatArray)) {
                $utmSourceMediumStatArray[$utmSourceMediumStatKey] = $orderStatResult;
            } else {
                $this->arrayAdd($utmSourceMediumStatArray[$utmSourceMediumStatKey], $orderStatResult);
            }
        }

        // 给 smarty 赋值
        $smarty->assign('utmSourceMediumStatArray', $utmSourceMediumStatArray);

        out_display:
        $smarty->display('stat_order_refer.tpl');
    }


    private function outputHeaderRow($activeSheet, $rowIndex)
    {
        // 输出头部信息
        $colIndex = 1;
        foreach ($this->filterArray as $key => $value) {

            $activeSheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $value);
            $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFont()->setBold(true);
            $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFill()->setFillType(
                \PHPExcel_Style_Fill::FILL_SOLID
            );

            $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFill()->getStartColor()->setARGB(
                'FFB0B0B0'
            );

            $colIndex++;
        }
    }

    private function outputDataRow($activeSheet, $rowIndex, $dataRowItem, $excludeKeyArray = null)
    {

        // 代表价格的列，需要特殊处理显示格式
        $priceColumnArray =
            array(
                'goods_price',
                'shipping_fee',
                'discount',
                'extra_discount',
                'refund',
                'extra_refund',
                'surplus',
                'bonus',
                'cps_amount',
                'cps_fee',
                'cps_extra',
            );

        // 输出数据
        $colIndex = 1;
        foreach ($this->filterArray as $key => $value) {

            if (!array_key_exists($key, $dataRowItem) //没有这个 key 就跳过
                || (!empty($excludeKeyArray) && in_array($key, $excludeKeyArray))
            ) {
                $colIndex++;
                continue;
            }

            $cellValue = isset($dataRowItem[$key]) ? $dataRowItem[$key] : '';
            if (in_array($key, $priceColumnArray)) {
                // 金额需要做转化
                $cellValue = Money::toSmartyDisplay($cellValue);
                $activeSheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $cellValue);
            } else {
                $activeSheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $cellValue);
                $activeSheet->getCellByColumnAndRow($colIndex, $rowIndex)->setDataType(
                    \PHPExcel_Cell_DataType::TYPE_STRING
                );
            }

            // 未付款订单，用淡蓝色标注
            if (array_key_exists('order_goods_status', $dataRowItem) && 0 == $dataRowItem['order_goods_status']) {
                $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFill()->setFillType(
                    \PHPExcel_Style_Fill::FILL_SOLID
                );
                $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFill()->getStartColor()->setARGB(
                    'FF49AFCD'
                );
            }

            // 退款订单，用红色标注 （退款订单）
            if (array_key_exists('order_goods_status', $dataRowItem) && $dataRowItem['order_goods_status'] > 1) {
                $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFill()->setFillType(
                    \PHPExcel_Style_Fill::FILL_SOLID
                );
                $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFill()->getStartColor()->setARGB(
                    'FFFF0000'
                );
            }

            $colIndex++;
        }

    }

    /**
     * 订单下载
     *
     * @param $f3
     */
    public function Download($f3)
    {

        // 权限检查
        $this->requirePrivilege('manage_stat_order_refer_download');

        $errorMessage = '';

        if (!$f3->get('GET')) {
            // 没有任何查询，直接显示空页面
            $errorMessage = '查询参数不能为空';
            goto out_fail;
        }

        // 做数据查询
        $orderGoodsArray = $this->doQuery($f3);

        // 没有数据，不需要计算
        if (empty($orderGoodsArray)) {
            $errorMessage = '没有订单可以下载';
            goto out_fail;
        }

        // 转换显示格式
        foreach ($orderGoodsArray as &$orderGoodsItem) {
            $orderGoodsItem['add_time']                =
                Time::gmTimeToLocalTimeStr($orderGoodsItem['add_time'], 'Y-m-d H:i:s');
            $orderGoodsItem['pay_time']                =
                Time::gmTimeToLocalTimeStr($orderGoodsItem['pay_time'], 'Y-m-d H:i:s');
            $orderGoodsItem['order_goods_status_desc'] =
                OrderGoodsService::$orderGoodsStatusDesc[$orderGoodsItem['order_goods_status']];
        }
        unset($orderGoodsItem);

        // 生成 Excel 并且提供下载
        require_once(PROTECTED_PATH . '/Vendor/PHPExcel/Settings.php');
        // 设置Excel缓存，防止数据太多拖死了程序
        \PHPExcel_Settings::setCacheStorageMethod(\PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp);

        // 导出为 Excel 格式
        $objPHPExcel = new \PHPExcel();

        // 设置工作 sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

        // 输出数据
        $rowIndex            = 1;
        $lastOrderId         = 0;
        $orderGoodsArraySize = count($orderGoodsArray);

        // 输出表头
        $this->outputHeaderRow($activeSheet, $rowIndex);
        $rowIndex++;

        // 用于订单计算的 order_goods
        $calOrderGoodsArray = array();

        for ($orderGoodsIndex = 0; $orderGoodsIndex < $orderGoodsArraySize; $orderGoodsIndex++) {

            // 取得这行数据
            $orderGoodsItem = $orderGoodsArray[$orderGoodsIndex];

            if ($lastOrderId != $orderGoodsItem['order_id']) {

                // 计算整个订单的 CPS 费用
                if (!empty($calOrderGoodsArray)) {
                    $tmpIndex                      = 0;
                    $orderStatResult               =
                        $this->calculateOrder($lastOrderId, $tmpIndex, $calOrderGoodsArray);
                    $orderStatResult['utm_source'] = '订单CPS总计-->';
                    unset($orderStatResult['utm_medium']);
                    // 输出订单的计算结果
                    $this->outputDataRow($activeSheet, $rowIndex, $orderStatResult);
                    $rowIndex += 2; // 留出一个空行

                    // 释放内存
                    unset($calOrderGoodsArray);
                    unset($orderStatResult);
                    $calOrderGoodsArray = array();
                }

                // 更新订单 id
                $lastOrderId = $orderGoodsItem['order_id'];
            }

            // 加入需要计算的 order_goods 记录
            $calOrderGoodsArray[] = $orderGoodsItem;
            // 输出 order_goods 行
            $this->outputDataRow($activeSheet, $rowIndex, $orderGoodsItem);

            // 数据换行
            $rowIndex++;
        }

        // 最后一次计算整个订单的 CPS 费用
        if (!empty($calOrderGoodsArray)) {
            $tmpIndex                      = 0;
            $orderStatResult               = $this->calculateOrder($lastOrderId, $tmpIndex, $calOrderGoodsArray);
            $orderStatResult['utm_source'] = '订单CPS总计-->';
            unset($orderStatResult['utm_medium']);
            // 输出订单的计算结果
            $this->outputDataRow($activeSheet, $rowIndex, $orderStatResult);
            $rowIndex += 2; // 留出一个空行

            // 释放内存
            unset($calOrderGoodsArray);
            unset($orderStatResult);
        }

        // 输出为 Excel5 格式
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        $fileName  = '来源渠道订单下载';
        header('Content-Type: application/vnd.ms-excel');
        if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
            header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '.xls"');
        } else {
            header('Content-Disposition: attachment; filename="' . $fileName . '.xls"');
        }
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output'); //输出到浏览器

        die();

        out_fail:
        echo $errorMessage;
    }

}
