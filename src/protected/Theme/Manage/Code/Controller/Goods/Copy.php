<?php

/**
 * @author QiangYu
 *
 * 商品复制操作
 *
 * */

namespace Controller\Goods;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;
use Core\Service\Goods\Gallery as GoodsGalleryService;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Goods\Log as GoodsLogService;
use Core\Service\Goods\Spec as GoodsSpecService;
use Core\Service\Goods\Type as GoodsTypeService;

class Copy extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_create');

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $goods_id = $validator->required('商品ID不能为空')->digits()->min(1)->validate('goods_id');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        // 取得商品信息
        $goodsBasicService = new GoodsBasicService();
        $goods = $goodsBasicService->loadGoodsById($goods_id);
        if ($goods->isEmpty()) {
            $this->addFlashMessage('非法商品ID');
            goto out_fail;
        }

        $authAdminUser = AuthHelper::getAuthUser();

        // 1. 复制 goods 信息
        $goodsArray = $goods->toArray();
        unset($goodsArray['goods_id']); // 清除主键

        // 新商品缺省为下线状态
        $goodsArray['is_on_sale'] = 0;

        // 清除购买数量统计
        $goodsArray['user_buy_number'] = 0;
        $goodsArray['user_pay_number'] = 0;

        // 设置复制人
        $goodsArray['admin_user_id'] = $authAdminUser['user_id'];
        $goodsArray['admin_user_name'] = $authAdminUser['user_name'];

        // 处理商品的规格
        if (!empty($goodsArray['goods_spec'])) {
            $goodsSpecService = new GoodsSpecService();
            $goodsSpecService->initWithJson($goodsArray['goods_spec']);
            $goodsSpecService->clearGoodsSpecImgIdArray(); // 清除图片 ID 的关联
            $goodsArray['goods_spec'] = $goodsSpecService->getJsonStr();
            unset($goodsSpecService);
        }

        $goodsArray['add_time'] = Time::gmTime();
        $newGoods = $goodsBasicService->loadGoodsById(0);
        $newGoods->copyFrom($goodsArray);
        $newGoods->save();

        // 更新 goods_sn
        $newGoods->goods_sn = $f3->get('sysConfig[goods_sn_prefix]') . $newGoods['goods_id'];
        $newGoods->save();
        unset($goodsArray);

        // 2. 复制 goods_attr 信息
        if ($goods->type_id > 0) {
            $goodsTypeService = new GoodsTypeService();
            $goodsAttrValueArray = $goodsTypeService->fetchGoodsAttrItemValueArray($goods->goods_id, $goods->type_id);
            foreach ($goodsAttrValueArray as $goodsAttrValue) {
                $goodsAttr = $goodsTypeService->loadGoodsAttrById(0);
                $goodsAttr->goods_id = $newGoods->goods_id;
                $goodsAttr->attr_item_id = $goodsAttrValue['meta_id'];
                $goodsAttr->attr_item_value = $goodsAttrValue['attr_item_value'];
                $goodsAttr->save();
                unset($goodsAttr);
            }
            unset($goodsAttrValueArray);
            unset($goodsTypeService);
        }

        // 3. 复制 goods_gallery 信息
        $goodsGalleryService = new GoodsGalleryService();
        $goodsGalleryArray = $goodsGalleryService->fetchGoodsGalleryArrayByGoodsId($goods_id);
        foreach ($goodsGalleryArray as $goodsGalleryItem) {

            // 新建一个 goods_gallery 记录
            $goodsGallery = $goodsGalleryService->loadGoodsGalleryById(0);
            unset($goodsGalleryItem['img_id']);
            $goodsGallery->copyFrom($goodsGalleryItem);
            $goodsGallery->goods_id = $newGoods['goods_id'];
            $goodsGallery->save();
            unset($goodsGallery);
        }
        unset($goodsGalleryArray);
        unset($goodsGalleryService);

        // 4. 复制 goods_team 信息
        $goodsTeam = $goodsBasicService->loadGoodsTeamByGoodsId($goods_id);
        if (!$goodsTeam->isEmpty()) {
            $goodsTeamInfo = $goodsTeam->toArray();
            unset($goodsTeamInfo['team_id']);
            $goodsTeamInfo['goods_id'] = $newGoods['goods_id'];
            $newGoodsTeam = new DataMapper('goods_team');
            $newGoodsTeam->copyFrom($goodsTeamInfo);
            $newGoodsTeam->save();
            unset($newGoodsTeam);
            unset($goodsTeamInfo);
            unset($goodsTeam);
        }

        // 5. 复制 link_goods 信息
        $linkGoodsArray = $goodsBasicService->fetchSimpleLinkGoodsArray($goods_id);
        foreach ($linkGoodsArray as $linkGoodsItem) {
            unset($linkGoodsItem['link_id']);
            $linkGoodsItem['goods_id'] = $newGoods['goods_id'];
            $linkGoodsItem['admin_id'] = $authAdminUser['user_id'];
            $linkGoods = new DataMapper('link_goods');
            $linkGoods->copyFrom($linkGoodsItem);
            $linkGoods->save();
            unset($linkGoods);
        }
        unset($linkGoodsArray);

        // 6. 复制 goods_promote 信息
        $goodsPromote = $goodsBasicService->loadGoodsPromoteByGoodsId($goods_id);
        if (!$goodsPromote->isEmpty()) {
            $goodsPromoteInfo = $goodsPromote->toArray();
            unset($goodsPromoteInfo['promote_id']);
            $goodsPromoteInfo['goods_id'] = $newGoods['goods_id'];
            $newGoodspromote = new DataMapper('goods_promote');
            $newGoodspromote->copyFrom($goodsPromoteInfo);
            $newGoodspromote->save();
            unset($newGoodspromote);
        }
        unset($goodsPromote);

        // 记录编辑日志
        $goodsLogContent = '从 [' . $goods_id . '] 复制过来';
        $goodsLogService = new GoodsLogService();
        $goodsLogService->addGoodsLog(
            $newGoods['goods_id'],
            $authAdminUser['user_id'],
            $authAdminUser['user_name'],
            '复制商品',
            $goodsLogContent
        );

        $this->addFlashMessage('复制新建商品成功');

        RouteHelper::reRoute(
            $this,
            RouteHelper::makeUrl('/Goods/Edit/Edit', array('goods_id' => $newGoods['goods_id']), true)
        );
        return; //正常返回

        out_fail:
        RouteHelper::reRoute($this, '/Goods/Search');
    }

}
