<?php

/**
 * @author QiangYu
 *
 * 普通图片处理类
 *
 * */

namespace Core\Helper\Image;

class Image
{
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
        return 3 == @$imageInfo['channels'];
    }
}
