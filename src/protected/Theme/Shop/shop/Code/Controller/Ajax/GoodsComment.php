<?php

/**
 * @author QiangYu
 *
 * 输出商品的用户评论 html
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\BaseService;

class GoodsComment extends \Controller\BaseController
{

    private function preparePage($goods_id, $pageNo)
    {
        global $smarty;

        // 设置缺省值
        $pageNo   = (isset($pageNo) && $pageNo > 0) ? $pageNo : 0;
        $pageSize = 10; // 每页显示 10 个

        // 查询条件
        $condArray   = array();
        $condArray[] = array('goods_id = ?', $goods_id);
        $condArray[] = array('is_show = 1');

        $baseService = new BaseService();

        $totalCount = $baseService->_countArray('goods_comment', $condArray);
        if ($totalCount <= 0) { // 没数据，可以直接退出了
            return;
        }

        // 页数超过最大值
        if ($pageNo * $pageSize >= $totalCount) {
            return;
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

        // 给 smarty 赋值
        $smarty->assign(
            'currentUrl',
            RouteHelper::makeUrl('/Ajax/GoodsComment', array('goods_id' => $goods_id))
        );
        $smarty->assign('totalCount', $totalCount);
        $smarty->assign('pageNo', $pageNo);
        $smarty->assign('pageSize', $pageSize);
        $smarty->assign('goodsCommentArray', $goodsCommentArray);
    }

    /**
     * 取得一个商品评论的页面
     *
     * @param int $goods_id
     * @param int $pageNo
     *
     * @return string
     */
    public function fetchPage($goods_id, $pageNo)
    {
        global $smarty;

        $goods_id = abs(intval($goods_id));
        $pageNo   = abs(intval($pageNo));

        if ($goods_id <= 0) {
            goto out_fail;
        }

        // 生成 smarty 的缓存 id
        $smartyCacheId = 'Goods|' . $goods_id . '|AjaxGoodsComment_' . $pageNo;

        // 开启并设置 smarty 缓存时间
        enableSmartyCache(true, bzf_get_option_value('smarty_cache_time_goods_view'));

        if ($smarty->isCached('ajax_goodscomment.tpl', $smartyCacheId)) {
            goto out_return;
        }

        $this->preparePage($goods_id, $pageNo);

        out_return:
        return $smarty->fetch('ajax_goodscomment.tpl', $smartyCacheId);

        out_fail:
        return '';
    }

    public function get($f3)
    {
        global $smarty;

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('GET'));
        $goods_id  = $validator->required('商品id不能为空')->digits('商品id非法')->min(1, true, '商品id非法')->validate('goods_id');
        $pageNo    = $validator->digits()->min(0)->validate('pageNo');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 生成 smarty 的缓存 id
        $smartyCacheId = 'Goods|' . $goods_id . '|AjaxGoodsComment_' . $pageNo;

        // 开启并设置 smarty 缓存时间
        enableSmartyCache(true, bzf_get_option_value('smarty_cache_time_goods_view'));

        if ($smarty->isCached('ajax_goodscomment.tpl', $smartyCacheId)) {
            goto out_display;
        }

        $this->preparePage($goods_id, $pageNo);

        out_display:
        $f3->expire(600); // 让客户端缓存 10 分钟
        $smarty->display('ajax_goodscomment.tpl', $smartyCacheId);
        return;

        out_fail:
        // output nothing
        return;
    }

}