<?php

/**
 * @author QiangYu
 *
 * 商品的 ajax 操作
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Search\SearchHelper;
use Core\Service\Goods\Category as CategoryService;
use Core\Service\Goods\Gallery as GoodsGalleryService;

class Goods extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    private $searchFieldSelector = 'goods_id, goods_name, goods_name_short';

    /**
     *
     * 根据某些查询条件取得商品的列表
     *
     * @param $f3
     */
    public function Search($f3)
    {
        // 参数验证
        $validator    = new Validator($f3->get('GET'));
        $errorMessage = '';

        $searchFormQuery                 = array();
        $searchFormQuery['is_on_sale']   =
            $validator->digits()->min(0)->filter('ValidatorIntValue')->validate('is_on_sale');
        $searchFormQuery['goods_id']     =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('goods_id');
        $searchFormQuery['suppliers_id'] =
            $validator->digits()->min(1)->filter('ValidatorIntValue')->validate('suppliers_id');
        $searchFormQuery['goods_name']   = $validator->validate('goods_name');
        $searchFormQuery['cat_id']       =
            $validator->digits()->min(0)->filter('ValidatorIntValue')->validate('cat_id');

        if (!$this->validate($validator)) {
            $errorMessage = implode('|', $this->flashMessageArray);
            goto out_fail;
        }

        // 建立查询条件
        $searchParamArray = QueryBuilder::buildSearchParamArray($searchFormQuery);

        // 商品列表
        $goodsArray = SearchHelper::search(
            SearchHelper::Module_Goods,
            $this->searchFieldSelector,
            $searchParamArray,
            array(array('goods_id', 'desc')),
            0,
            25 // 一次最多取 25 条记录，多了也没用
        );

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, $goodsArray);
        return;

        out_fail: // 失败，返回出错信息
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }

    /**
     * 根据 goods_id 得到一个商品的图片集
     *
     * @param $f3
     */
    public function GalleryThumb($f3)
    {

        // 参数验证
        $validator    = new Validator($f3->get('GET'));
        $errorMessage = '';

        $goods_id =
            $validator->required()->digits()->min(1)->filter('ValidatorIntValue')->validate('goods_id');

        if (!$this->validate($validator)) {
            $errorMessage = implode('|', $this->flashMessageArray);
            goto out_fail;
        }

        $goodsGalleryService = new GoodsGalleryService();
        $galleryArray        = $goodsGalleryService->fetchGoodsGalleryArrayByGoodsId($goods_id);

        $thumImageList = array();
        foreach ($galleryArray as $galleryItem) {
            $thumImageList[] = array(
                'img_id'    => $galleryItem['img_id'],
                'thumb_url' => RouteHelper::makeImageUrl($galleryItem['thumb_url'])
            );
        }

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, $thumImageList);
        return;

        out_fail: // 失败，返回出错信息
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }

    /**
     * 列出整个商品分类树
     *
     * @param $f3
     */
    public function ListCategoryTree($f3)
    {
        // 检查缓存
        $cacheKey = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);

        if ($f3->get('GET[nocache]')) {
            goto nocache;
        }

        $categoryTreeDisplayArray = $f3->get($cacheKey);
        if (!empty($categoryTreeDisplayArray)) {
            goto out;
        }

        nocache: // 没有缓存数据

        $categoryService   = new CategoryService();
        $categoryTreeArray = $categoryService->fetchCategoryTreeArray(0, true);

        //构造显示数组
        $categoryTreeDisplayArray = array();

        function buildTreeDisplay(&$categoryDisplayArray, $categoryArray, $namePrefix)
        {
            foreach ($categoryArray as $categoryItem) {

                $categoryItem['meta_name'] =
                    $namePrefix . $categoryItem['meta_name'] . ($categoryItem['meta_status'] ? '' : '(不显示)');
                $categoryDisplayArray[]    = $categoryItem;

                if (isset($categoryItem['child_list'])) {
                    buildTreeDisplay(
                        $categoryDisplayArray,
                        $categoryItem['child_list'],
                        $namePrefix . '---------->'
                    );
                }
            }
        }

        buildTreeDisplay($categoryTreeDisplayArray, $categoryTreeArray, '');

        $f3->set($cacheKey, $categoryTreeDisplayArray, 300); //缓存 5 分钟

        out:
        if (!$f3->get('GET[nocache]')) {
            $f3->expire(60); // 客户端缓存 1 分钟
        }
        Ajax::header();
        echo Ajax::buildResult(null, null, $categoryTreeDisplayArray);
    }

}
