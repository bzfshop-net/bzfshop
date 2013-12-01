<?php

/**
 * @author QiangYu
 *
 * 商品编辑操作
 *
 * */

namespace Controller\Goods\Edit;

use Core\Cache\ClearHelper;
use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Money;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Goods as GoodsBasicService;
use Core\Service\Goods\Log as GoodsLogService;
use Core\Service\User\Admin as AdminUserService;
use Core\Service\User\Supplier as SupplierUserService;

class Edit extends \Controller\AuthController
{
    static $goodsLogDesc = '商品信息';

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
        $goods             = $goodsBasicService->loadGoodsById($goods_id);
        if ($goods->isEmpty()) {
            $this->addFlashMessage('非法商品ID');
            goto out_fail;
        }

        // 显示商品
        $smarty->assign('goods', $goods);

        out_display:
        $smarty->display('goods_edit_edit.tpl');
        return;

        out_fail:
        RouteHelper::reRoute($this, '/Goods/Search');
    }


    public function post($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_post');

        global $smarty;

        $isCreateGoods = false; // 是否是创建新商品

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $goods_id  = $validator->digits()->filter('ValidatorIntValue')->validate('goods_id');

        if (!$this->validate($validator)) {
            goto out_fail_list_goods;
        }
        unset($validator);

        // 用户提交的商品信息做验证
        $goods = $f3->get('POST[goods]');

        if (empty($goods)) {
            goto out_fail_validate;
        }

        $validator = new Validator($goods);
        $goodsInfo = array();

        //表单数据验证、过滤
        $goodsInfo['goods_name']       = $validator->required('商品名不能为空')->validate('goods_name');
        $goodsInfo['goods_name_short'] = $validator->required('商品短标题不能为空')->validate('goods_name_short');
        $goodsInfo['keywords']         = $validator->validate('keywords');
        $goodsInfo['seo_title']        = $validator->validate('seo_title');
        $goodsInfo['seo_keyword']      = $validator->validate('seo_keyword');
        $goodsInfo['seo_description']  = $validator->validate('seo_description');
        $goodsInfo['goods_sn']         = $validator->validate('goods_sn');
        $goodsInfo['warehouse']        = $validator->validate('warehouse');
        $goodsInfo['shelf']            = $validator->validate('shelf');
        $goodsInfo['cat_id']           = $validator->required('商品分类不能为空')->filter('ValidatorIntValue')->validate(
            'cat_id'
        );

        // 记录管理员
        $authAdminUser              = AuthHelper::getAuthUser();
        $goodsInfo['admin_user_id'] = $validator->filter('ValidatorIntValue')->validate(
            'admin_user_id'
        );
        // 如果没有选择管理员，就用当前管理员
        if (empty($goodsInfo['admin_user_id'])) {
            $goodsInfo['admin_user_id']   = $authAdminUser['user_id'];
            $goodsInfo['admin_user_name'] = $authAdminUser['user_name'];
        } else {
            $adminUserService = new AdminUserService();
            $adminUser        = $adminUserService->loadAdminById($goodsInfo['admin_user_id']);
            if ($adminUser->isEmpty()) {
                $this->addFlashMessage('管理员[' . $goodsInfo['admin_user_id'] . ']不存在');
                goto out_fail_validate;
            }
            $goodsInfo['admin_user_name'] = $adminUser['user_name'];
            unset($adminUser);
            unset($adminUserService);
        }

        $goodsInfo['brand_id']               = $validator->filter('ValidatorIntValue')->validate(
            'brand_id'
        );
        $goodsInfo['suppliers_id']           = $validator->required('供货商不能为空')->filter('ValidatorIntValue')->validate(
            'suppliers_id'
        );
        $goodsInfo['is_alone_sale']          = $validator->filter('ValidatorIntValue')->validate('is_alone_sale');
        $goodsInfo['is_best']                = $validator->filter('ValidatorIntValue')->validate('is_best');
        $goodsInfo['is_new']                 = $validator->filter('ValidatorIntValue')->validate('is_new');
        $goodsInfo['is_hot']                 = $validator->filter('ValidatorIntValue')->validate('is_hot');
        $goodsInfo['is_on_sale']             = $validator->filter('ValidatorIntValue')->validate('is_on_sale');
        $goodsInfo['market_price']           = Money::toStorage($validator->validate('market_price'));
        $goodsInfo['shop_price']             = Money::toStorage($validator->validate('shop_price'));
        $goodsInfo['shipping_fee']           = Money::toStorage($validator->validate('shipping_fee'));
        $goodsInfo['shipping_free_number']   = $validator->validate('shipping_free_number');
        $goodsInfo['goods_number']           = abs($validator->filter('ValidatorIntValue')->validate('goods_number'));
        $goodsInfo['virtual_buy_number']     = $validator->filter('ValidatorIntValue')->validate('virtual_buy_number');
        $goodsInfo['suppliers_price']        = Money::toStorage($validator->validate('suppliers_price'));
        $goodsInfo['suppliers_shipping_fee'] = Money::toStorage($validator->validate('suppliers_shipping_fee'));
        $goodsInfo['sort_order']             = $validator->validate('sort_order');
        $goodsInfo['warn_number']            = $validator->filter('ValidatorIntValue')->validate('warn_number');
        $goodsInfo['goods_brief']            = @$goods['goods_brief']; //不需要过滤 html
        $goodsInfo['goods_notice']           = @$goods['goods_notice']; //不需要过滤 html
        $goodsInfo['goods_after_service']    = @$goods['goods_after_service']; //不需要过滤 html
        $goodsInfo['seller_note']            = $validator->validate('seller_note');

        $goodsInfo['system_tag_list'] = Utils::makeTagString(@$goods['system_tag_list']); // 生成系统的 tag string
        $goodsInfo['update_time']     = Time::gmTime(); // 商品的更新时间

        $goodsInfo['goods_desc'] = @$goods['goods_desc']; //不需要过滤 html

        if (!$this->validate($validator)) {
            goto out_fail_validate;
        }

        // 某些时候，我们不允许编辑直接粘贴别人网站的图片上来，所以我们需要过滤图片的域名
        $goodsDescAllowImageDomainArray = $f3->get('sysConfig[goods_desc_allow_image_domain_array]');
        if ($goodsDescAllowImageDomainArray
            && is_array($goodsDescAllowImageDomainArray)
            && !empty($goodsDescAllowImageDomainArray)
        ) {
            $patternMatch = array();
            preg_match_all(
                '/<img(.*?)src="(.*?)"(.*?)\/?>/',
                $goodsInfo['goods_desc'],
                $patternMatch,
                PREG_SET_ORDER
            );

            // 检查每一个图片
            foreach ($patternMatch as $matchItem) {
                $imageUrl = $matchItem[2];
                $urlInfo  = parse_url($imageUrl);
                if (!in_array(@$urlInfo['host'], $goodsDescAllowImageDomainArray)) {
                    $this->addFlashMessage('商品详情非法图片 ' . $imageUrl);
                    goto out_fail_validate;
                }
            }
        }

        // 写入到数据库
        unset($goods);
        $goodsBasicService = new GoodsBasicService();
        $goods             = $goodsBasicService->loadGoodsById($goods_id);

        // 判断是否是新建商品
        $isCreateGoods = $goods->isEmpty();
        if ($isCreateGoods) {
            // 权限检查
            $this->requirePrivilege('manage_goods_create');
            $goodsInfo['add_time'] = Time::gmTime();
        }

        $post_goods_sn = $validator->validate('goods_sn');
        if ($isCreateGoods && !Utils::isBlank($post_goods_sn)) {
            $goodsInfo['goods_sn'] = $post_goods_sn;
        }

        $goods->copyFrom($goodsInfo);
        $goods->save();

        // 新商品需要自动生成 goods_sn
        if ($isCreateGoods && Utils::isBlank($post_goods_sn)) {
            $goods->goods_sn = $f3->get('sysConfig[goods_sn_prefix]') . $goods['goods_id'];
            $goods->save();
        }

        // 取得供货商信息
        $supplierName = '';
        if (!empty($goods['suppliers_id'])) {
            $supplierUserService = new SupplierUserService();
            $supplierInfo        = $supplierUserService->loadSupplierById($goods['suppliers_id']);
            if (!$supplierInfo->isEmpty()) {
                $supplierName = $supplierInfo['suppliers_name'];
            }
        }

        // 记录商品编辑日志
        $goodsLogContent = '商品编辑：[' . $goods['admin_user_id'] . ']' . $goods['admin_user_name'] . "\n"
            . '上架状态：' . ($goods['is_on_sale'] > 0 ? '已上架' : '未上架') . "\n"
            . '销售价：' . Money::toSmartyDisplay($goods['shop_price']) . '  供货价：'
            . Money::toSmartyDisplay($goods['suppliers_price']) . "\n"
            . '快递费：' . Money::toSmartyDisplay($goods['shipping_fee'])
            . '  供货快递费：' . Money::toSmartyDisplay($goods['suppliers_shipping_fee']) . "\n"
            . ($goods['shipping_free_number'] > 0 ? '' . $goods['shipping_free_number'] . "件免邮\n" : '')
            . '商品排序：' . $goods['sort_order'] . "\n"
            . '系统Tag：' . $goods['system_tag_list'] . "\n"
            . '供货商：[' . $goods['suppliers_id'] . ']' . $supplierName;

        $goodsLogService = new GoodsLogService();
        $goodsLogService->addGoodsLog(
            $goods['goods_id'],
            $authAdminUser['user_id'],
            $authAdminUser['user_name'],
            ($isCreateGoods ? '新建商品' : static::$goodsLogDesc),
            $goodsLogContent
        );

        // 成功，显示商品详情
        $this->addFlashMessage('商品信息保存成功');

        //清除缓存，确保商品显示正确
        ClearHelper::clearGoodsCacheById($goods->goods_id);

        RouteHelper::reRoute(
            $this,
            RouteHelper::makeUrl('/Goods/Edit/Edit', array('goods_id' => $goods->goods_id), true)
        );
        return;

        // 参数验证失败
        out_fail_validate:
        if (!$goods_id) {
            // 新建商品验证失败
            RouteHelper::reRoute($this, '/Goods/Create');
            return;
        }

        $smarty->assign('goods', $goodsInfo);
        $smarty->display('goods_edit_edit.tpl');
        return;

        out_fail_list_goods:
        RouteHelper::reRoute($this, '/Goods/Search');
    }

}
