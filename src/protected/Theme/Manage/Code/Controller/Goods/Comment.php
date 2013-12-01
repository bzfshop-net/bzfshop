<?php

/**
 * @author QiangYu
 *
 * 商品属性组的操作
 *
 * */

namespace Controller\Goods;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Money;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Service\BaseService;
use Core\Service\Goods\Comment as GoodsCommentService;

class Comment extends \Controller\AuthController
{

    public function ListComment($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_comment_listcomment');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');
        $pageSize  = $validator->digits()->min(0)->validate('pageSize');

        // 查询条件
        $formQuery                  = array();
        $formQuery['goods_id']      = $validator->filter('ValidatorIntValue')->validate('goods_id');
        $formQuery['is_show']       = $validator->filter('ValidatorIntValue')->validate('is_show');
        $formQuery['admin_user_id'] = $validator->filter('ValidatorIntValue')->validate('admin_user_id');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = (isset($pageSize) && $pageSize > 0) ? $pageSize : 10;

        // 查询条件
        $condArray = QueryBuilder::buildQueryCondArray($formQuery);

        $baseService = new BaseService();

        $totalCount = $baseService->_countArray('goods_comment', $condArray);
        if ($totalCount <= 0) { // 没用户，可以直接退出了
            goto out_display;
        }

        // 页数超过最大值，返回第一页
        if ($pageNo * $pageSize >= $totalCount) {
            RouteHelper::reRoute($this, '/Goods/AttrGroup/ListAttrGroup');
        }

        // 查询数据
        $goodsCommentArray = $baseService->_fetchArray(
            'goods_comment',
            '*',
            $condArray,
            array('order' => 'comment_id desc'),
            $pageNo * $pageSize,
            $pageSize
        );

        // 给模板赋值
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('goodsCommentArray', $goodsCommentArray);

        out_display:
        $smarty->display('goods_comment_listcomment.tpl');
    }

    public function Edit($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_comment_edit');

        global $smarty;

        // 参数验证
        $validator  = new Validator($f3->get('GET'));
        $comment_id = $validator->digits()->min(1)->validate('comment_id');
        if (!$comment_id) {
            $comment_id = 0;
        }

        $goodsCommentService = new GoodsCommentService();
        $goodsComment        = $goodsCommentService->loadGoodsCommentById($comment_id);

        if (!$f3->get('POST')) {
            // 没有 post ，只是普通的显示
            goto out_display;
        }

        // 新建商品评论
        if (0 == $comment_id) {
            $this->requirePrivilege('manage_goods_comment_create');
            $goodsComment->create_time  = Time::gmTime();
            $goodsComment->comment_time = Time::gmTime();
        }

        unset($validator);
        $validator                  = new Validator($f3->get('POST'));
        $goodsComment->goods_id     = $validator->digits()->filter('ValidatorIntValue')->validate('goods_id');
        $goodsComment->goods_price  =
            Money::toStorage($validator->validate('goods_price'));
        $goodsComment->goods_number =
            $validator->required()->digits()->filter('ValidatorIntValue')->validate('goods_number');
        $goodsComment->goods_attr   = $validator->validate('goods_attr');
        $goodsComment->is_show      = $validator->digits()->filter('ValidatorIntValue')->validate('is_show');

        $goodsComment->user_name    = $validator->required()->validate('user_name');
        $goodsComment->comment_time = Time::gmStrToTime($validator->required()->validate('comment_time'));
        $goodsComment->comment      = $validator->validate('comment');
        $goodsComment->comment_rate = $validator->digits()->filter('ValidatorIntValue')->validate('comment_rate');
        $goodsComment->reply        = $validator->validate('reply');

        if (!$this->validate($validator)) {
            goto out_display;
        }

        if (!empty($goodsComment->reply)) {
            $goodsComment->reply_time = Time::gmTime();
        }

        // 更新管理员信息
        $authAdminUser                 = AuthHelper::getAuthUser();
        $goodsComment->admin_user_id   = $authAdminUser['user_id'];
        $goodsComment->admin_user_name = $authAdminUser['user_name'];

        $goodsComment->save();

        if (0 == $comment_id) {
            $this->addFlashMessage('新建商品评论成功');
        } else {
            $this->addFlashMessage('更新商品评论成功');
        }

        out_display:
        //给 smarty 模板赋值
        $smarty->assign($goodsComment->toArray());
        $smarty->display('goods_comment_edit.tpl');
        return;

        out_fail: // 失败从这里退出
        RouteHelper::reRoute($this, '/Goods/Comment/ListComment');
    }

    public function Create($f3)
    {
        // 新建商品类型，权限检查
        $this->requirePrivilege('manage_goods_comment_create');

        global $smarty;
        $smarty->assign('create_time', Time::gmTime());
        $smarty->assign('goods_price', 0);
        $smarty->display('goods_comment_edit.tpl');
    }

}
