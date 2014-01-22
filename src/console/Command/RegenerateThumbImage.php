<?php

use Core\Helper\Image\StorageImage as StorageImageHelper;
use Core\Service\BaseService;
use Core\Service\Goods\Gallery as GoodsGalleryService;

class RegenerateThumbImage implements \Clip\Command
{

    public function run(array $params)
    {
        global $f3;

        // 每次处理多少条记录
        $batchProcessCount = 100;

        // 图片所在的根目录
        $dataPathRoot       = $f3->get('sysConfig[data_path_root]');
        $image_thumb_width  = $f3->get('sysConfig[image_thumb_width]');
        $image_thumb_height = $f3->get('sysConfig[image_thumb_height]');

        $goodsGalleryService = new GoodsGalleryService();

        $baseService            = new BaseService();
        $totalGoodsGalleryCount = $baseService->_countArray('goods_gallery', null);

        // 记录处理开始
        for ($offset = 0; $offset < $totalGoodsGalleryCount; $offset += $batchProcessCount) {
            $goodsGalleryArray = $baseService->_fetchArray(
                'goods_gallery',
                '*',
                null,
                array('order' => 'img_id asc'),
                $offset,
                $batchProcessCount
            );
            foreach ($goodsGalleryArray as $goodsGalleryItem) {

                if (!is_file($dataPathRoot . '/' . $goodsGalleryItem['img_original'])) {
                    continue; // 文件不存在，不处理
                }

                $pathInfoArray = pathinfo($goodsGalleryItem['img_original']);

                //生成缩略图
                $imageThumbFileRelativeName =
                    $pathInfoArray['dirname'] . '/' . $pathInfoArray['filename'] . '_'
                    . $image_thumb_width . 'x' . $image_thumb_height . '.jpg';

                //重新生存缩略图
                printLog('Re-generate File :' . $imageThumbFileRelativeName);
                StorageImageHelper::resizeImage(
                    $dataPathRoot,
                    $goodsGalleryItem['img_original'],
                    $imageThumbFileRelativeName,
                    $image_thumb_width,
                    $image_thumb_height
                );

                // 更新 goods_gallery 设置
                printLog('update goods_gallery img_id [' . $goodsGalleryItem['img_id'] . ']');
                $goodsGallery = $goodsGalleryService->loadGoodsGalleryById($goodsGalleryItem['img_id']);
                if (!$goodsGallery->isEmpty()) {
                    $goodsGallery->thumb_url = $imageThumbFileRelativeName;
                    $goodsGallery->save();
                }

                // 主动释放资源
                unset($goodsGallery);
                unset($pathInfoArray);
                unset($imageThumbFileRelativeName);
            }

            unset($goodsGalleryArray);
            printLog('re-generate thumb image offset : ' . $offset);
        }

        printLog('re-generate thumb image finished , offset : ' . $offset);
    }

    public function help()
    {
        echo "re-generate all thumb image in goods_gallery table";
    }
}
