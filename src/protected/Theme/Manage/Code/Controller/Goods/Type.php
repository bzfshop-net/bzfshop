<?php

/**
 * @author QiangYu
 *
 * 商品类型操作
 *
 * */

namespace Controller\Goods;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Type as GoodsTypeService;
use Core\Service\User\AdminLog;

class Type extends \Controller\AuthController
{

    public function ListType($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_type_listtype');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo = $validator->digits()->min(0)->validate('pageNo');
        $pageSize = $validator->digits()->min(0)->validate('pageSize');

        // 查询条件
        $formQuery = array();
        $formQuery['meta_name'] = $validator->validate('meta_name');
        $formQuery['meta_desc'] = $validator->validate('meta_desc');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 设置缺省值
        $pageNo = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 20;

        // 查询条件
        $condArray = QueryBuilder::buildQueryCondArray($formQuery);

        $goodsTypeService = new GoodsTypeService();

        $totalCount = $goodsTypeService->countGoodsTypeArray($condArray);
        if ($totalCount <= 0) { // 没数据，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Goods/Type/ListType');
        }

        // 查询数据
        $goodsTypeArray = $goodsTypeService->fetchGoodsTypeArray(
            $condArray,
            $pageNo * $pageSize,
            $pageSize
        );

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('goodsTypeArray', $goodsTypeArray);

        out_display:
        $smarty->display('goods_type_listtype.tpl');
    }

    public function Edit($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_type_listtype');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id = $validator->digits()->min(1)->validate('meta_id');
        if (!$meta_id) {
            $meta_id = 0;
        }

        $goodsTypeService = new GoodsTypeService();
        $goodsType = $goodsTypeService->loadGoodsTypeById($meta_id);

        if (!$f3->get('POST')) {
            // 没有 post ，只是普通的显示
            goto out_display;
        }

        unset($validator);
        $validator = new Validator($f3->get('POST'));
        $goodsType->meta_name = $validator->required()->validate('meta_name');
        $goodsType->meta_desc = $validator->required()->validate('meta_desc');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        $goodsType->save();

        if (0 == $meta_id) {
            $this->addFlashMessage('新建商品类型成功');
        } else {
            $this->addFlashMessage('更新商品类型成功');
        }

        // 记录管理员日志
        AdminLog::logAdminOperate('goods.type.edit', '商品类型', $goodsType->meta_name);

        out_display:

        // 新建的，reRoute 到编辑页面
        if (!$meta_id) {
            RouteHelper::reRoute(
                $this,
                RouteHelper::makeUrl('/Goods/Type/Edit', array('meta_id' => $goodsType->meta_id), true)
            );
        }

        //给 smarty 模板赋值
        $smarty->assign($goodsType->toArray());
        $smarty->display('goods_type_edit.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Goods/Type/ListType');
    }

    public function Create($f3)
    {
        // 新建商品类型，权限检查
        $this->requirePrivilege('manage_goods_type_listtype');

        global $smarty;
        $smarty->display('goods_type_edit.tpl');
    }

    public function ListAttr($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_type_listtype');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id = $validator->required()->digits()->min(1)->validate('typeId');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        $goodsTypeService = new GoodsTypeService();

        $goodsType = $goodsTypeService->loadGoodsTypeById($meta_id);
        if ($goodsType->isEmpty()) {
            $this->addFlashMessage('商品类型[' . $meta_id . ']非法');
            goto out_fail;
        }

        // 取得属性树形结构
        $goodsAttrTreeTable = $goodsTypeService->fetchGoodsTypeAttrTreeTable($meta_id);

        // 属性的显示
        foreach ($goodsAttrTreeTable as &$goodsAttrTreeItem) {
            if (GoodsTypeService::META_TYPE_GOODS_TYPE_ATTR_ITEM == $goodsAttrTreeItem['meta_type']) {
                $goodsAttrTreeItem['attr_type_desc'] = @GoodsTypeService::$attrItemTypeDesc[$goodsAttrTreeItem['meta_ename']];
                if (!empty($goodsAttrTreeItem['meta_data'])) {
                    $goodsAttrTreeItem['meta_data'] = str_replace(',', "\n", $goodsAttrTreeItem['meta_data']);
                }
            }
        }

        // 给模板赋值
        $smarty->assign('goodsType', $goodsType);
        $smarty->assign('goodsAttrTreeTable', $goodsAttrTreeTable);

        out_display:
        $smarty->display('goods_type_listattr.tpl');
        return;

        out_fail:
        RouteHelper::reRoute($this, '/Goods/Type/ListType');
    }

