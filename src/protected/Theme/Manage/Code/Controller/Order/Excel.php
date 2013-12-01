<?php

/**
 * @author QiangYu
 *
 * 订单导出为 Excel 文件
 *
 * */

namespace Controller\Order;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Money;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Service\Meta\Express as ExpressService;
use Core\Service\Order\Action as OrderActionService;
use Core\Service\Order\Goods as OrderGoodsService;
use Core\Service\Order\Order as OrderBasicService;

class Excel extends \Controller\AuthController
{
    // 过滤数据，只显示这里定义的列
    private $filterArray = array(
        'order_id'                => '订单ID号',
        'rec_id'                  => '子订单ID号',
        'order_goods_status_desc' => '订单状态',
        'add_time'                => '下单时间',
        'pay_time'                => '支付时间',
        'goods_id'                => '商品ID号',
        'goods_name'              => '商品名称',
        'goods_attr'              => '商品选项',
        'goods_number'            => '购买数量',
        'consignee'               => '收件人',
        'mobile'                  => '手机号',
        'tel'                     => '电话',
        'zipcode'                 => '邮编',
        'address'                 => '收件地址',
        'postscript'              => '顾客留言',
        'memo'                    => '客服备注',
        'suppliers_price'         => '供货单价',
        'suppliers_total_price'   => '供货总价',
        'suppliers_shipping_fee'  => '供货快递',
        'shipping_id'             => '填入快递ID',
        'shipping_no'             => '填入快递单号',
        'shipping_name'           => '快递名称(不用填)',
        'refund'                  => '退款',
        'refund_note'             => '退款说明',
        'extra_refund'            => '额外退款',
        'extra_refund_note'       => '额外退款说明',
        'extra_refund_time'       => '额外退款最后时间',
        // 这后面的是用于手动结算的字段
        'goods_price'             => '商品金额',
        'discount'                => '商品优惠',
        'discount_note'           => '商品优惠说明',
        'extra_discount'          => '额外优惠',
        'extra_discount_note'     => '额外优惠说明',
        'shipping_fee'            => '邮费',
    );


    private function outputExpressArray($activeSheet, $expressArray)
    {
        $rowIndex = 1;
        $activeSheet->setCellValueByColumnAndRow(1, $rowIndex, '快递ID');
        $activeSheet->setCellValueByColumnAndRow(2, $rowIndex, '快递名称');
        $rowIndex++;

        foreach ($expressArray as $expressItem) {
            $activeSheet->setCellValueByColumnAndRow(1, $rowIndex, $expressItem['meta_id']);
            $activeSheet->setCellValueByColumnAndRow(2, $rowIndex, $expressItem['meta_name']);
            $rowIndex++;
        }
    }

