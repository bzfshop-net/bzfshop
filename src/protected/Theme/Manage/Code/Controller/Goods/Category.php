<?php

/**
 * @author QiangYu
 *
 * 商品分类操作
 *
 * */

namespace Controller\Goods;

use Core\Cache\ClearHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Category as GoodsCategoryService;

class Category extends \Controller\AuthController
{
    private function getCategoryFlatArray($goodsCategoryTreeArray, &$goodsCategoryFlatArray)
    {

        foreach ($goodsCategoryTreeArray as & $goodsCategoryItem) {
            $goodsCategoryFlatArray[] = & $goodsCategoryItem;
            if (!empty($goodsCategoryItem['child_list'])) {
                $this->getCategoryFlatArray($goodsCategoryItem['child_list'], $goodsCategoryFlatArray);
            }
            unset($goodsCategoryItem['child_list']);
        }
    }

    public function get($f3)
    {
        global $smarty;

        $goodsCategoryService = new GoodsCategoryService();

        $goodsCategoryTreeArray = $goodsCategoryService->fetchCategoryTreeArray(0, true);

        if (empty($goodsCategoryTreeArray)) {
            goto out_display;
        }

        $goodsCategoryFlatArray = array();
        $this->getCategoryFlatArray($goodsCategoryTreeArray, $goodsCategoryFlatArray);
        $smarty->assign('goodsCategoryFlatArray', $goodsCategoryFlatArray);

        $categoryGoodsCountArray = $goodsCategoryService->calcCategoryGoodsCount();
        $categoryIdToGoodsCountArray = array();
        foreach ($categoryGoodsCountArray as $categoryItem) {
            $categoryIdToGoodsCountArray[$categoryItem['cat_id']] = $categoryItem['goods_count'];
        }
        $smarty->assign('categoryIdToGoodsCountArray', $categoryIdToGoodsCountArray);

        out_display:
        $smarty->display('goods_category.tpl');
    }

    /**
     * 更新或者新建一个分类
     *
     * @param $f3
     */
    public function Edit($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_category_edit');

        // 参数验证
        $validator = new Validator($f3->get('POST'));
        $meta_id = $validator->digits()->validate('meta_id');
        $meta_id = $meta_id ? : 0;

        $meta_name = $validator->validate('meta_name');
        $parent_meta_id = $validator->digits()->validate('parent_meta_id');
        $meta_sort_order = $validator->digits()->validate('meta_sort_order');
        $meta_status = $validator->digits()->validate('meta_status');
        // 筛选属性
        $filterTypeIdArray = $validator->validate('filterTypeIdArray');
        $filterAttrItemIdArray = $validator->validate('filterAttrItemIdArray');

        if (!$this->validate($validator)) {
            goto out;
        }

        if ($parent_meta_id > 0 && $parent_meta_id == $meta_id) {
            $this->addFlashMessage('父分类不能指向自己');
            goto out;
        }

        // 构造筛选属性结构
        $filterArray = array();
        $count = min(count($filterTypeIdArray), count($filterAttrItemIdArray));
        for ($index = 0; $index < $count; $index++) {
            $typeId = abs(intval($filterTypeIdArray[$index]));
            $attrItemId = abs(intval($filterAttrItemIdArray[$index]));
            if ($typeId <= 0 || $attrItemId <= 0) {
                // 非法值跳过
                continue;
            }
            $filterArray[] = array('typeId' => $typeId, 'attrItemId' => $attrItemId);
        }

        $meta_data = array('filterArray' => $filterArray);

        $goodsCategoryService = new GoodsCategoryService();
        $goodsCategoryService->saveCategoryById(
            $meta_id,
            $parent_meta_id,
            $meta_name,
            null,
            json_encode($meta_data),
            $meta_sort_order,
            $meta_status
        );

        // 清除商品分类的缓存
        ClearHelper::clearGoodsCategory();

        $this->addFlashMessage('商品分类保存成功');

        out:
        RouteHelper::reRoute($this, '/Goods/Category');
    }

    /**
     * 删除一个分类
     *
     * @param $f3
     */
    public function Remove($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_category_edit');

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id = $validator->required()->digits()->min(1)->validate('meta_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        $goodsCategoryService = new GoodsCategoryService();
        $category = $goodsCategoryService->loadCategoryById($meta_id);
        if ($category->isEmpty()) {
            $this->addFlashMessage('分类不存在');
            goto out;
        }

        // 检查当前分类是否存在子分类
        $childArray = $goodsCategoryService->fetchCategoryArray($category['meta_id'], true);
        if (!empty($childArray)) {
            $this->addFlashMessage('当前分类有子分类，不能删除');
            goto out;
        }

        // 检查当前分类是否有商品
        $categoryGoodsCountArray = $goodsCategoryService->calcCategoryGoodsCount();
        foreach ($categoryGoodsCountArray as $categoryItem) {
            if ($meta_id == $categoryItem['cat_id'] && $categoryItem['goods_count'] > 0) {
                $this->addFlashMessage('当前分类有商品，不能删除');
                goto out;
            }
        }

        // 删除分类
        $category->erase();

        // 清除商品分类的缓存
        ClearHelper::clearGoodsCategory();

        $this->addFlashMessage('分类删除成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    /**
     * 把商品从一个分类转移到另外一个分类
     * @param $f3
     */
    public function TransferGoods($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_category_edit');

        // 参数验证
        $validator = new Validator($f3->get('POST'));
        $meta_id = $validator->required()->digits()->min(1)->validate('meta_id');
        $target_meta_id = $validator->required('必须选择一个目标分类')->digits()->min(1)->validate('target_meta_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        if ($meta_id == $target_meta_id) {
            $this->addFlashMessage('目标分类不能是自己');
            goto out;
        }

        $goodsCategoryService = new GoodsCategoryService();
        $goodsCategoryService->transferGoodsToNewCategory($meta_id, $target_meta_id);

        $this->addFlashMessage('商品转移成功');
        $this->addFlashMessage('注意：商品转移只是当前分类商品，不包括子分类的商品');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }
}