    public function AttrGroupEdit($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_type_listtype');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id = $validator->digits()->min(1)->validate('meta_id');
        if (!$meta_id) {
            $meta_id = 0;
        }

        $goodsTypeService = new GoodsTypeService();
        $goodsAttrGroup = $goodsTypeService->loadGoodsTypeAttrGroupById($meta_id);

        if (!$f3->get('POST')) {
            // 没有 post ，只是普通的显示
            goto out_display;
        }

        unset($validator);
        $validator = new Validator($f3->get('POST'));

        if (0 === $meta_id) {
            // 新建的组
            $goodsAttrGroup->parent_meta_id = $validator->required()->validate('typeId');
        }

        $goodsAttrGroup->meta_name = $validator->required()->validate('meta_name');
        $goodsAttrGroup->meta_desc = $validator->required()->validate('meta_desc');
        $goodsAttrGroup->meta_sort_order = $validator->digits()->validate('meta_sort_order');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        $goodsAttrGroup->save();

        if (0 === $meta_id) {
            $this->addFlashMessage('新建商品属性组成功');
        } else {
            $this->addFlashMessage('更新商品属性组成功');
        }

        // 记录管理员日志
        AdminLog::logAdminOperate('goods.type.attrgroup.edit', '商品属性组', $goodsAttrGroup->meta_name);

        out_display:

        // 新建的，reRoute 到编辑页面
        if (!$meta_id) {
            RouteHelper::reRoute(
                $this,
                RouteHelper::makeUrl('/Goods/Type/AttrGroupEdit', array('meta_id' => $goodsAttrGroup->meta_id), true)
            );
        }

        //给 smarty 模板赋值
        $smarty->assign('typeId', $goodsAttrGroup->parent_meta_id);
        $smarty->assign($goodsAttrGroup->toArray());
        $smarty->display('goods_type_attrgroupedit.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Goods/Type/ListType');
    }

    public function AttrGroupCreate($f3)
    {
        // 新建商品类型，权限检查
        $this->requirePrivilege('manage_goods_type_listtype');
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $typeId = $validator->required()->digits()->min(1)->validate('typeId');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        global $smarty;
        $smarty->display('goods_type_attrgroupedit.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Goods/Type/ListType');
    }

    public function AttrGroupRemove($f3)
    {

        // 权限检查
        $this->requirePrivilege('manage_goods_type_listtype');

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id = $validator->required()->digits()->min(1)->validate('meta_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        $goodsTypeService = new GoodsTypeService();
        $goodsTypeService->removeGoodsTypeAttrGroup($meta_id);

        $this->addFlashMessage('成功删除属性组[' . $meta_id . ']');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    public function AttrItemEdit($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_type_listtype');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id = $validator->digits()->min(1)->validate('meta_id');
        if (!$meta_id) {
            $meta_id = 0;
        }

        $goodsTypeService = new GoodsTypeService();
        $goodsAttrItem = $goodsTypeService->loadGoodsTypeAttrItemById($meta_id);

        if (!$f3->get('POST')) {
            // 没有 post ，只是普通的显示
            goto out_display;
        }

        unset($validator);
        $validator = new Validator($f3->get('POST'));

        if (0 === $meta_id) {
            // 新建的组
            $goodsAttrItem->parent_meta_id = $validator->required()->validate('typeId');
        }

        // 属性组
        $goodsAttrItem->meta_key = $validator->digits()->validate('meta_key');
        $goodsAttrItem->meta_name = $validator->required()->validate('meta_name');
        $goodsAttrItem->meta_desc = $validator->required()->validate('meta_desc');
        $goodsAttrItem->meta_sort_order = $validator->digits()->validate('meta_sort_order');
        // 属性类型，单选、单行输入、多行输入
        $goodsAttrItem->meta_ename = $validator->required()->validate('meta_ename');
        // 选项列表，逗号分隔
        $goodsAttrItem->meta_data = $validator->validate('meta_data');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        $goodsAttrItem->save();

        if (0 === $meta_id) {
            $this->addFlashMessage('新建商品属性成功');
        } else {
            $this->addFlashMessage('更新商品属性成功');
        }

        // 记录管理员日志
        AdminLog::logAdminOperate('goods.type.attritem.edit', '商品属性', $goodsAttrItem->meta_name);

        out_display:

        // 新建的，reRoute 到编辑页面
        if (!$meta_id) {
            RouteHelper::reRoute(
                $this,
                RouteHelper::makeUrl('/Goods/Type/AttrItemEdit', array('meta_id' => $goodsAttrItem->meta_id), true)
            );
        }

        //给 smarty 模板赋值
        $smarty->assign('typeId', $goodsAttrItem->parent_meta_id);
        $smarty->assign($goodsAttrItem->toArray());
        $smarty->display('goods_type_attritemedit.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Goods/Type/ListType');
    }

    public function AttrItemCreate($f3)
    {
        // 新建商品类型，权限检查
        $this->requirePrivilege('manage_goods_type_listtype');
        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $typeId = $validator->required()->digits()->min(1)->validate('typeId');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        global $smarty;
        $smarty->display('goods_type_attritemedit.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Goods/Type/ListType');
    }

    public function AttrItemRemove($f3)
    {

        // 权限检查
        $this->requirePrivilege('manage_goods_type_listtype');

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id = $validator->required()->digits()->min(1)->validate('meta_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        $goodsTypeService = new GoodsTypeService();
        $goodsTypeService->removeGoodsTypeAttrItem($meta_id);

        $this->addFlashMessage('成功删除属性[' . $meta_id . ']');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

}
