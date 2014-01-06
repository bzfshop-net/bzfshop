<?php

/**
 * @author QiangYu
 *
 * 商品图片相册编辑操作
 *
 * */

namespace Controller\Goods\Edit;

use Core\Cache\ClearHelper;
use Core\Cloud\CloudHelper;
use Core\Helper\Image\StorageImage as StorageImageHelper;
use Core\Helper\Utility\Ajax as AjaxHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Gallery as GoodsGalleryService;

class Gallery extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_get');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $goods_id  = $validator->required('商品ID不能为空')->digits()->min(1)->validate('goods_id');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 取得商品图片集信息
        $goodsGalleryService = new GoodsGalleryService();
        $goodsGalleryArray   = $goodsGalleryService->fetchGoodsGalleryArrayByGoodsId($goods_id);

        // 处理图片路径，全部需要生成为绝对路径
        foreach ($goodsGalleryArray as &$goodsGalleryItem) {
            $goodsGalleryItem['img_url']      = RouteHelper::makeImageUrl($goodsGalleryItem['img_url']);
            $goodsGalleryItem['thumb_url']    = RouteHelper::makeImageUrl($goodsGalleryItem['thumb_url']);
            $goodsGalleryItem['img_original'] = RouteHelper::makeImageUrl($goodsGalleryItem['img_original']);
        }

        // 显示商品图片集
        $smarty->assign('goodsGalleryArray', $goodsGalleryArray);

        out_display:
        $smarty->display('goods_edit_gallery.tpl');
        return;

        out_fail:
        RouteHelper::reRoute($this, '/Goods/Search');
    }

    /**
     * 上传一张图片
     *
     * @param $f3
     */
    public function Upload($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_post');

        // 参数验证
        $errorMessage = '';
        $validator    = new Validator($f3->get('POST'));
        $goods_id     = $validator->required('商品ID不能为空')->digits()->min(1)->validate('goods_id');

        if (!$this->validate($validator)) {
            $errorMessage = "param goods_id does not exist";
            goto out_fail;
        }

        // 上传路径
        $dataPathRoot = $f3->get('sysConfig[data_path_root]');
        if (empty($dataPathRoot)) {
            $dataPathRoot = $f3->get('BASE') . '/data';
        }

        // 上传路径对应的 URL 前缀
        $dataUrlPrefix = $f3->get('sysConfig[data_url_prefix]');
        if (empty($dataUrlPrefix)) {
            $dataUrlPrefix = $f3->get('BASE') . '/data';
        }

        // 我们的文件上传操作全部采用 KindEditor 来做
        $kindEditor = new \KindEditor\KindEditor();
        $fileInfo   = $kindEditor->doAction($dataPathRoot, $dataUrlPrefix, 'upload');

        // 文件上传之后调用后续处理，生成缩略图

        // 上传文件相对 dataPathRoot 的文件名
        $imageOriginalFileRelativeName = $fileInfo['relativeName'];

        $pathInfoArray = pathinfo($imageOriginalFileRelativeName);

        //生成头图
        $imageFileRelativeName =
            $pathInfoArray['dirname'] . '/' . $pathInfoArray['filename'] . '_'
            . $f3->get('sysConfig[image_width]') . 'x' . $f3->get('sysConfig[image_height]') . '.jpg';

        StorageImageHelper::resizeImage(
            $dataPathRoot,
            $imageOriginalFileRelativeName,
            $imageFileRelativeName,
            $f3->get('sysConfig[image_width]'),
            $f3->get('sysConfig[image_height]')
        );

        //生成缩略图
        $imageThumbFileRelativeName =
            $pathInfoArray['dirname'] . '/' . $pathInfoArray['filename'] . '_'
            . $f3->get('sysConfig[image_thumb_width]') . 'x' . $f3->get('sysConfig[image_thumb_height]') . '.jpg';

        StorageImageHelper::resizeImage(
            $dataPathRoot,
            $imageOriginalFileRelativeName,
            $imageThumbFileRelativeName,
            $f3->get('sysConfig[image_thumb_width]'),
            $f3->get('sysConfig[image_thumb_height]')
        );

        //保存 goods_gallery 记录
        $goodsGalleryService = new GoodsGalleryService();

        // ID 为0，返回一个新建的 dataMapper
        $goodsGallery = $goodsGalleryService->_loadById('goods_gallery', 'img_id=?', 0);

        $goodsGallery->goods_id     = $goods_id;
        $goodsGallery->img_desc     = $fileInfo['originalName'];
        $goodsGallery->img_original = $imageOriginalFileRelativeName;
        $goodsGallery->img_url      = $imageFileRelativeName;
        $goodsGallery->thumb_url    = $imageThumbFileRelativeName;

        $goodsGallery->save();

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goods_id);

        return;

        out_fail: // 失败从这里返回
        AjaxHelper::header();
        echo json_encode(array('error' => 1, 'message' => $errorMessage));
    }

    /**
     * 删除一张图片
     *
     * @param $f3
     */
    public function Remove($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_post');

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $img_id    = $validator->required('图片ID不能为空')->digits()->min(1)->validate('img_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        //操作 goods_gallery 记录
        $goodsGalleryService = new GoodsGalleryService();
        $goodsGallery        = $goodsGalleryService->_loadById('goods_gallery', 'img_id = ?', $img_id);

        if ($goodsGallery->isEmpty()) {
            $this->addFlashMessage('img_id [' . $img_id . '] 不存在');
            goto out;
        }

        // 删除数据库记录
        $goodsGallery->erase();
        $this->addFlashMessage('图片 [' . $img_id . '] 删除成功');

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goodsGallery->goods_id);

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }


    /**
     * 更新 goods_gallery 的内容
     *
     * @param $f3
     */
    public function Update($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_post');

        // 参数验证
        $validator      = new Validator($f3->get('POST'));
        $img_id         = $validator->required('图片ID不能为空')->digits()->min(1)->validate('img_id');
        $img_sort_order = $validator->digits('图片排序必须是数字')->min(0)->validate('img_sort_order');
        $img_desc       = $validator->validate('img_desc');

        if (!$this->validate($validator)) {
            goto out;
        }

        //操作 goods_gallery 记录
        $goodsGalleryService = new GoodsGalleryService();
        $goodsGallery        = $goodsGalleryService->_loadById('goods_gallery', 'img_id = ?', $img_id);

        $goodsGallery->img_desc       = $img_desc;
        $goodsGallery->img_sort_order = $img_sort_order;

        $goodsGallery->save();

        $this->addFlashMessage('图片 [' . $img_id . '] 修改成功');

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goodsGallery->goods_id);

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }


    /**
     * 从网络抓取图片进入相册
     *
     * @param $f3
     */
    public function Fetch($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_post');

        // 参数验证
        $validator = new Validator($f3->get('POST'));
        $goods_id  = $validator->required('商品ID不能为空')->digits()->min(1)->validate('goods_id');
        $imageUrl  = $validator->required('图片地址不能为空')->validate('imageUrl');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 抓取图片，伪装成浏览器防止被某些服务器阻止
        $webInstance = \Web::instance();
        $webInstance->engine('curl');
        $request = $webInstance->request(
            $imageUrl,
            array(
                 'user_agent' =>
                     'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729)'
            )
        );

        if (!$request || (isset($request['http_code']) && 200 != $request['http_code'])) {
            $this->addFlashMessage('抓取失败，请检查你的抓取地址');
            goto out;
        }

        // 把图片保存到 Storage 中
        $cloudStorage = CloudHelper::getCloudModule(CloudHelper::CLOUD_MODULE_STORAGE);

        // 图片文件先保存到临时文件中
        $tempSrcFilePath = $cloudStorage->getTempFilePath();
        file_put_contents($tempSrcFilePath, $request['body']);

        // 上传目录
        $dataPathRoot         = $f3->get('sysConfig[data_path_root]');
        $saveFilePathRelative = 'upload/image/' . date("Y/m/d") . '/' . date("YmdHis") . '_'
            . rand(1, 10000) . strtolower(strrchr($imageUrl, '.'));

        // 文件上传到 Storage
        if (!$cloudStorage->moveFileToStorage($dataPathRoot, $saveFilePathRelative, $tempSrcFilePath)) {
            $this->addFlashMessage('保存文件到存储失败，失败');
            goto out;
        }
        @unlink($tempSrcFilePath);

        // 保存 goods_gallery 记录
        $imageOriginalFileRelativeName = $saveFilePathRelative;
        $pathInfoArray                 = pathinfo($imageOriginalFileRelativeName);

        //生成头图
        $imageFileRelativeName =
            $pathInfoArray['dirname'] . '/' . $pathInfoArray['filename'] . '_'
            . $f3->get('sysConfig[image_width]') . 'x' . $f3->get('sysConfig[image_height]') . '.jpg';

        StorageImageHelper::resizeImage(
            $dataPathRoot,
            $imageOriginalFileRelativeName,
            $imageFileRelativeName,
            $f3->get('sysConfig[image_width]'),
            $f3->get('sysConfig[image_height]')
        );

        //生成缩略图
        $imageThumbFileRelativeName =
            $pathInfoArray['dirname'] . '/' . $pathInfoArray['filename'] . '_'
            . $f3->get('sysConfig[image_thumb_width]') . 'x' . $f3->get('sysConfig[image_thumb_height]') . '.jpg';

        StorageImageHelper::resizeImage(
            $dataPathRoot,
            $imageOriginalFileRelativeName,
            $imageThumbFileRelativeName,
            $f3->get('sysConfig[image_thumb_width]'),
            $f3->get('sysConfig[image_thumb_height]')
        );

        //保存 goods_gallery 记录
        $goodsGalleryService = new GoodsGalleryService();

        // ID 为0，返回一个新建的 dataMapper
        $goodsGallery = $goodsGalleryService->_loadById('goods_gallery', 'img_id=?', 0);

        $goodsGallery->goods_id     = $goods_id;
        $goodsGallery->img_desc     = '网络下载图片';
        $goodsGallery->img_original = $imageOriginalFileRelativeName;
        $goodsGallery->img_url      = $imageFileRelativeName;
        $goodsGallery->thumb_url    = $imageThumbFileRelativeName;

        $goodsGallery->save();

        $this->addFlashMessage('抓取图片成功');

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goodsGallery->goods_id);

        out:
        // 释放资源
        unset($request);
        unset($webInstance);
        RouteHelper::reRoute($this, RouteHelper::makeUrl('/Goods/Edit/Gallery', array('goods_id' => $goods_id), true));
        return; // 成功从这里返回

        out_fail:
        RouteHelper::reRoute($this, '/Goods/Search');
    }

}
