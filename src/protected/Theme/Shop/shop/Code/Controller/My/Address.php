<?php

/**
 * @author QiangYu
 *
 * 我的地址
 *
 * */

namespace Controller\My;

use Core\Helper\Utility\Auth as AuthHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Service\User\Address as UserAddressService;

class Address extends \Controller\AuthController
{

    public function get($f3)
    {
        global $smarty;

        $userInfo = AuthHelper::getAuthUser();

        $userAddressService = new UserAddressService();
        $addressInfo        = $userAddressService->loadUserFirstAddress($userInfo['user_id']);

        $smarty->assign($addressInfo->toArray());

        out_display:
        $smarty->display('my_address.tpl', 'get');
    }

    public function post($f3)
    {
        global $smarty;

        // 首先做参数合法性验证
        $validator = new Validator($f3->get('POST'));

        $addressInfo              = array();
        $addressInfo['consignee'] = $validator->required('姓名不能为空')->validate('consignee');
        $addressInfo['address']   = $validator->required('地址不能为空')->validate('address');
        $addressInfo['mobile']    = $validator->required('手机号码不能为空')->digits('手机号码格式不正确')->validate('mobile');
        $addressInfo['tel']       = $validator->validate('tel');
        $addressInfo['zipcode']   = $validator->digits('邮编格式不正确')->validate('zipcode');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        $userInfo = AuthHelper::getAuthUser();

        $userAddressService = new UserAddressService();
        $userAddressService->updateUserFirstAddress($userInfo['user_id'], $addressInfo);

        $this->addFlashMessage('地址更新成功');

        RouteHelper::reRoute($this, '/My/Address');
        return;

        out_fail: // 失败返回
        $smarty->display('my_address.tpl', 'post');
    }

}
