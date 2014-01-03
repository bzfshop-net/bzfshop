<?php

/**
 * @author QiangYu
 *
 * 一个工具集合类
 *
 * */

namespace Core\Helper\Utility;

use Core\Service\Meta\Dictionary as MetaDictionaryService;

final class Utils
{

    /**
     * tag 的分隔符
     */
    public static $tagSeparator = ',';

    /**
     * 判断参数是否为空
     *
     * @return boolean
     *
     * @param mixed $value
     * */
    public static function isEmpty($value)
    {
        return (isset($value) ? empty($value) : true);
    }

    /**
     * 判断字符串是否为空，包括 null, '' 多个空格等
     *
     * @return boolean
     *
     * @param string $str
     * */
    public static function isBlank($str)
    {
        if (!isset($str)) {
            return true;
        }

        $trimValue = trim($str);
        return empty($trimValue);
    }

    /**
     * 对字符串做 mask ，比如用户名不希望完全显示
     *
     * @param string $srcStr
     *
     * @return string
     */
    public static function maskString($srcStr)
    {
        $dstStr = '';

        $srcLen = mb_strlen($srcStr);

        if ($srcLen <= 0) {
            $dstStr = '*';
            goto out;
        }

        $headLen = ($srcLen > 4) ? 4 : intval(ceil($srcLen / 2));
        $tailLen = ($srcLen > 10) ? 3 : 1;
        if ($headLen + $tailLen >= $srcLen) {
            $headLen--;
            $tailLen--;
        }
        $headLen = ($headLen > 0) ? $headLen : 0;
        $tailLen = ($tailLen > 0) ? $tailLen : 0;
        $padLen  = $srcLen - $headLen - $tailLen;
        $padLen  = ($padLen <= 0 || $padLen > 5) ? 5 : $padLen;

        // 生成最后的字符串
        $dstStr =
            mb_substr($srcStr, 0, $headLen)
            . str_pad('', $padLen, '*')
            . mb_substr(
                $srcStr,
                $srcLen - $tailLen,
                $tailLen
            );

        out:
        return $dstStr;
    }

    /**
     * 从数组生成 Tag
     *
     * 注意： 我们在 头、尾 都加上 separator 方便做查询
     * 比如  ,tag1,tag2,tag3,  查询的时候就用 ,tag1, 做查询就可以了
     * 如果不加上头尾的分隔符，容易出错，比如 tag1,tag2,tag23,tag34 如果我们查询 tag2 ，就会出现误查
     *
     * @param array $tagArray
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function makeTagString($tagArray)
    {
        if (empty($tagArray)) {
            return '';
        }

        if (!is_array($tagArray)) {
            throw new \InvalidArgumentException('tagArray is invalid [' . var_export($tagArray, true) . ']');
        }

        return self::$tagSeparator . implode(self::$tagSeparator, $tagArray) . self::$tagSeparator;
    }

    /**
     * 把 tag 解析为 array
     *
     * @param $tagString
     *
     * @return array
     */
    public static function parseTagString($tagString)
    {
        if (empty($tagString)) {
            return array();
        }

        // 去除头尾的 separator
        $length = strlen($tagString);
        if (self::$tagSeparator == $tagString[$length - 1]) {
            $length--;
        }

        $startIndex = (self::$tagSeparator == $tagString[0]) ? 1 : 0;
        if ($startIndex > 0) {
            $length--;
        }

        $tagString = substr($tagString, $startIndex, $length);
        return explode(self::$tagSeparator, $tagString);
    }

    /**
     * 判断 $tag 是否存在 $searchStr 中
     *
     * @param string $tag
     * @param string $searchStr
     *
     * @return bool
     */
    public static function isTagExist($tag, $searchStr)
    {
        $needle = self::$tagSeparator . $tag . self::$tagSeparator;
        return false !== strrpos($searchStr, $needle);
    }

