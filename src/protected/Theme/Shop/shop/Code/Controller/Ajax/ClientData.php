<?php

/**
 * @author QiangYu
 *
 * 取得客户端需要的信息
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\ClientData as ClientDataHelper;

class ClientData extends \Controller\BaseController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    public function get($f3)
    {
        Ajax::header();
        $clientData = ClientDataHelper::getClientDataArray();
        if (null == $clientData) {
            $clientData = array();
        }
        echo Ajax::buildResult(null, null, json_encode($clientData));
    }

}