<?php

/**
 * 编辑器通用上传类
 */

namespace Core\Helper\Utility;

use Core\Helper\Image\Image;

class FileUpload
{

    private $errorCode = -1; // 错误码，错误为 -1 ，上传成功为 0
    private $fileField; //表单提交时候文件对应的名字，比如 $_POST['upfile']
    private $file; //文件上传对象
    private $config; //配置信息
    private $oriName; //原始文件名
    private $fileName; //新文件名
    private $fullName; //完整文件名,绝对路径
    private $relativeName; //相对路径文件名,即从当前配置目录开始的路径
    private $fileSize; //文件大小
    private $imageWidth = 0; // 图片宽度
    private $imageHeight = 0; // 图片高度
    private $imageType = array(".gif", ".png", ".jpg", ".jpeg", ".bmp"); // 只对这些扩展名的图片取 宽度 和 高度
    private $fileType; //文件类型
    private $stateInfo; //上传状态信息,
    private $stateMap = array( //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "POST"    => "文件大小超出 post_max_size 限制",
        "SIZE"    => "文件大小超出网站限制",
        "TYPE"    => "不允许的文件类型",
        "RGB"     => "图片不是RGB格式",
        "DIR"     => "目录创建失败",
        "IO"      => "输入输出错误",
        "UNKNOWN" => "未知错误",
        "MOVE"    => "文件保存时出错"
    );

    /**
     * 构造函数
     *
     * 一个配置的简单例子
     *
     *  $config = array(
     *       "savePath" => $dataPathRoot . '/upload/image',
     *       "pathFix" => $dataPathRoot,
     *       'urlPrefix' => $dataUrlPrefix,
     *       "maxSize" => 1000, //单位KB
     *       "allowFiles" => array(".gif", ".png", ".jpg", ".jpeg")
     *  );
     *
     * @param string $fileField 表单名称
     * @param array  $config    配置项
     * @param bool   $base64    是否解析base64编码，可省略。若开启，则$fileField代表的是base64编码的字符串表单名
     */
    public function __construct($fileField, $config, $base64 = false)
    {
        $this->fileField = $fileField;
        $this->config    = $config;
        $this->stateInfo = $this->stateMap[0];
        $this->upFile($base64);
    }

    /**
     * 上传文件的主处理方法
     * @param $base64
     *
     * @return mixed
     */
    private function upFile($base64)
    {
        //处理base64上传
        if ("base64" == $base64) {
            $content = $_POST[$this->fileField];
            $this->base64ToImage($content);
            return;
        }

        //处理普通上传
        $file = $this->file = $_FILES[$this->fileField];
        if (!$file) {
            $this->stateInfo = $this->getStateInfo('POST');
            return;
        }
        if ($this->file['error']) {
            $this->stateInfo = $this->getStateInfo($file['error']);
            return;
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("UNKNOWN");
            return;
        }

        $this->oriName  = $file['name'];
        $this->fileSize = $file['size'];
        $this->fileType = $this->getFileExt();

        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("SIZE");
            return;
        }
        if (!$this->checkType()) {
            $this->stateInfo = $this->getStateInfo("TYPE");
            return;
        }

        // 图片必须是 RGB 格式，不允许上传 CMYK 格式图片
        if (in_array($this->fileType, $this->imageType) && !Image::isImageRGB($file["tmp_name"])) {
            $this->stateInfo = $this->getStateInfo("RGB");
            return;
        }

        $this->fullName = $this->getFolder() . '/' . $this->getName();

        if ($this->stateInfo == $this->stateMap[0]) {

            if (!move_uploaded_file($file["tmp_name"], $this->fullName)) {
                $this->stateInfo = $this->getStateInfo("MOVE");
            } else {

                if (in_array($this->fileType, $this->imageType)) {
                    // 如果是图片我们需要取得图片的 宽度 和 高度
                    list($this->imageWidth, $this->imageHeight) = getimagesize($this->fullName);
                }

                //上传成功，设置错误码
                $this->errorCode = 0;
            }
        }
    }

    /**
     * 处理base64编码的图片上传
     *
     * @param $base64Data
     *
     * @return mixed
     */
    private function base64ToImage($base64Data)
    {
        $img            = base64_decode($base64Data);
        $this->fileName = time() . rand(1, 10000) . ".png";
        $this->fullName = $this->getFolder() . '/' . $this->fileName;
        if (!file_put_contents($this->fullName, $img)) {
            $this->stateInfo = $this->getStateInfo("IO");
            return;
        }
        $this->oriName  = "";
        $this->fileSize = strlen($img);
        $this->fileType = ".png";
    }

    /**
     * 获取当前上传成功文件的各项信息
     * @return array
     */
    public function getFileInfo()
    {

        if (!empty($this->config['pathFix'])) {
            $this->relativeName = str_replace($this->config['pathFix'] . '/', '', $this->fullName);
        } else {
            $this->relativeName = $this->fullName;
        }

        $fileInfoArray = array(
            "errorCode"    => $this->errorCode,
            "originalName" => $this->oriName,
            "name"         => $this->fileName,
            "fullName"     => $this->fullName,
            "relativeName" => $this->relativeName,
            "url"          => $this->config['urlPrefix'] . '/' . $this->relativeName,
            "size"         => $this->fileSize,
            "type"         => $this->fileType,
            "state"        => $this->stateInfo
        );

        // 如果是图片，并且有图片尺寸信息，我们输出图片尺寸
        if ($this->imageWidth) {
            $fileInfoArray['imageWidth'] = $this->imageWidth;
        }
        if ($this->imageHeight) {
            $fileInfoArray['imageHeight'] = $this->imageHeight;
        }

        return $fileInfoArray;
    }

    /**
     * 上传错误检查
     * @param $errCode
     *
     * @return string
     */
    private function getStateInfo($errCode)
    {
        return !$this->stateMap[$errCode] ? $this->stateMap["UNKNOWN"] : $this->stateMap[$errCode];
    }

    /**
     * 重命名文件
     * @return string
     */
    private function getName()
    {
        return $this->fileName = date("YmdHis") . '_' . rand(1, 10000) . $this->getFileExt();
    }

    /**
     * 文件类型检测
     * @return bool
     */
    private function checkType()
    {
        return in_array($this->getFileExt(), $this->config["allowFiles"]);
    }

    /**
     * 文件大小检测
     * @return bool
     */
    private function checkSize()
    {
        return $this->fileSize <= ($this->config["maxSize"] * 1024);
    }

    /**
     * 获取文件扩展名
     * @return string
     */
    private function getFileExt()
    {
        return strtolower(strrchr($this->file["name"], '.'));
    }

    /**
     * 按照日期自动创建存储文件夹
     * @return string
     */
    private function getFolder()
    {
        $pathStr = $this->config["savePath"];
        if (strrchr($pathStr, "/") != "/") {
            $pathStr .= "/";
        }
        $pathStr .= date("Y/m/d");
        if (!file_exists($pathStr)) {
            if (!mkdir($pathStr, 0755, true)) {
                return false;
            }
        }
        return $pathStr;
    }

}