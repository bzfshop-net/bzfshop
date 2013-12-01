<?php

/**
 * KindEditor 的封装，方便 KindEditor 更好的集成到我们的框架中
 * 增强安全性
 *
 * @author YuQiangWin7
 */

namespace KindEditor;

use Core\Helper\Utility\FileUpload;

class KindEditor
{

    //定义允许上传的文件扩展名
    private $allowFileExt = array(
        'image'               => array('.gif', '.jpg', '.jpeg', '.png', '.bmp'),
        'flash'               => array('.swf', '.flv'),
        'media'               => array(
            '.swf',
            '.flv',
            '.mp3',
            '.wav',
            '.wma',
            '.wmv',
            '.mid',
            '.avi',
            '.mpg',
            '.asf',
            '.rm',
            '.rmvb'
        ),
        'file'                => array(
            '.doc',
            '.docx',
            '.xls',
            '.xlsx',
            '.ppt',
            '.htm',
            '.html',
            '.txt',
            '.zip',
            '.rar',
            '.gz',
            '.bz2'
        ),
        'image_goods_promote' => array('.png', '.gif', '.jpg', '.jpeg'), // 商品推广渠道指定的图片
        'image_other'         => array('.png', '.gif', '.jpg', '.jpeg'), // 网站设置的广告图片
        'image_article'       => array('.png', '.gif', '.jpg', '.jpeg'), // 网站文章的图片
        // 不要用 image_adv 之类，因为很多浏览器的 广告过滤插件会自动过滤这种 URL，结果你的图片就显示不出来了
    );

    public function __construct()
    {
        global $f3;

        // 可以在 cfg 文件中设置上传文件允许的扩展名
        $fileUploadConfig = $f3->get('sysConfig[file_upload]');
        if (empty($fileUploadConfig)) {
            return;
        }

        if (isset($fileUploadConfig['image'])
            && !empty($fileUploadConfig['image'])
            && is_array($fileUploadConfig['image'])
        ) {
            $this->allowFileExt['image'] = $fileUploadConfig['image'];
        }

        if (isset($fileUploadConfig['image_goods_promote'])
            && !empty($fileUploadConfig['image_goods_promote'])
            && is_array($fileUploadConfig['image_goods_promote'])
        ) {
            $this->allowFileExt['image_goods_promote'] = $fileUploadConfig['image_goods_promote'];
        }

        if (isset($fileUploadConfig['image_other'])
            && !empty($fileUploadConfig['image_other'])
            && is_array($fileUploadConfig['image_other'])
        ) {
            $this->allowFileExt['image_other'] = $fileUploadConfig['image_other'];
        }

        if (isset($fileUploadConfig['image_article'])
            && !empty($fileUploadConfig['image_article'])
            && is_array($fileUploadConfig['image_article'])
        ) {
            $this->allowFileExt['image_article'] = $fileUploadConfig['image_article'];
        }
    }

    /**
     * 上传文件服务
     *
     * @return 返回 FileUpload 的 fileInfo 信息， 失败返回 null
     */
    private function upload($dataPathRoot, $dataUrlPrefix)
    {

        $errorMessage = '上传错误';

        $dirName = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
        // 可以通过 dirname 参数来指定上传的目录
        $dirName = empty($_GET['dirname']) ? $dirName : trim($_GET['dirname']);

        if (!isset($this->allowFileExt[$dirName]) || empty($this->allowFileExt[$dirName])) {
            $errorMessage = '目录名不正确[' . $dirName . ']';
            goto out_error;
        }

        //上传配置
        $config = array(
            "savePath"   => $dataPathRoot . '/upload/' . $dirName,
            "pathFix"    => $dataPathRoot,
            'urlPrefix'  => $dataUrlPrefix,
            "maxSize"    => 5000, //单位KB, 5MB 差不多够大了
            "allowFiles" => $this->allowFileExt[$dirName]
        );

        //上传文件
        $fileUpload = new FileUpload('imgFile', $config);
        //取得上传之后的文件信息
        $fileInfo = $fileUpload->getFileInfo();

        //上传成功
        if (0 != $fileInfo['errorCode']) {
            $errorMessage = $fileInfo['state'];
            goto out_error;
        }

        header('Content-type: text/html; charset=UTF-8');
        $jsonArray = array('error' => 0, 'url' => $fileInfo['url']);

        // 目前 KindEditor 对于返回图片大小有 BUG，不能使用
        /*
          if ($fileInfo['imageWidth'] > 0) {
          $jsonArray['width'] = $fileInfo['imageWidth'];
          }
          if ($fileInfo['imageHeight'] > 0) {
          $jsonArray['height'] = $fileInfo['imageHeight'];
          } */

        echo json_encode($jsonArray);
        return $fileInfo; // 成功，返回文件信息

        out_error: //失败，返回错误        
        header('Content-type: text/html; charset=UTF-8');
        echo json_encode(array('error' => 1, 'message' => $errorMessage));
        return null;
    }

    /**
     * 做 KindEditor 对应的操作
     *
     * @param string $dataPathRoot  上传路径的根目录
     * @param string $dataUrlPrefix 上传路径对应的 URL 前缀
     * @param string $action        操作
     * * */
    public function doAction($dataPathRoot, $dataUrlPrefix, $action)
    {

        switch ($action) {

            case 'upload': // 上传文件，返回文件信息
                return $this->upload($dataPathRoot, $dataUrlPrefix);
                break;

            case 'manage': // 文件管理
                $root_path = $dataPathRoot . '/upload/';
                $root_url  = $dataUrlPrefix . '/upload/';
                goto do_action;
                break;

            default: // 不认识的操作，直接返回
                echo json_encode(array('error' => 1, 'message' => "action [$action] does not exist"));
                return;
        }

        return; // 失败从这里返回

        do_action: // 这里做实际的操作
        include_once dirname(__FILE__) . '/' . $action . '.php';
    }

}

