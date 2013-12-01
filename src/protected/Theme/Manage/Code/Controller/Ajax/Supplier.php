<?php

/**
 * @author QiangYu
 *
 * 404 错误
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Service\BaseService;
use Core\Service\Order\Goods as OrderGoodsService;
use Core\Service\User\Supplier as UserSupplierService;

class Supplier extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    public function ListSupplierIdName($f3)
    {

        // 检查缓存
        $cacheKey      = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);
        $supplierArray = $f3->get($cacheKey);
        if (!empty($supplierArray)) {
            goto out;
        }

        $baseService   = new BaseService();
        $supplierArray =
            $baseService->_fetchArray(
                'suppliers',
                'suppliers_id,suppliers_account,suppliers_name',
                null,
                array('order' => 'suppliers_id desc'),
                0,
                0,
                0
            );

        $f3->set($cacheKey, $supplierArray, 300); //缓存 5 分钟

        out:
        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $supplierArray);
    }

    /***
     * 列出一个时间段里面没有结算的商家列表
     *
     * @param $f3
     */
    public function ListOrderGoodsSupplierIdName($f3)
    {
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        //表单查询
        $formQuery = array();

        // 是否这个列表是用于供货商订单结算
        $supplierForSettle = $validator->validate('supplier_for_settle');

        //付款时间
        $payTimeStartStr       = $validator->required()->validate('pay_time_start');
        $payTimeStart          = Time::gmStrToTime($payTimeStartStr) ? : null;
        $payTimeEndStr         = $validator->required()->validate('pay_time_end');
        $payTimeEnd            = Time::gmStrToTime($payTimeEndStr) ? : null;
        $formQuery['pay_time'] = array($payTimeStart, $payTimeEnd);

        //额外退款时间
        $extraRefundTimeStartStr        = $validator->required()->validate('extra_refund_time_start');
        $extraRefundTimeStart           = Time::gmStrToTime($extraRefundTimeStartStr) ? : null;
        $extraRefundTimeEndStr          = $validator->required()->validate('extra_refund_time_end');
        $extraRefundTimeEnd             = Time::gmStrToTime($extraRefundTimeEndStr) ? : null;
        $formQuery['extra_refund_time'] = array($extraRefundTimeStart, $extraRefundTimeEnd);

        if (
            !($payTimeStart && $payTimeEnd) && !($extraRefundTimeStart && $extraRefundTimeEnd)
        ) {
            goto out_fail;
        }

        // 取得供货商 id
        $condArray   = array();
        $condArray[] = array('oi.order_id = og.order_id');

        // 这个列表是用于供货商订单结算，只取得需要结算的供货商
        if (!empty($supplierForSettle)) {
            //只有付款了订单才显示
            $condArray[] = array('order_goods_status > 0');
            //只有发货了订单才需要结算
            $condArray[] = array('og.shipping_id > 0');
            //商家结算过了就不要显示出来了
            $condArray[] = array('settle_id = 0');
        }

        // 表单查询
        $condArray = array_merge($condArray, QueryBuilder::buildQueryCondArray($formQuery));

        $orderGoodsService = new OrderGoodsService();
        $queryArray        = $orderGoodsService->_fetchArray(
            array('order_info' => 'oi', 'order_goods' => 'og'), //table
            'distinct(og.suppliers_id)',
            // fields
            $condArray,
            array('order' => 'suppliers_id desc'),
            0,
            $f3->get('sysConfig[max_query_record_count]'),
            0
        ); //最多限制 max_query_record_count 条记录

        // 取得供货商 id 列表
        $supplierIdArray = array();
        foreach ($queryArray as $queryItem) {
            $supplierIdArray[] = $queryItem['suppliers_id'];
        }

        if (empty($supplierIdArray)) {
            // 没有数据，退出
            goto out_fail;
        }

        // 取得供货商信息
        $userSupplierService = new UserSupplierService();
        $queryArray          = $userSupplierService->fetchSupplierArrayBySupplierIdArray($supplierIdArray);

        $supplierArray = array();
        foreach ($queryArray as $queryItem) {
            $supplierItem                      = array();
            $supplierItem['suppliers_id']      = $queryItem['suppliers_id'];
            $supplierItem['suppliers_account'] = $queryItem['suppliers_account'];
            $supplierItem['suppliers_name']    = $queryItem['suppliers_name'];
            $supplierArray[]                   = $supplierItem;
        }

        // 正常从这里返回
        Ajax::header();
        echo Ajax::buildResult(null, null, $supplierArray);
        return;

        out_fail:
        Ajax::header();
        echo Ajax::buildResult(null, null, array());
    }

}
