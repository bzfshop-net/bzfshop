<?php

/**
 * @author QiangYu
 *
 * Sae Storage 的实现，用于存储主要的数据，比如用户上传的图片、文件等等
 *
 * */

namespace Core\Cloud\Sae;

use Core\Cloud\ICloudStorage;

class SaeStorage extends \Prefab implements ICloudStorage
{

    private static $domain = null;
    private static $saeStorage = null;

    function __construct()
    {
        if (!self::$domain) {
            global $f3;
            self::$domain = $f3->get('sysConfig[sae_storage_data_domain]');
        }

        if (!self::$saeStorage) {
            self::$saeStorage = new \SaeStorage();
        }
    }

    public function initStorage()
    {
        return true;
    }

    public function uploadFile($storageId, $targetRelativePath, $sourceFullPath)
    {
        return self::$saeStorage->upload(
            self::$domain,
            $targetRelativePath,
            $sourceFullPath
        );
    }

    public function readFile($storageId, $relativePath)
    {
        return self::$saeStorage->read(self::$domain, $relativePath);
    }

    public function writeFile($storageId, $relativePath, $content)
    {
        return self::$saeStorage->write(self::$domain, $relativePath, $content);
    }

    public function removeFile($storageId, $relativePath)
    {
        return self::$saeStorage->delete(self::$domain, $relativePath);
    }

    public function moveFileToStorage($storageId, $targetRelativePath, $sourceFullPath)
    {
        return $this->uploadFile($storageId, $targetRelativePath, $sourceFullPath);
    }

    public function getTempFilePath($fileName = null)
    {
        $fileName = $fileName ? : uniqid();
        return SAE_TMP_PATH . DIRECTORY_SEPARATOR . $fileName;
    }

    public function createTempFileForStorageFile($storageId, $relativePath)
    {
        $tempFilePath = $this->getTempFilePath(basename($relativePath));
        file_put_contents($tempFilePath, $this->readFile($storageId, $relativePath));
        return $tempFilePath;
    }

    public function fileExists($storageId, $relativePath)
    {
        return self::$saeStorage->fileExists(self::$domain, $relativePath);
    }

    public function getFileModifyTime($storageId, $relativePath)
    {
        $attrArray = self::$saeStorage->getAttr(self::$domain, $relativePath, array('datetime'));
        return @$attrArray['datetime'];
    }

}