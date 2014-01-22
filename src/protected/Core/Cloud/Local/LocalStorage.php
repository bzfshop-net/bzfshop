<?php

/**
 * @author QiangYu
 *
 * 本地文件系统直接存储，$storageId 就是起始目录，所有的文件都是相对于这个起始目录而言的
 *
 * */

namespace Core\Cloud\Local;

use Core\Cloud\ICloudStorage;

class LocalStorage extends \Prefab implements ICloudStorage
{

    public function initStorage()
    {
        return true;
    }


    public function uploadFile($storageId, $targetRelativePath, $sourceFullPath)
    {
        // 自动建立目录路径
        $targetFullPath = $storageId . DIRECTORY_SEPARATOR . $targetRelativePath;
        $pathInfo       = pathinfo($targetFullPath);
        if (@$pathInfo['dirname'] && !file_exists($pathInfo['dirname'])) {
            if (!mkdir($pathInfo['dirname'], 0755, true)) {
                return false;
            }
        }
        // 上传文件
        return move_uploaded_file($sourceFullPath, $targetFullPath);
    }

    public function readFile($storageId, $relativePath)
    {
        return file_get_contents($storageId . DIRECTORY_SEPARATOR . $relativePath);
    }

    public function writeFile($storageId, $relativePath, $content)
    {
        return file_put_contents($storageId . DIRECTORY_SEPARATOR . $relativePath, $content);
    }

    public function removeFile($storageId, $relativePath)
    {
        return unlink($storageId . DIRECTORY_SEPARATOR . $relativePath);
    }

    public function moveFileToStorage($storageId, $targetRelativePath, $sourceFullPath)
    {
        // 自动建立目录路径
        $targetFullPath = $storageId . DIRECTORY_SEPARATOR . $targetRelativePath;
        $pathInfo       = pathinfo($targetFullPath);
        if (@$pathInfo['dirname'] && !file_exists($pathInfo['dirname'])) {
            if (!mkdir($pathInfo['dirname'], 0755, true)) {
                return false;
            }
        }
        return rename($sourceFullPath, $storageId . DIRECTORY_SEPARATOR . $targetRelativePath);
    }

    public function getTempFilePath($fileName = null)
    {
        $fileName = $fileName ? : uniqid();
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
    }

    public function createTempFileForStorageFile($storageId, $relativePath)
    {
        $tempFilePath = $this->getTempFilePath(basename($relativePath));
        copy($storageId . DIRECTORY_SEPARATOR . $relativePath, $tempFilePath);
        return $tempFilePath;
    }

    public function fileExists($storageId, $relativePath)
    {
        return file_exists($storageId . DIRECTORY_SEPARATOR . $relativePath);
    }

    public function getFileModifyTime($storageId, $relativePath)
    {
        return filemtime($storageId . DIRECTORY_SEPARATOR . $relativePath);
    }

}