<?php

/**
 * @author QiangYu
 *
 * KindEditor 对应的 PHP 程序后台
 *
 * */

namespace Controller\File;

use Core\Helper\Utility\Validator;

class KindEditor extends \Controller\AuthController
{

    public function get($f3)
    {
        // 上传路径
        $dataPathRoot = $f3->get('sysConfig[data_path_root]');
        if (empty($dataPathRoot)) {
            $dataPathRoot = $f3->get('BASE') . '/data';
        }

        // 上传路径对应的 URL 前缀
        $dataUrlPrefix = $f3->get('sysConfig[data_url_prefix]');
        if (empty($dataUrlPrefix)) {
            $dataUrlPrefix = $f3->get('BASE') . '/data';
        }

        // 操作
        $validator = new Validator($f3->get('GET'));
        $action    = $validator->validate('action');

        $kindEditor = new \KindEditor\KindEditor();
        $kindEditor->doAction($dataPathRoot, $dataUrlPrefix, $action);
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
