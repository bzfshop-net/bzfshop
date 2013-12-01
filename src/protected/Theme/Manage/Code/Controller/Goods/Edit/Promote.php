<?php

/**
 * @author QiangYu
 *
 * 商品推广信息编辑操作
 *
 * */

namespace Controller\Goods\Edit;

use Core\Cache\ClearHelper;
use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Money;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Goods\Log as GoodsLogService;

class Promote extends \Controller\AuthController
{
    static $goodsLogDesc = '推广渠道';

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

        // 取得商品信息
        $goodsBasicService = new GoodsBasicService();
        $goodsPromote      = $goodsBasicService->loadGoodsPromoteByGoodsId($goods_id);

        // 显示商品推广渠道信息
        $smarty->assign('goods_promote', $goodsPromote);

        out_display:
        $smarty->display('goods_edit_promote.tpl');
        return;

        out_fail:
        RouteHelper::reRoute($this, '/Goods/Search');
    }


    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_post');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $goods_id  = $validator->required('商品ID不能为空')->digits()->min(1)->validate('goods_id');

        if (!$this->validate($validator)) {
            goto out_fail_list_goods;
        }
        unset($validator);

        // 用户提交的商品信息做验证
        $goodsPromoteInfo = $f3->get('POST.goods_promote');

        if (empty($goodsPromoteInfo)) {
            goto out_fail_validate;
        }

        //安全性处理
        unset($goodsPromoteInfo['promote_id']);
        $goodsPromoteInfo['goods_id'] = $goods_id;

        // 写入到数据库
        $goodsBasicService = new GoodsBasicService();
        $goodsPromote      = $goodsBasicService->loadGoodsPromoteByGoodsId($goods_id);
        $goodsPromote->copyFrom($goodsPromoteInfo);
        $goodsPromote->save();

        // 记录商品编辑日志
        $goodsLogContent =
            '360分类：' . $goodsPromote['360tuan_category'] . ',' . $goodsPromote['360tuan_category_end'] . "\n"
            . "360排序：" . $goodsPromote['360tuan_sort_order'];

        $authAdminUser   = AuthHelper::getAuthUser();
        $goodsLogService = new GoodsLogService();
        $goodsLogService->addGoodsLog(
            $goods_id,
            $authAdminUser['user_id'],
            $authAdminUser['user_name'],
            static::$goodsLogDesc,
            $goodsLogContent
        );

        // 成功，显示商品详情
        $this->addFlashMessage('商品推广渠道保存成功');

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goods_id);

        RouteHelper::reRoute($this, RouteHelper::makeUrl('/Goods/Edit/Promote', array('goods_id' => $goods_id), true));
        return;

        // 参数验证失败
        out_fail_validate:
        $smarty->display('goods_edit_promote.tpl');
        return;

        out_fail_list_goods:
        RouteHelper::reRoute($this, '/Goods/Search');
    }

}