    /**
     * 过滤去除 $src 中除去 字母、数字 意外的别的字符
     *
     * @return string
     *
     * @param $src
     */
    public static function filterAlnumStr($src)
    {

        $len    = strlen($src);
        $result = '';
        for ($index = 0; $index < $len; $index++) {
            $char = $src[$index];
            if (ctype_alnum($char)) {
                $result .= $char;
            }
        }

        return $result;
    }

    /**
     * 生成随机的 ID 供 html 使用
     *
     * @return string
     */
    public static function generateRandomHtmlId()
    {
        return 'gen_id_' . time() . '_' . mt_rand(1, 100000);
    }

    /**
     * 递归创建所有子目录
     *
     * @return boolean
     *
     * @param string $path 目录的路径，例如 /tmp/aaa/bbb/ccc/ddd
     *
     * */
    public static function mkDir($path)
    {
        $arr = array();

        while (!is_dir($path)) {
            // 例 /a/b/c/d 如果不目录,则是我的工作
            array_push($arr, $path); //工作计划入栈
            $path = dirname($path);
        }

        if (empty($arr)) {
            return true;
        }

        // 工作计划出栈
        while (count($arr)) {
            $tmp = array_pop($arr);
            @mkdir($tmp);
        }

        return true;
    }

    /**
     * 删除文件
     *
     * @param string $filePath 文件完整路径
     */
    public static function rmFile($filePath)
    {
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }


    /**
     * 递归复制所有的文件和目录到新地方
     *
     * @param $source
     * @param $destination
     */
    public static function copyFile($source, $destination, $dirMask = 0755, $fileMask = 0644)
    {
        if (is_dir($source)) {
            @mkdir($destination, $dirMask, true);
            @touch($destination);
            $directory = dir($source);
            while (false !== ($readdirectory = $directory->read())) {
                if ($readdirectory == '.' || $readdirectory == '..') {
                    continue;
                }
                $PathDir = $source . DIRECTORY_SEPARATOR . $readdirectory;
                if (is_dir($PathDir)) {
                    Utils::copyFile($PathDir, $destination . DIRECTORY_SEPARATOR . $readdirectory);
                    continue;
                }
                copy($PathDir, $destination . DIRECTORY_SEPARATOR . $readdirectory);
                @chmod($destination . DIRECTORY_SEPARATOR . $readdirectory, $fileMask);
            }

            $directory->close();
        } else {
            //如果目录不存在，则需要建立目录
            $destDir = dirname($destination);
            if (!is_dir($destDir)) {
                @mkdir($destDir, $dirMask, true);
                @touch($destDir);
            }
            copy($source, $destination);
            @chmod($destination, $fileMask);
        }
    }

    /**
     * 复制一个文件，返回复制后文件的相对路径
     *
     * @param string $dataPathRoot
     * @param string $fileRelativePath
     *
     * @return string
     */
    public static function duplicateFile($dataPathRoot, $fileRelativePath)
    {
        $sourceFilePath = $dataPathRoot . '/' . $fileRelativePath;
        if (!is_file($sourceFilePath) || !file_exists($sourceFilePath)) {
            return '';
        }

        $pathInfoArray = pathinfo($sourceFilePath);
        $baseFileName  = $pathInfoArray['basename'];
        // 截断文件名，防止文件名太长了
        if (strlen($baseFileName) > 12) {
            $baseFileName = substr($baseFileName, strlen($baseFileName) - 12);
        }
        $targetFilePath = $pathInfoArray['dirname'] . '/' . date("YmdHis") . '_' . rand(1, 10000) . '_' . $baseFileName;

        // 复制文件
        Utils::copyFile($sourceFilePath, $targetFilePath);

        // 去掉 dataRootPath，返回相对路径
        return str_replace($dataPathRoot . '/', '', $targetFilePath);
    }

    /**
     * 调用字典服务翻译内容
     *
     * @param $key
     *
     * @return mixed
     */
    public static function dictionaryName($key)
    {
        if (empty($key)) {
            return $key;
        }

        $metaDictionaryService = new MetaDictionaryService();
        $dictItem              = $metaDictionaryService->getWord($key, 600); //缓存 10 分钟
        return $dictItem['name'];
    }
}