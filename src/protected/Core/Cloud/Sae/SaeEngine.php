<?php
/**
 * @author QiangYu
 *
 * 新浪的 Sae 云平台引擎
 *
 */

namespace Core\Cloud\Sae;


use Core\Cloud\CloudHelper;
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
        $f3->set(
            'sysConfig[data_url_prefix]',
            rtrim($saeStorage->getUrl($f3->get('sysConfig[sae_storage_data_domain]'), ''), '/')
        );

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
        $smarty->compile_locking = false;

        // asset 路径，用于发布 css, js , 图片 等
        $f3->set('sysConfig[asset_path_root]', $f3->get('sysConfig[sae_storage_data_path]') . '/asset');
        $f3->set('sysConfig[asset_path_url_prefix]', $f3->get('sysConfig[data_url_prefix]') . '/asset');

        // 我们把 Asset 发布到 Storage，由于 Sae 的 Storage 有一些限制，所以我们关闭 Asset 的智能发布功能
        // 关闭系统的 asset 合并功能
        $f3->set('sysConfig[enable_asset_merge]', false);
        // 关闭 asset 自动重新发布功能，Sae Storage 不支持取时间戳，所以无法自动重新发布
        $f3->set('sysConfig[enable_asset_smart_publish]', false);
        $f3->set('sysConfig[enable_asset_hash_url]', false);
        $f3->set('sysConfig[enable_asset_hash_name]', false);

    }

    private function initConsoleEnv()
    {
        global $f3;

        $systemUpperFirst = 'Console';

        $saeStorage = new \SaeStorage();

        //数据路径
        $f3->set('sysConfig[data_path_root]', $f3->get('sysConfig[sae_storage_data_path]'));
        $f3->set(
            'sysConfig[data_url_prefix]',
            rtrim($saeStorage->getUrl('sysConfig[sae_storage_data_domain]', ''), '/')
        );

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
                return new SaeLog();
            case CloudHelper::CLOUD_MODULE_DB:
                return new SaeDb();
            default:
                return null;
        }
    }
} 