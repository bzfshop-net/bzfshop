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

        // 新建的品牌，reRoute 到编辑页面
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

}
