<?php

/**
 * @author QiangYu
 *
 * 商品品牌操作
 *
 * */

namespace Controller\Goods;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Brand as GoodsBrandService;
use Core\Service\User\AdminLog;

class Brand extends \Controller\AuthController
{

    public function ListBrand($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_brand_listbrand');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo = $validator->digits()->min(0)->validate('pageNo');
        $pageSize = $validator->digits()->min(0)->validate('pageSize');

        // 查询条件
        $formQuery = array();
        $formQuery['brand_name'] = $validator->validate('brand_name');
        $formQuery['brand_desc'] = $validator->validate('brand_desc');
        $formQuery['is_custom'] = $validator->filter('ValidatorIntValue')->validate('is_custom');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 设置缺省值
        $pageNo = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        // 查询条件
        $condArray = QueryBuilder::buildQueryCondArray($formQuery);

        $goodsBrandService = new GoodsBrandService();

        $totalCount = $goodsBrandService->countBrandArray($condArray);
        if ($totalCount <= 0) { // 没用户，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Goods/Brand/ListBrand');
        }

        // 查询数据
        $goodsBrandArray = $goodsBrandService->fetchBrandArray(
            $condArray,
            $pageNo * $pageSize,
            $pageSize
        );

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('goodsBrandArray', $goodsBrandArray);

        out_display:
        $smarty->display('goods_brand_listbrand.tpl');
    }

    public function Edit($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_brand_listbrand');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $brand_id = $validator->digits()->min(1)->validate('brand_id');
        if (!$brand_id) {
            $brand_id = 0;
        }

        $goodsBrandService = new GoodsBrandService();
        $goodsBrand = $goodsBrandService->loadBrandById($brand_id);

        if (!$f3->get('POST')) {
            // 没有 post ，只是普通的显示
            goto out_display;
        }

        unset($validator);
        $validator = new Validator($f3->get('POST'));
        $goodsBrand->brand_name = $validator->required()->validate('brand_name');
        $goodsBrand->brand_desc = $validator->required()->validate('brand_desc');
        $goodsBrand->brand_logo = $validator->validate('brand_logo');
        $goodsBrand->is_custom = $validator->digits()->filter('ValidatorIntValue')->validate('is_custom');
        $goodsBrand->custom_page = $f3->get('POST[custom_page]');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        $goodsBrand->save();

        if (0 == $brand_id) {
            $this->addFlashMessage('新建商品品牌成功');
        } else {
            $this->addFlashMessage('更新商品品牌成功');
        }

        // 记录管理员日志
        AdminLog::logAdminOperate('goods.brand.edit', '编辑品牌', $goodsBrand->brand_name);

        out_display:

        // 新建的品牌，reRoute 到编辑页面
        if (!$brand_id) {
            RouteHelper::reRoute(
                $this,
                RouteHelper::makeUrl('/Goods/Brand/Edit', array('brand_id' => $goodsBrand->brand_id), true)
            );
        }

        //给 smarty 模板赋值
        $smarty->assign($goodsBrand->toArray());
        $smarty->display('goods_brand_edit.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Goods/Brand/ListBrand');
    }

    public function Create($f3)
    {
        // 新建商品类型，权限检查
        $this->requirePrivilege('manage_goods_brand_listbrand');

        global $smarty;
        $smarty->display('goods_brand_edit.tpl');
    }

}
