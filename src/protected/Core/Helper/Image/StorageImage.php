<?php

/**
 * @author QiangYu
 *
 * Storage 中的图片操作， Storage 中一个很大的不同就是 文件不能像简单的文件一样读写，所以很多图片处理函数也不能直接操作
 * 一般都需要 复制到临时目录中，操作，然后写回到 Storage 中
 *
 * */

namespace Core\Helper\Image;

use Core\Cloud\CloudHelper;

class StorageImage
{
    // 缓存目录名
    public static $cacheDirName = 'cache';

    /**
     * 对 Storage 中的图片做 Resize 操作，注意 Storage 的文件不能简单的用文件函数操作，需要创建一个临时文件，操作，然后写回去
     *
     * @param string $dataPathRoot               图片集所在的根目录
     * @param string $imageFileRelativeName      源图片相对 $dataPathRoot 的路径
     * @param string $imageThumbFileRelativeName 目标片相对 $dataPathRoot 的路径
     * @param int    $width                      宽度
     * @param int    $height                     高度
     */
    public static function resizeImage(
        $dataPathRoot,
        $imageFileRelativeName,
        $imageThumbFileRelativeName,
        $width,
        $height
    ) {

        $cloudStorage     = CloudHelper::getCloudModule(CloudHelper::CLOUD_MODULE_STORAGE);
        $tempSrcFilePath  = $cloudStorage->createTempFileForStorageFile($dataPathRoot, $imageFileRelativeName);
        $tempDestFilePath = $cloudStorage->getTempFilePath();

        //生成缩略图
        if (extension_loaded('imagick')) {
            // 如果有 imagick 模块，优先选择 imagick 模块，因为生成的图片质量更高
            $img = new \Imagick($tempSrcFilePath);
            $img->stripimage(); //去除图片信息
            $img->setimagecompressionquality(95); //保证图片的压缩质量，同时大小可以接受
            $img->thumbnailimage($width, $height, true);
            // 把图片先写到临时文件中
            $img->writeimage($tempDestFilePath);
            // 然后把临时文件移动到 Storage 存储中
            $cloudStorage->moveFileToStorage($dataPathRoot, $imageThumbFileRelativeName, $tempDestFilePath);
            //主动释放资源，防止程序出错
            $img->destroy();
            unset($img);
        } else {
            // F3 框架的 Image 类限制只能操作 UI 路径中的文件，所以我们这里需要设置 UI 路径
            global $f3;
            $f3->set('UI', dirname($tempSrcFilePath));
            $img = new \Image('/' . basename($tempSrcFilePath));
            $img->resize($width, $height, false);
            // 把图片先写到临时文件中
            $img->dump('jpeg', $tempDestFilePath);
            // 然后把临时文件移动到 Storage 存储中
            $cloudStorage->moveFileToStorage($dataPathRoot, $imageThumbFileRelativeName, $tempDestFilePath);
            //主动释放资源，防止程序出错
            $img->__destruct();
            unset($img);
        }

        // 删除临时文件
        @unlink($tempSrcFilePath);
        @unlink($tempDestFilePath);
    }

    public static function cropImage(
        $dataPathRoot,
        $imageFileRelativeName,
        $imageThumbFileRelativeName,
        $width,
        $height
    ) {

        $cloudStorage     = CloudHelper::getCloudModule(CloudHelper::CLOUD_MODULE_STORAGE);
        $tempSrcFilePath  = $cloudStorage->createTempFileForStorageFile($dataPathRoot, $imageFileRelativeName);
        $tempDestFilePath = $cloudStorage->getTempFilePath();

        //生成缩略图
        if (extension_loaded('imagick')) {
            // 如果有 imagick 模块，优先选择 imagick 模块，因为生成的图片质量更高
            $img = new \Imagick($tempSrcFilePath);
            $img->stripimage(); //去除图片信息
            $img->setimagecompressionquality(95); //保证图片的压缩质量，同时大小可以接受
            $img->cropimage($width, $height, 0, 0);
            $img->writeimage($tempDestFilePath);
            $cloudStorage->moveFileToStorage($dataPathRoot, $imageThumbFileRelativeName, $tempDestFilePath);
            //主动释放资源，防止程序出错
            $img->destroy();
            unset($img);
        } else {
            // F3 框架的 Image 类限制只能操作 UI 路径中的文件，所以我们这里需要设置 UI 路径
            global $f3;
            $f3->set('UI', dirname($tempSrcFilePath));
            $img = new \Image('/' . basename($tempSrcFilePath));
            $img->resize($width, $height, true);
            $img->dump('jpeg', $tempDestFilePath);
            $cloudStorage->moveFileToStorage($dataPathRoot, $imageThumbFileRelativeName, $tempDestFilePath);
            //主动释放资源，防止程序出错
            $img->__destruct();
            unset($img);
        }

        // 删除临时文件
        @unlink($tempSrcFilePath);
        @unlink($tempDestFilePath);
    }

    /**
     * 生成一个新的尺寸的图片，并且保存到缓存目录里面
     *
     * @param  string $dataPathRoot          数据存放的根目录
     * @param  string $imageFileRelativeName 源图片文件的相对路径名，相对于 $dataPathRoot
     * @param   int   $width
     * @param   int   $height
     * @param bool    $crop                  如果图片长宽比例不合适，是否剪切掉
     *
     * @return string  返回相对于 $dataPathRoot 的新文件路径
     */
    public static function cropImageIfNotExist(
        $dataPathRoot,
        $imageFileRelativeName,
        $width,
        $height
    ) {

        $cloudStorage = CloudHelper::getCloudModule(CloudHelper::CLOUD_MODULE_STORAGE);

        $soureFilePath = $dataPathRoot . '/' . $imageFileRelativeName;

        // 源文件不存在，返回空
        if (!$cloudStorage->fileExists($dataPathRoot, $imageFileRelativeName)) {
            printLog('[' . $soureFilePath . '] does not exist', __CLASS__, \Core\Log\Base::ERROR);
            return '';
        }

        // 自动生成缩率文件，放在 Cache 目录下
        $pathInfoArray          = pathinfo($imageFileRelativeName);
        $targetDirPathRelative  = static::$cacheDirName . '/' . $pathInfoArray['dirname'];
        $targetFilePathRelative = $targetDirPathRelative
            . '/' . $pathInfoArray['filename'] . '_' . $width . 'x' . $height . '_'
            . 'crop' . '.' . $pathInfoArray['extension'];

        if ($cloudStorage->fileExists($dataPathRoot, $targetFilePathRelative)) {
            goto out;
        }

        self::cropImage(
            $dataPathRoot,
            $imageFileRelativeName,
            $targetFilePathRelative,
            $width,
            $height
        );

        printLog(
            'crop [' . $soureFilePath . '] to [' . $dataPathRoot . '/' . $targetFilePathRelative
            . '] width [' . $width . '] height [' . $height . ']',
            __CLASS__
        );

        out:
        return $targetFilePathRelative;
    }
}
