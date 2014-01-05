<?php
/**
 * @author QiangYu
 *
 * 云平台的数据存储实现
 *
 */

namespace Core\Cloud;


interface ICloudStorage
{

    /**
     * 存储系统的初始化
     *
     * @return bool
     */
    public function initStorage();

    /**
     * 把文件上传到 Storage 系统中
     *
     * @param string $storageId          存储系统的 ID，留作将来的扩展
     * @param string $targetRelativePath 目标文件路径
     * @param string $sourceFullPath     源文件路径
     *
     * @return mixed
     */
    public function uploadFile($storageId, $targetRelativePath, $sourceFullPath);

    /**
     * 读取并返回文件的内容
     *
     * @param string $storageId
     * @param string $relativePath
     *
     * @return string
     */
    public function readFile($storageId, $relativePath);

    /**
     * 写入文件内容到 Storage
     *
     * @param string $storageId
     * @param string $relativePath
     * @param string $content
     *
     * @return int
     */
    public function writeFile($storageId, $relativePath, $content);

    /**
     * 删除文件、目录
     *
     * @param string $storageId
     * @param string $relativePath
     *
     * @return bool
     */
    public function removeFile($storageId, $relativePath);

    /**
     * 文件、目录是否存在
     *
     * @param string $storageId
     * @param string $relativePath
     *
     * @return bool
     */
    public function fileExists($storageId, $relativePath);

    /**
     * 取得文件的修改时间
     *
     * @param string $storageId
     * @param string $relativePath
     *
     * @return int 返回 unix 时间戳
     */
    public function getFileModifyTime($storageId, $relativePath);
} 