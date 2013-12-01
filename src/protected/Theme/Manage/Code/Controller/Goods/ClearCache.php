<?php

/**
 * @author QiangYu
 *
 * 清除商品的缓存
 *
 * */

namespace Controller\Goods;

use Core\Cache\ClearHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;

class ClearCache extends \Controller\AuthController
{

    public function get($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_goods_edit_edit_get');

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $goods_id  = $validator->required('商品ID不能为空')->digits()->min(1)->validate('goods_id');

        if (!$this->validate($validator)) {
            goto out;
        }

        // 清除商品缓存
        ClearHelper::clearGoodsCacheById($goods_id);
        $this->addFlashMessage('商品[' . $goods_id . '] 缓存清除成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

}
