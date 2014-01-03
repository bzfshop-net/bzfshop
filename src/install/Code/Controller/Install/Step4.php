<?php

/**
 * @author QiangYu
 *
 * 404 错误
 *
 * */

namespace Controller\Install;

class Step4 extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        // 建立 install.lock 文件，后面就不会再进入安装程序了
        file_put_contents($f3->get('sysConfig[data_path_root]') . '/install.lock', 'install.lock');

        $smarty->assign('envFile', realpath(INSTALL_PATH . '/../protected/Config/env.cfg'));
        $smarty->assign('configFile', realpath(INSTALL_PATH . '/../protected/Config/common-prod.cfg'));

        $smarty->display('install_step4.tpl');
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
