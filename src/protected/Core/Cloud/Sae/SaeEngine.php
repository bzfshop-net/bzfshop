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

        // -------------------- 1. 设置 data 路径 --------------------------------------

        // 当前网站的 webroot_schema_host
        if (!$f3->get('sysConfig[webroot_schema_host]')) {
            $f3->set(
                'sysConfig[webroot_schema_host]',
                $f3->get('SCHEME') . '://' . $f3->get('HOST')
                . (('80' != $f3->get('PORT')) ? ':' . $f3->get('PORT') : '')
            );
        }

        // 当前网站的 webroot_url_prefix
        if (!$f3->get('sysConfig[webroot_url_prefix]')) {
            $f3->set(
                'sysConfig[webroot_url_prefix]',
                $f3->get('sysConfig[webroot_schema_host]') . $f3->get('BASE')
            );
        }

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

        // -------------------- 2. 设置 runtime 路径 --------------------------------------

        // RunTime 路径
        $f3->set('sysConfig[runtime_path]', $f3->get('sysConfig[sae_runtime]'));

        define('RUNTIME_PATH', $f3->get('sysConfig[runtime_path]'));

        // 设置 Tmp 路径
        $f3->set('TEMP', RUNTIME_PATH . '/Temp/');

        // 设置 Log 路径
        $f3->set('LOGS', RUNTIME_PATH . '/Log/' . $systemUpperFirst . '/');

        //Sae 禁止 Cache 功能
        $f3->set('CACHE', false);

        // -------------------- 3. 设置 Smarty --------------------------------------

        //设置 smarty 工作目录
        $smarty->setCompileDir(RUNTIME_PATH . '/Smarty');
        $smarty->setCacheDir(RUNTIME_PATH . '/Smarty');
        $smarty->compile_locking = false;

        //smarty 在 SAE 下禁用缓存
        $f3->set('sysConfig[smarty_caching]', false);

        // smarty 会对生成的模板文件做一个 chmod 操作，这个操作会导致 Sae 报错
        $smarty->_file_perms = null;

        // -------------------- 4. 设置 Asset 管理 --------------------------------------

        // 关闭系统的 asset 合并功能
        $f3->set('sysConfig[enable_asset_merge]', false);

        // 我们使用 Sae 专用的 AssetManager
        SaeAssetManager::instance($system);
        \Core\Asset\ManagerHelper::setAssetManager(SaeAssetManager::instance());
    }

    private function initConsoleEnv()
    {
        global $f3;

        $systemUpperFirst = 'Console';

        $saeStorage = new \SaeStorage();

        // -------------------- 1. 设置 data 路径 --------------------------------------

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

        // -------------------- 2. 设置 runtime 路径 --------------------------------------

        // RunTime 路径
        $f3->set('sysConfig[runtime_path]', $f3->get('sysConfig[sae_runtime]'));

        define('RUNTIME_PATH', $f3->get('sysConfig[runtime_path]'));

        // 设置 Tmp 路径
        $f3->set('TEMP', SAE_TMP_PATH . DIRECTORY_SEPARATOR);

        // 设置 Log 路径
        $f3->set('LOGS', RUNTIME_PATH . '/Log/' . $systemUpperFirst . '/');

        //Sae 禁止 Cache 功能
        $f3->set('CACHE', false);
    }

    public function initCloudEnv($system)
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
                return SaeLog::instance();
            case CloudHelper::CLOUD_MODULE_DB:
                return SaeDb::instance();
            case CloudHelper::CLOUD_MODULE_STORAGE:
                return SaeStorage::instance();
            default:
                return null;
        }
    }
} 