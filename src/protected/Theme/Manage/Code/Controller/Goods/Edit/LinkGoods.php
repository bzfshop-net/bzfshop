<?php

/**
 * @author QiangYu
 *
 * 商品的操作日志显示
 *
 * */

namespace Controller\Goods\Edit;

use Core\Cache\ClearHelper;
use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Goods\Log as GoodsLogService;

class LinkGoods extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_get');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));

        $goods_id = $validator->required('商品ID不能为空')->digits()->min(1)->validate('goods_id');

        $smarty->display('goods_edit_linkgoods.tpl');
        return;

        out_fail:
        RouteHelper::reRoute($this, '/Goods/Search');
    }

    /**
     * 列出我关联了哪些商品
     *
     * @param $f3
     */
    public function ajaxListLinkGoods($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_get', true);

        // 首先做参数验证
        $validator    = new Validator($f3->get('GET'));
        $errorMessage = '';

        $goods_id = $validator->required()->digits()->min(1)->validate('goods_id');

        if (!$this->validate($validator)) {
            $errorMessage = implode('|', $this->flashMessageArray);
            goto out_fail;
        }

        $goodsBasicService = new GoodsBasicService();
        $linkGoodsArray    = $goodsBasicService->fetchLinkGoodsArray($goods_id);

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, $linkGoodsArray);
        return;

        out_fail: // 失败，返回出错信息
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }

    /**
     * 列出哪些商品关联了我
     *
     * @param $f3
     */
    public function ajaxListLinkByGoods($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_get', true);

        // 首先做参数验证
        $validator    = new Validator($f3->get('GET'));
        $errorMessage = '';

        $link_goods_id = $validator->required()->digits()->min(1)->validate('link_goods_id');

        if (!$this->validate($validator)) {
            $errorMessage = implode('|', $this->flashMessageArray);
            goto out_fail;
        }

        $goodsBasicService = new GoodsBasicService();
        $linkGoodsArray    = $goodsBasicService->fetchLinkByGoodsArray($link_goods_id);

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, $linkGoodsArray);
        return;

        out_fail: // 失败，返回出错信息
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }

    /**
     * 删除商品关联
     *
     * @param $f3
     */
    public function ajaxRemoveLink($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_post', true);

        // 首先做参数验证
        $validator    = new Validator($f3->get('GET'));
        $errorMessage = '';

        $link_id = $validator->required()->digits()->min(1)->validate('link_id');

        if (!$this->validate($validator)) {
            $errorMessage = implode('|', $this->flashMessageArray);
            goto out_fail;
        }

        $dataMapper = new DataMapper('link_goods');
        $dataMapper->loadOne(array('link_id = ?', $link_id));

        if ($dataMapper->isEmpty()) {
            $errorMessage = '关联记录不存在';
            goto out_fail;
        }

        $goods_id        = $dataMapper->goods_id;
        $goodsLogContent = $dataMapper->link_goods_id;

        // 删除记录
        $dataMapper->erase();

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goods_id);

        // 记录商品编辑日志
        $authAdminUser   = AuthHelper::getAuthUser();
        $goodsLogService = new GoodsLogService();
        $goodsLogService->addGoodsLog(
            $goods_id,
            $authAdminUser['user_id'],
            $authAdminUser['user_name'],
            '取消商品关联',
            $goodsLogContent
        );

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, null);
        return;

        out_fail: // 失败，返回出错信息
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }

    /**
     * 增加商品关联
     *
     * @param $f3
     */
    public function ajaxAddLink($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_post', true);

        // 首先做参数验证
        $validator    = new Validator($f3->get('GET'));
        $errorMessage = '';

        $goods_id      = $validator->required()->digits()->min(1)->validate('goods_id');
        $link_goods_id = $validator->required()->digits()->min(1)->validate('link_goods_id');

        if (!$this->validate($validator)) {
            $errorMessage = implode('|', $this->flashMessageArray);
            goto out_fail;
        }

        $dataMapper = new DataMapper('link_goods');
        $dataMapper->loadOne(array('goods_id = ? and link_goods_id = ?', $goods_id, $link_goods_id));

        // 已经关联了，不要重复关联
        if (!$dataMapper->isEmpty()) {
            goto out;
        }

        $authAdminUser = AuthHelper::getAuthUser();

        // 添加记录
        $dataMapper->goods_id      = $goods_id;
        $dataMapper->link_goods_id = $link_goods_id;
        $dataMapper->admin_id      = $authAdminUser['user_id'];
        $dataMapper->save();

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goods_id);

        // 记录商品编辑日志
        $goodsLogService = new GoodsLogService();
        $goodsLogService->addGoodsLog(
            $goods_id,
            $authAdminUser['user_id'],
            $authAdminUser['user_name'],
            '添加商品关联',
            $link_goods_id
        );

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, null);
        return;

        out_fail: // 失败，返回出错信息
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }
}
