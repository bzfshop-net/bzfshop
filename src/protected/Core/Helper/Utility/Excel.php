<?php

/**
 * @author QiangYu
 *
 * 一个工具集合类
 *
 * */

namespace Core\Helper\Utility;

final class Excel {

    /**
     * 把数组数据输出到 Excel 表格
     * 
     * @param array $dataArray 数据格式为 array(array(第一行数据), array(第二行数据), ...)
     * @param string $fileName 文件名，不带 xls 后缀，不带路径
     * @param string $filePath 输出文件的路径，如果为 null 表示输出到浏览器作为附件下载
     * @param int $maxCellWidth  最大单元格宽度限制
     * * */
    public static function dumpArrayToExel(array $dataArray, $fileName, $filePath = null, $maxCellWidth = null) {
        $objPHPExcel = new \PHPExcel();

        // 设置工作 sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();
        $activeSheet->fromArray($dataArray);

        // 调整列大小，防止数据挤到一起去了
        $maxColumnIndex = $activeSheet->getColumnDimension($activeSheet->getHighestColumn())->getColumnIndex();
        for ($columnIndex = 'A'; $columnIndex != $maxColumnIndex; $columnIndex++) {
            $activeSheet->getColumnDimension($columnIndex)->setAutoSize(true);
        }
        $activeSheet->calculateColumnWidths();

        if ($maxCellWidth > 0) {
            for ($columnIndex = 'A'; $columnIndex != $maxColumnIndex; $columnIndex++) {
                if ($activeSheet->getColumnDimension($columnIndex)->getWidth() > $maxCellWidth) {
                    $activeSheet->getColumnDimension($columnIndex)->setAutoSize(false);
                    $activeSheet->getColumnDimension($columnIndex)->setWidth($maxCellWidth);
                    $activeSheet->getStyle($columnIndex)->getAlignment()->setWrapText(true);
                }
            }
        }

        // 输出为 Excel5 格式
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        if (empty($filePath)) {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output'); //输出到浏览器  
            return;
        }

        $objWriter->save($filePath . '/' . urlencode($fileName) . '.xls'); //输出到文件
    }

}