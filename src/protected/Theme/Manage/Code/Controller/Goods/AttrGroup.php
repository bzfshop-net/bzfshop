<?php

/**
 * @author QiangYu
 *
 * 商品属性组的操作
 *
 * */

namespace Controller\Goods;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Meta\GoodsAttrGroup as GoodsAttrGroupService;

class AttrGroup extends \Controller\AuthController
{

    public function ListAttrGroup($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_attrgroup_listattrgroup');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 查询条件
        $formQuery              = array();
        $formQuery['meta_name'] = $validator->validate('meta_name');
        $formQuery['meta_desc'] = $validator->validate('meta_desc');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        // 查询条件
        $condArray = QueryBuilder::buildQueryCondArray($formQuery);

        $goodsAttrGroupService = new GoodsAttrGroupService();

        $totalCount = $goodsAttrGroupService->countGoodsAttrGroupArray($condArray);
        if ($totalCount <= 0) { // 没用户，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Goods/AttrGroup/ListAttrGroup');
        }

        // 查询数据
        $goodsAttrGroupArray =
            $goodsAttrGroupService->fetchGoodsAttrGroupArray($condArray, $pageNo * $pageSize, $pageSize);

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('goodsAttrGroupArray', $goodsAttrGroupArray);

        out_display:
        $smarty->display('goods_attrgroup_listattrgroup.tpl');
    }

    public function Edit($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_attrgroup_edit');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id   = $validator->digits()->min(1)->validate('meta_id');
        if (!$meta_id) {
            $meta_id = 0;
        }

        $goodsAttrGroupService = new GoodsAttrGroupService();
        $goodsAttrGroup        = $goodsAttrGroupService->loadMetaById($meta_id);

        if (0 != $meta_id && GoodsAttrGroupService::META_TYPE != $goodsAttrGroup['meta_type']) {
            $this->addFlashMessage('商品类型 ID[' . $meta_id . '] 非法');
            goto out_fail;
        }

        if (!$f3->get('POST')) {
            // 没有 post ，只是普通的显示
            goto out_display;
        }

        unset($validator);
        $validator                 = new Validator($f3->get('POST'));
        $goodsAttrGroup->meta_type = GoodsAttrGroupService::META_TYPE;
        $goodsAttrGroup->meta_name = $validator->required()->validate('meta_name');
        $goodsAttrGroup->meta_desc = $validator->validate('meta_desc');
        $meta_data                 = $validator->validate('meta_data');

        // 处理 meta_data 数据
        $metaDataArray   = explode("\n", preg_replace('/\r\n/', "\n", $meta_data));
        $meta_data_array = array();
        foreach ($metaDataArray as $metaDataItem) {
            $metaDataItem = trim($metaDataItem);
            if (empty($metaDataItem)) {
                //跳过空行
                continue;
            }
            $meta_data_array[] = $metaDataItem;
        }

        $goodsAttrGroup->meta_data = implode("\n", $meta_data_array);

        if (!$this->validate($validator)) {
            goto out_display;
        }

        if (0 == $meta_id) {
            // 新建商品类型，权限检查
            $this->requirePrivilege('manage_goods_attrgroup_create');
        }

        $goodsAttrGroup->save();

        if (0 == $meta_id) {
            $this->addFlashMessage('新建商品类型成功');
        } else {
            $this->addFlashMessage('更新商品类型成功');
        }

        out_display:
        //给 smarty 模板赋值
        $smarty->assign($goodsAttrGroup->toArray());
        $smarty->display('goods_attrgroup_edit.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Goods/AttrGroup/ListAttrGroup');
    }

    public function Create($f3)
    {
        // 新建商品类型，权限检查
        $this->requirePrivilege('manage_goods_attrgroup_create');

        global $smarty;
        $smarty->display('goods_attrgroup_edit.tpl');
    }

}