    /**
     * 输出商品的 header 信息
     *
     * @param object $activeSheet
     * @param int    $rowIndex
     */
    private function outputHeaderRow($activeSheet, $rowIndex)
    {
        // 输出头部信息
        $colIndex = 4;
        foreach ($this->filterArray as $key => $value) {

            $activeSheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $value);
            $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFont()->setBold(true);
            $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFill()->setFillType(
                \PHPExcel_Style_Fill::FILL_SOLID
            );

            if (in_array($key, array('shipping_id', 'shipping_no'))) {
                $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFill()->getStartColor()->setARGB(
                    'FFFFFF00'
                );
            } else {
                $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFill()->getStartColor()->setARGB(
                    'FFB0B0B0'
                );
            }
            $colIndex++;
        }
    }

    private function outputDataRow($activeSheet, $rowIndex, $orderGoodsItem, $excludeKeyArray = null)
    {
        // 代表价格的列，需要特殊处理显示格式
        $priceColumnArray =
            array(
                'suppliers_price',
                'suppliers_total_price',
                'suppliers_shipping_fee',
                'refund',
                'extra_refund',
                'goods_price',
                'shipping_fee',
                'discount',
                'extra_discount',
            );

        // 输出数据
        $colIndex = 4;
        foreach ($this->filterArray as $key => $value) {

            if (!empty($excludeKeyArray) && in_array($key, $excludeKeyArray)) {
                $colIndex++;
                continue;
            }

            $cellValue = isset($orderGoodsItem[$key]) ? $orderGoodsItem[$key] : '';

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

            // 客服备注用黄色标明
            if ('memo' == $key) {
                $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFill()->setFillType(
                    \PHPExcel_Style_Fill::FILL_SOLID
                );
                $activeSheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getFill()->getStartColor()->setARGB(
                    'FFFFFF00'
                );
            }

            // 不是付款订单，用红色标注 （退款订单）
            if (1 != $orderGoodsItem['order_goods_status']) {
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

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_excel_download');

        global $smarty;
        $smarty->assign('max_query_record_count', $f3->get('sysConfig[max_query_record_count]'));
        $smarty->display('order_excel.tpl');
    }

    /**
     *
     * 批量下载订单
     *
     */
    public function Download($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_excel_download');

        // 参数验证
        $validator = new Validator($f3->get('GET'));

        //表单查询
        $formQuery                 = array();
        $formQuery['suppliers_id'] =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('suppliers_id');
        $formQuery['goods_id']     = $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('goods_id');

        if (empty($formQuery['suppliers_id']) && empty($formQuery['goods_id'])) {
            $this->addFlashMessage('供货商ID 和 商品ID 不能同时为空');
            goto out_fail;
        }

        //付款时间
        $payTimeStartStr       = $validator->validate('pay_time_start');
        $payTimeStart          = Time::gmStrToTime($payTimeStartStr) ? : null;
        $payTimeEndStr         = $validator->validate('pay_time_end');
        $payTimeEnd            = Time::gmStrToTime($payTimeEndStr) ? : null;
        $formQuery['pay_time'] = array($payTimeStart, $payTimeEnd);

        //额外退款，时间
        $extraRefundTimeStartStr        = $validator->validate('extra_refund_time_start');
        $extraRefundTimeStart           = Time::gmStrToTime($extraRefundTimeStartStr) ? : null;
        $extraRefundTimeEndStr          = $validator->validate('extra_refund_time_end');
        $extraRefundTimeEnd             = Time::gmStrToTime($extraRefundTimeEndStr) ? : null;
        $formQuery['extra_refund_time'] = array($extraRefundTimeStart, $extraRefundTimeEnd);

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        if (Utils::isBlank($formQuery['suppliers_id'])
            || ((Utils::isBlank($payTimeStart) || Utils::isBlank($payTimeEnd))
                && (Utils::isBlank($extraRefundTimeStart) || Utils::isBlank($extraRefundTimeEnd)))
        ) {
            $this->addFlashMessage('按照供货商下载订单必须提供时间段参数');
            goto out_fail;
        }

        $condArray   = array();
        $condArray[] = array('oi.order_id = og.order_id');

        $condArray[] = array('order_goods_status > 0');

        // 表单查询
        $condArray = array_merge($condArray, QueryBuilder::buildQueryCondArray($formQuery));

        $orderGoodsService = new OrderGoodsService();
        $orderGoodsArray   = $orderGoodsService->_fetchArray(
            array('order_info' => 'oi', 'order_goods' => 'og'), //table
            'og.*, oi.add_time, oi.pay_time, oi.consignee, oi.mobile, oi.tel, oi.zipcode, oi.postscript, oi.address',
            // fields
            $condArray,
            array('order' => 'oi.order_id asc, og.rec_id asc, og.goods_id asc'),
            0,
            $f3->get('sysConfig[max_query_record_count]'),
            0
        ); //最多限制 max_query_record_count 条记录

        // 转换显示格式
        foreach ($orderGoodsArray as &$orderGoodsItem) {
            $orderGoodsItem['add_time']                =
                Time::gmTimeToLocalTimeStr($orderGoodsItem['add_time'], 'Y-m-d H:i:s');
            $orderGoodsItem['pay_time']                =
                Time::gmTimeToLocalTimeStr($orderGoodsItem['pay_time'], 'Y-m-d H:i:s');
            $orderGoodsItem['extra_refund_time']       =
                Time::gmTimeToLocalTimeStr($orderGoodsItem['extra_refund_time'], 'Y-m-d H:i:s');
            $orderGoodsItem['suppliers_total_price']   =
                $orderGoodsItem['suppliers_price'] * $orderGoodsItem['goods_number'];
            $orderGoodsItem['order_goods_status_desc'] =
                OrderGoodsService::$orderGoodsStatusDesc[$orderGoodsItem['order_goods_status']];
        }
        unset($orderGoodsItem);

        require_once(PROTECTED_PATH . '/Vendor/PHPExcel/Settings.php');
        // 设置Excel缓存，防止数据太多拖死了程序
        \PHPExcel_Settings::setCacheStorageMethod(\PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp);

        // 导出为 Excel 格式
        $objPHPExcel = new \PHPExcel();

        // 设置工作 sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

        // 取得快递公司列表
        $expressService = new ExpressService();
        $expressArray   = $expressService->fetchExpressArray();
        //输出快递公司参考表
        $this->outputExpressArray($activeSheet, $expressArray);

        //释放内存
        unset($expressArray);
        unset($expressService);

        // 格式化数据
        $rowIndex            = 1;
        $lastOrderId         = 0;
        $orderGoodsArraySize = count($orderGoodsArray);

        // 输出头部信息
        $this->outputHeaderRow($activeSheet, $rowIndex);
        $rowIndex++; // 换行

        for ($orderGoodsIndex = 0; $orderGoodsIndex < $orderGoodsArraySize; $orderGoodsIndex++) {

            // 取得这行数据
            $orderGoodsItem = $orderGoodsArray[$orderGoodsIndex];
            if ($lastOrderId != $orderGoodsItem['order_id']) {
                $lastOrderId = $orderGoodsItem['order_id'];
                // 不同的订单，需要特殊处理
                $rowIndex += 2; // 跳过 2 行
            }

            // 输出数据
            //$this->outputDataRow($activeSheet, $rowIndex, $orderGoodsItem, array('goods_name', 'goods_id'));
            $this->outputDataRow($activeSheet, $rowIndex, $orderGoodsItem);
            $rowIndex++; // 换行
        }

        $fileName = '订单下载_' . $formQuery['goods_id'] . '_' .
            Time::gmTimeToLocalTimeStr($payTimeStart, 'Y-m-d_H-i-s') . '__' . Time::gmTimeToLocalTimeStr(
                $payTimeEnd,
                'Y-m-d_H-i-s'
            );

        // 输出为 Excel5 格式
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        header('Content-Type: application/vnd.ms-excel');
        if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
            header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '.xls"');
        } else {
            header('Content-Disposition: attachment; filename="' . $fileName . '.xls"');
        }
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output'); //输出到浏览器  

        die();

        out_fail: // 失败，打印错误消息
        $flashMessageArray = $this->flashMessageArray;
        foreach ($flashMessageArray as $flashMessage) {
            echo $flashMessage . '<br />';
        }
    }

    /**
     * 批量上传快递单号
     *
     * @param $f3
     */
    public function Upload($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_order_excel_upload');

        $recIdColumnIndex      = 5; // 子订单 ID 列号
        $shippingIdColumnIndex = 23; // 快递公司 ID 列号
        $shippingNoColumnIndex = 24; // 快递单号列

        if (empty($_FILES) || !array_key_exists('uploadfile', $_FILES)) {
            $this->addFlashMessage('没有上传文件');
            goto out;
        }

        if ($_FILES['uploadfile']['error'] > 0) {
            $this->addFlashMessage('上传文件错误：' . $_FILES['uploadfile']['error']);
            goto out;
        }

        // 解析上传的文件名
        $pathInfoArray = pathinfo($_FILES['uploadfile']['name']);
        $fileExt       = strtolower($pathInfoArray['extension']);
        if ('xls' != $fileExt) {
            $this->addFlashMessage('文件格式错误，必须是 Excel xls 文件');
            goto out;
        }

        // 把文件移动到临时目录
        $targetFile = $f3->get('TEMP') . time() . $fileExt;
        move_uploaded_file($_FILES['uploadfile']['tmp_name'], $targetFile);

        require_once(PROTECTED_PATH . '/Vendor/PHPExcel/Settings.php');
        // 设置Excel缓存，防止数据太多拖死了程序
        \PHPExcel_Settings::setCacheStorageMethod(\PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp);

        try {
            $objPHPExcel = \PHPExcel_IOFactory::load($targetFile);
        } catch (\Exception $e) {
            $this->addFlashMessage('上传的文件格式错误，请注意不要修改批量下载订单文件的格式');
            goto out;
        }

        // 取得快递公司列表
        $expressService = new ExpressService();
        $expressArray   = $expressService->fetchExpressArray();
        // 构建 shipping_id --> express 的反查表
        $shippingIdExpressArray = array();
        foreach ($expressArray as $expressItem) {
            $shippingIdExpressArray[$expressItem['meta_id']] = $expressItem;
        }
        unset($expressArray);
        unset($expressService); // 释放内存

        $activeSheet = $objPHPExcel->setActiveSheetIndex(0);
        $maxRow      = $activeSheet->getHighestRow();

        $expressSetCount = 0; // 成功设置计数

        $authAdminUser     = AuthHelper::getAuthUser();
        $orderBasicService = new OrderBasicService();

        // 一行一行的读取数据
        for ($currentRow = 1; $currentRow <= $maxRow; $currentRow++) {

            // 取得子订单 ID
            $recIdStr = trim($activeSheet->getCellByColumnAndRow($recIdColumnIndex, $currentRow)->getValue());
            if (!ctype_digit($recIdStr)) {
                // 如果不全是数字，说明这列不对
                continue;
            }

            $orderGoods = $orderBasicService->loadOrderGoodsById(intval($recIdStr));
            if ($orderGoods->isEmpty()
                || OrderGoodsService::OGS_PAY != $orderGoods->order_goods_status
            ) {
                $this->addFlashMessage('子订单[' . $recIdStr . ']非法');
                continue;
            }

            //取得快递公司 ID 设置
            $shippingIdStr = trim($activeSheet->getCellByColumnAndRow($shippingIdColumnIndex, $currentRow)->getValue());
            if (!ctype_digit($shippingIdStr) || intval($shippingIdStr) <= 0) {
                $this->addFlashMessage('子订单[' . $recIdStr . '] 对应的 快递ID 错误');
                continue;
            }

            $shipping_id = intval($shippingIdStr);
            if (!isset($shippingIdExpressArray[$shipping_id])) {
                $this->addFlashMessage('子订单[' . $recIdStr . '] 对应的 快递ID[' . $shipping_id . '] 非法');
                continue;
            }

            if ($orderGoods->shipping_id > 0) {
                $this->addFlashMessage(
                    '子订单[' . $recIdStr . '] 覆盖了之前已有的快递信息 ['
                    . $orderGoods->shipping_name . '：' . $orderGoods->shipping_no . ']'
                );
            }

            //取得快递单号
            $shippingNoStr = trim($activeSheet->getCellByColumnAndRow($shippingNoColumnIndex, $currentRow)->getValue());

            //设置快递信息
            $orderGoods->shipping_id   = $shipping_id;
            $orderGoods->shipping_name = $shippingIdExpressArray[$shipping_id]['meta_name'];
            $orderGoods->shipping_no   = $shippingNoStr;
            $orderGoods->save();

            $expressSetCount++;

            // 更新 order_info 的 update_time 字段
            $orderInfo              = $orderBasicService->loadOrderInfoById($orderGoods['order_id'], 1); //缓存1秒
            $orderInfo->update_time = Time::gmTime();
            $orderInfo->save();

            // 添加订单操作日志
            $action_note =
                '' . $shipping_id . ',' . $shippingIdExpressArray[$shipping_id]['meta_name'] . ',' . $shippingNoStr;

            $orderActionService = new OrderActionService();
            $orderActionService->logOrderAction(
                $orderGoods['order_id'],
                $orderGoods['rec_id'],
                $orderInfo['order_status'],
                $orderInfo['pay_status'],
                $orderGoods['order_goods_status'],
                $action_note,
                '管理员：[' . $authAdminUser['user_id'] . ']' . $authAdminUser['user_name'],
                0,
                $orderInfo['shipping_status']
            );

        }

        $this->addFlashMessage('一共更新了 ' . $expressSetCount . ' 个快递信息');

        out:
        // 删除上传文件
        if (!empty($targetFile)) {
            @unlink($targetFile);
        }
        // 回到批量下载界面
        RouteHelper::reRoute($this, '/Order/Excel');
    }

}
