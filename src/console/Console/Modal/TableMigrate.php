<?php

/**
 * @author QiangYu
 *
 * 数据迁移时候用于 复制表的数据
 *
 * */

namespace Console\Modal;

use Core\Helper\Utility\QueryBuilder;
use Core\Modal\SqlMapper as DataMapper;

class TableMigrate
{

    private static $loggerSource = 'TableMigrate';

    public $batchProcessCount = 1000; // 每次批处理得数量，防止数量过大搞死系统

    /**
     * 清空一个表
     *
     * @param DataMapper $dataMapper
     */
    public function clearTable(DataMapper $dataMapper)
    {
        $dataMapper->erase('1');
    }

    /**
     *  设置表的 auto_increment 值
     */
    public function setAutoIncValue(DataMapper $dataMapper, $value)
    {
        $sql = 'ALTER TABLE ' . $dataMapper->getTableName() . ' AUTO_INCREMENT = ' . $value;
        $dataMapper->getDb()->exec($sql);
    }

    /**
     * 重设 auto_increment 的值，自动计算最大的值
     *
     * @string string $primaryKey 主键名称
     *
     */
    public function resetAutoIncValue(DataMapper $dataMapper, $primaryKey)
    {
        list($result) = $dataMapper->select('max(' . $primaryKey . ') as mvalue', null, null, 0);
        $autoIncValue = $result->getAdhocValue('mvalue') + 1;
        $this->setAutoIncValue($dataMapper, $autoIncValue);
    }


    /**
     * 把一个表的数据转换到另外一个表中
     *
     * @param string   $srcTableName                     源表名
     * @param array    $condArray
     *                                                   array(
     *                                                   array('supplier_id = ?', $supplier_id)
     *                                                   array('is_on_sale = ?', 1)
     *                                                   array('supplier_price > ? or supplier_price < ?', $priceMin, $priceMax)
     * )
     * @param array    $optionArray                      排序分页等条件 ，格式例如  array('order' => 'goods_id asc', 'offset' => 100, 'limit' => '10')
     * @param string   $dstTableName                     目的表名
     * @param array    $columnMap                        列对应，格式如 array('id' => 'goods_id' , 'name' => 'goods_name')
     * @param array    $srcValueConvertFuncArray
     *              对源数据做装换，例如： array('id' => function($srcValue, $srcRecord){ return $srcValue + 1;} , ...)，用闭包函数做转化
     * @param callable $recordPreFunc                    在查询到 src 的记录之后调用 ，函数原型 function(&$srcRecord){ ... }
     * @param callable $recordPostFunc                   在 src 已经往 dst 赋值完成之后，dst 还没有写入数据库之前调用,函数原型  function(&$srcRecord, &dstRecord){...}
     *
     */
    public function convertTable(
        $srcTableName,
        $condArray,
        $optionArray,
        $dstTableName,
        $columnMap,
        $srcValueConvertFuncArray,
        $recordPreFunc = null,
        $recordPostFunc = null
    ) {

        $srcFieldArray = array();
        $dstFieldArray = array();

        foreach ($columnMap as $srcField => $dstField) {
            $srcFieldArray[] = $srcField;
            $dstFieldArray[] = $dstField;
        }

        $srcTable = new SrcDataMapper($srcTableName);

        // 构造查询条件
        $filter = null;
        if (!empty($condArray)) {
            $filter = QueryBuilder::buildAndFilter($condArray);
        }

        // 获得总数
        $totalRecordCount = $srcTable->count($filter);
        printLog(
            'begin convertTable ' . $srcTableName . '-->' . $dstTableName . ' totalRecordCount:' . $totalRecordCount,
            self::$loggerSource
        );

        $recordLeft  = $totalRecordCount; // 剩余多少记录需要处理
        $queryOffset = 0; // 查询的起始位置

        while ($recordLeft > 0) {

            // 这次需要处理多少记录
            $processCount = ($recordLeft > $this->batchProcessCount) ? $this->batchProcessCount : $recordLeft;

            // 记录处理进度
            printLog(
                'totalRecordCount:' . $totalRecordCount . ' recordLeft:' . $recordLeft . ' processCount:'
                    . $processCount,
                self::$loggerSource
            );

            $recordLeft -= $processCount;

            // 从源表查询数据
            $srcField = '*';
            if (!empty($srcFieldArray)) {
                $srcField = implode(',', $srcFieldArray);
            }

            // 处理查询的起始位置
            $optionArray = array_merge($optionArray, array('offset' => $queryOffset, 'limit' => $processCount));
            $queryOffset += $processCount;

            // 查询数据
            $srcRecordList = $srcTable->select($srcField, $filter, $optionArray);

            // 转换数据到目标表中
            foreach ($srcRecordList as $srcRecordItem) {

                // PreFunc 处理
                if ($recordPreFunc) {
                    $recordPreFunc($srcRecordItem);
                }

                $dstTable = new DstDataMapper($dstTableName);
                //字段复制
                foreach ($columnMap as $srcField => $dstField) {

                    // 无意义的字段，不复制
                    if (null == $dstField) {
                        continue;
                    }

                    if (isset($srcValueConvertFuncArray) && isset($srcValueConvertFuncArray[$srcField])) {
                        if (!is_callable($srcValueConvertFuncArray[$srcField])) {
                            printLog(
                                $srcField . ' value convert is not function ',
                                self::$loggerSource,
                                \Core\Log\Base::ERROR
                            );
                            continue;
                        }

                        // 做数据转化
                        $dstTable->$dstField =
                            $srcValueConvertFuncArray[$srcField]($srcRecordItem[$srcField], $srcRecordItem);

                    } else {
                        $dstTable->$dstField = $srcRecordItem[$srcField];
                    }
                }

                // postFunc 处理
                if ($recordPostFunc) {
                    $recordPostFunc($srcRecordItem, $dstTable);
                }

                $dstTable->save();
                unset($dstTable); // 及时释放数据，优化内存使用
            }

            unset($srcRecordList); // 及时释放数据，优化内存使用
            gc_collect_cycles(); // 主动做一次垃圾回收
        }

        printLog(
            'finish convertTable ' . $srcTableName . '-->' . $dstTableName . ' totalRecordCount:' . $totalRecordCount,
            self::$loggerSource
        );

    }

}
