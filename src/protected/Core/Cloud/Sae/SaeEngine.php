<?php
/**
 * @author QiangYu
 *
 * 新浪的 Sae 云平台引擎
 *
 */

namespace Core\Cloud\Sae;


use Core\Cloud\ICloudEngine;
use Core\Plugin\PluginHelper;

class SaeEngine implements ICloudEngine
{
    private $system = null;

    public function detectCloudEnv()
    {
        return true;
    }

    private function initWebEnv($system)
    {
        global $f3;
        global $smarty;

        $systemUpperFirst = ucfirst($system);

        $saeStorage = new \SaeStorage();

        //数据路径
        $f3->set('sysConfig[data_path_root]', $f3->get('sysConfig[sae_storage_data_path]'));
        $f3->set('sysConfig[data_url_prefix]', rtrim($saeStorage->getUrl('domain', ''), '/'));

        //图片 image_url_prefix
        if (!$f3->get('sysConfig[image_url_prefix]')) {
            $f3->set('sysConfig[image_url_prefix]', $f3->get('sysConfig[data_url_prefix]'));
        }

        // RunTime 路径
        $f3->set('sysConfig[runtime_path]', $f3->get('sysConfig[sae_runtime]'));

        define('RUNTIME_PATH', $f3->get('sysConfig[runtime_path]'));

        // 设置 Tmp 路径
        $f3->set('TEMP', RUNTIME_PATH . '/Temp/');

        // 设置 Log 路径
        $f3->set('LOGS', RUNTIME_PATH . '/Log/' . $systemUpperFirst . '/');

        //开启 Cache 功能
        $f3->set('CACHE', RUNTIME_PATH . '/Cache/');

        //设置 smarty 工作目录
        $smarty->setCompileDir(RUNTIME_PATH . '/Smarty/' . $systemUpperFirst . '/Compile');
        $smarty->setCacheDir(RUNTIME_PATH . '/Smarty/' . $systemUpperFirst . '/Cache');

        // asset 路径，用于发布 css, js , 图片 等
        $f3->set('sysConfig[asset_path_root]', $f3->get('sysConfig[sae_storage_data_path]') . '/asset');
        $f3->set('sysConfig[asset_path_url_prefix]', $f3->get('sysConfig[data_url_prefix]') . '/asset');

    }

    private function initConsoleEnv()
    {
        // do nothing now
    }

    public function initEnv($system)
    {
        global $f3;

        $this->system = $system;

        if (PluginHelper::SYSTEM_CONSOLE == $system) {
            $this->initConsoleEnv();
        } else {
            $this->initWebEnv($system);
        }

        return true;
    }

    public function getCloudModule($module)
    {
        switch ($module) {
            case CloudHelper::CLOUD_MODULE_Log:
                return new SaeEngine();

            default:
                return null;
        }
    }
} 