<?php

/**
 * @author QiangYu
 *
 * 商品团购信息编辑操作
 *
 * */

namespace Controller\Goods\Edit;

use Core\Cache\ClearHelper;
use Core\Helper\Utility\Money;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Goods as GoodsBasicService;

class Team extends \Controller\AuthController
{

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
        $goodsTeam         = $goodsBasicService->loadGoodsTeamByGoodsId($goods_id);

        // 显示商品
        $smarty->assign('goods_team', $goodsTeam);

        out_display:
        $smarty->display('goods_edit_team.tpl');
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
        $goodsTeam = $f3->get('POST.goods_team');

        if (empty($goodsTeam)) {
            goto out_fail_validate;
        }

        $validator     = new Validator($goodsTeam);
        $goodsTeamInfo = array();

        $goodsTeamInfo['goods_id'] = $goods_id;

        //表单数据验证、过滤
        $goodsTeamInfo['team_enable']          = $validator->digits()->validate('team_enable');
        $goodsTeamInfo['team_title']           = $validator->required('团购标题不能为空')->validate('team_title');
        $goodsTeamInfo['team_seo_keyword']     = $validator->validate('team_seo_keyword');
        $goodsTeamInfo['team_seo_description'] = $validator->validate('team_seo_description');

        $goodsTeamInfo['team_price']      = Money::toStorage($validator->validate('team_price'));
        $goodsTeamInfo['team_sort_order'] = $validator->filter('ValidatorIntValue')->validate('team_sort_order');
        $goodsTeamInfo['team_per_number'] = $validator->filter('ValidatorIntValue')->validate('team_per_number');
        $goodsTeamInfo['team_min_number'] = $validator->filter('ValidatorIntValue')->validate('team_min_number');
        $goodsTeamInfo['team_max_number'] = $validator->filter('ValidatorIntValue')->validate('team_max_number');
        $goodsTeamInfo['team_pre_number'] = $validator->filter('ValidatorIntValue')->validate('team_pre_number');

        //单独解析时间
        $team_begin_time_str = $validator->validate('team_begin_time_str');
        $teamBeginTime       = Time::gmStrToTime($team_begin_time_str);
        if ($teamBeginTime <= 0) {
            $this->addFlashMessage('团购开始时间无效');
            goto out_fail_validate;
        }
        $goodsTeamInfo['team_begin_time'] = $teamBeginTime;

        $team_end_time_str = $validator->validate('team_end_time_str');
        $teamEndTime       = Time::gmStrToTime($team_end_time_str);
        if ($teamEndTime <= 0) {
            $this->addFlashMessage('团购结束时间无效');
            goto out_fail_validate;
        }
        $goodsTeamInfo['team_end_time'] = $teamEndTime;

        //参数验证
        if (!$this->validate($validator)) {
            goto out_fail_validate;
        }

        // 写入到数据库
        unset($goodsTeam);
        $goodsBasicService = new GoodsBasicService();
        $goodsTeam         = $goodsBasicService->loadGoodsTeamByGoodsId($goods_id);
        $goodsTeam->copyFrom($goodsTeamInfo);
        $goodsTeam->save();

        // 成功，显示商品详情
        $this->addFlashMessage('商品团购信息保存成功');

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goods_id);

        RouteHelper::reRoute($this, RouteHelper::makeUrl('/Goods/Edit/Team', array('goods_id' => $goods_id), true));
        return;

        // 参数验证失败
        out_fail_validate:
        $smarty->display('goods_edit_team.tpl');
        return;

        out_fail_list_goods:
        RouteHelper::reRoute($this, '/Goods/Search');
    }

}
