<?php

/**
 * @author QiangYu
 *
 * 验证码生成工具类
 *
 * */

namespace Core\Helper\Image;

class Image
{
    // 缓存目录名
    public static $cacheDirName = 'cache';

    /**
     * 判断图片是否是 RGB 格式
     *
     * @param string $imagePath
     *
     * @return bool
     */
    public static function isImageRGB($imagePath)
    {
        if (!is_file($imagePath)) {
            return false;
        }
        $imageInfo = getimagesize($imagePath);
        return 3 == $imageInfo['channels'];
    }

    /**
     * @param string $dataPathRoot                图片集所在的根目录
     * @param string $imageFileRelativeName       源图片相对 $dataPathRoot 的路径
     * @param string $imageThumbFileRelativeName  目标片相对 $dataPathRoot 的路径
     * @param int    $width                       宽度
     * @param int    $height                      高度
     */
    public static function resizeImage(
        $dataPathRoot,
        $imageFileRelativeName,
        $imageThumbFileRelativeName,
        $width,
        $height
    ) {
        //生成缩略图
        if (extension_loaded('imagick')) {
            // 如果有 imagick 模块，优先选择 imagick 模块，因为生成的图片质量更高
            $img = new \Imagick($dataPathRoot . '/' . $imageFileRelativeName);
            $img->stripimage(); //去除图片信息
            $img->setimagecompressionquality(95); //保证图片的压缩质量，同时大小可以接受
            $img->thumbnailimage($width, $height, true);
            $img->writeimage($dataPathRoot . '/' . $imageThumbFileRelativeName);
            //主动释放资源，防止程序出错
            $img->destroy();
            unset($img);
        } else {
            // F3 框架的 Image 类限制只能操作 UI 路径中的文件，所以我们这里需要设置 UI 路径
            global $f3;
            $f3->set('UI', $dataPathRoot);
            $img = new \Image('/' . $imageFileRelativeName);
            $img->resize($width, $height, false);
            $img->dump('jpeg', $dataPathRoot . '/' . $imageThumbFileRelativeName);
            //主动释放资源，防止程序出错
            $img->__destruct();
            unset($img);
        }
    }

    public static function cropImage(
        $dataPathRoot,
        $imageFileRelativeName,
        $imageThumbFileRelativeName,
        $width,
        $height
    ) {
        //生成缩略图
        if (extension_loaded('imagick')) {
            // 如果有 imagick 模块，优先选择 imagick 模块，因为生成的图片质量更高
            $img = new \Imagick($dataPathRoot . '/' . $imageFileRelativeName);
            $img->stripimage(); //去除图片信息
            $img->setimagecompressionquality(95); //保证图片的压缩质量，同时大小可以接受
            $img->cropimage($width, $height, 0, 0);
            $img->writeimage($dataPathRoot . '/' . $imageThumbFileRelativeName);
            //主动释放资源，防止程序出错
            $img->destroy();
            unset($img);
        } else {
            // F3 框架的 Image 类限制只能操作 UI 路径中的文件，所以我们这里需要设置 UI 路径
            global $f3;
            $f3->set('UI', $dataPathRoot);
            $img = new \Image('/' . $imageFileRelativeName);
            $img->resize($width, $height, true);
            $img->dump('jpeg', $dataPathRoot . '/' . $imageThumbFileRelativeName);
            //主动释放资源，防止程序出错
            $img->__destruct();
            unset($img);
        }
    }

    /**
     * 生成一个新的尺寸的图片，并且保存到缓存目录里面
     *
     * @param  string $dataPathRoot           数据存放的根目录
     * @param  string $imageFileRelativeName  源图片文件的相对路径名，相对于 $dataPathRoot
     * @param   int   $width
     * @param   int   $height
     * @param bool    $crop                   如果图片长宽比例不合适，是否剪切掉
     *
     * @return string  返回相对于 $dataPathRoot 的新文件路径
     */
    public static function cropImageIfNotExist(
        $dataPathRoot,
        $imageFileRelativeName,
        $width,
        $height
    ) {

        $soureFilePath = $dataPathRoot . '/' . $imageFileRelativeName;
        // 源文件不存在，返回空
        if (!file_exists($soureFilePath)) {
            printLog('[' . $soureFilePath . '] does not exist', __CLASS__, \Core\Log\Base::ERROR);
            return '';
        }

        // 自动生成缩率文件，放在 Cache 目录下
        $pathInfoArray          = pathinfo($imageFileRelativeName);
        $targetDirPathRelative  = static::$cacheDirName . '/' . $pathInfoArray['dirname'];
        $targetFilePathRelative = $targetDirPathRelative
            . '/' . $pathInfoArray['filename'] . '_' . $width . 'x' . $height . '_'
            . 'crop' . '.' . $pathInfoArray['extension'];


        if (file_exists($dataPathRoot . '/' . $targetFilePathRelative)) {
            goto out;
        }

        // 如果目标目录不存在则建立它
        if (!is_dir($dataPathRoot . '/' . $targetDirPathRelative)) {
            @mkdir($dataPathRoot . '/' . $targetDirPathRelative, 0755, true);
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
