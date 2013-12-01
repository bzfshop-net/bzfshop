<?php

/**
 * @author QiangYu
 *
 * 所有 Controller 的基类
 *
 * */

namespace Controller;

use Core\Helper\Utility\Validator;

class BaseController extends \Core\Controller\BaseController
{

    public function beforeRoute($f3)
    {
        parent::beforeRoute($f3);
        // do nothing
    }

    public function afterRoute($f3)
    {
        // 使用 Cookie 输出 flash message，目的是方便页面做缓存，别把这些数据写在页面上
        if (count($this->flashMessageArray) > 0) {
            setcookie('flash_message', json_encode($this->flashMessageArray), 0, $f3->get('BASE') . '/');
        } else {
            //setcookie('flash_message', null, 0, $f3->get('BASE')); // 清除上次的旧数据
        }
        parent::afterRoute($f3);
    }

    /**
     * Http 的 HEAD 方法调用
     *
     * @param $f3
     */
    public function head($f3)
    {
        // do nothing here
    }
}
