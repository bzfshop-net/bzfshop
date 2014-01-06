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
     * 把一个传统文件移动到 Storage 保存
     *
     * @param string $storageId
     * @param string $targetRelativePath
     * @param string $sourceFullPath
     *
     * @return mixed
     */
    public function moveFileToStorage($storageId, $targetRelativePath, $sourceFullPath);

    /**
     * 生成一个临时文件的路径，确保可以读写
     *
     * @param string $fileName 临时文件名，不写的话会自动生成一个
     *
     * @return string
     */
    public function getTempFilePath($fileName = null);

    /**
     * 为 Storage 中的文件创建一个相应的临时文件用于操作
     *
     * @param string $storageId
     * @param string $relativePath
     *
     * @return string 返回临时文件的路径
     */
    public function createTempFileForStorageFile($storageId, $relativePath);

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