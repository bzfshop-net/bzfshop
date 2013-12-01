<?php

/**
 * @author QiangYu
 *
 * 快递公司 设置
 *
 * */

namespace Controller\Misc;

use Core\Helper\Utility\Request;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\Meta\Express as ExpressService;

class Express extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;

        $expressService = new ExpressService();

        $expressArray = $expressService->fetchExpressArray();

        $smarty->assign('expressArray', $expressArray);

        $smarty->display('misc_express.tpl');
    }

    /**
     * 更新或者新建一个快递公司
     *
     * @param $f3
     */
    public function Edit($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_misc_express_edit');

        global $smarty;

        // 参数验证
        $validator = new Validator($f3->get('GET'));
        $meta_id   = $validator->digits()->validate('meta_id');
        $meta_id   = $meta_id ? : 0;

        //  加载 快递信息
        $expressService = new ExpressService();
        $expressInfo    = $expressService->loadMetaById($meta_id);

        if (Request::isRequestGet()) {
            goto out_assign;
        }

        // 安全性检查
        if ($meta_id > 0) {
            if ($expressInfo->isEmpty()
                || ExpressService::META_TYPE != $expressInfo->meta_type
            ) {
                $this->addFlashMessage('非法ID[' . $meta_id . ']');
                goto out;
            }
        }

        unset($validator);
        $validator                     = new Validator($f3->get('POST'));
        $inputArray                    = array();
        $inputArray['meta_type']       = ExpressService::META_TYPE;
        $inputArray['meta_name']       = $validator->required()->validate('meta_name');
        $inputArray['meta_ename']      = $validator->required()->validate('meta_ename');
        $inputArray['meta_sort_order'] = $validator->digits()->validate('meta_sort_order');
        $inputArray['meta_status']     = $validator->digits()->validate('meta_status');
        $inputArray['meta_desc']       = $validator->validate('meta_desc');

        if (!$this->validate($validator)) {
            goto out;
        }

        // 保存
        $expressInfo->copyFrom($inputArray);
        $expressInfo->save();

        $this->addFlashMessage('快递信息保存成功');

        // POST 成功从这里退出
        RouteHelper::reRoute(
            $this,
            RouteHelper::makeUrl('/Misc/Express/Edit', array('meta_id' => $expressInfo->meta_id), true)
        );
        return;

        out_assign:
        $smarty->assign($expressInfo->toArray());

        out:
        $smarty->display('misc_express_edit.tpl');
    }

}
