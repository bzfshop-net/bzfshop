<?php
/**
 * @author QiangYu
 *
 * 本地操作引擎，如果你的程序不是在任何的云平台上而是在服务器自身上面操作，这里实现了一个 本地引擎
 *
 */

namespace Core\Cloud\Local;


use Core\Cloud\CloudHelper;
use Core\Cloud\ICloudEngine;
use Core\Plugin\PluginHelper;

class LocalEngine implements ICloudEngine
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
        $sysPath          = '';
        $sysDir           = '';

        switch ($system) {
            case PluginHelper::SYSTEM_SHOP:
                $sysPath = SHOP_PATH;
                $sysDir  = SHOP_DIR;
                break;
            case PluginHelper::SYSTEM_AIMEIDAREN:
                $sysPath = AIMEIDAREN_PATH;
                $sysDir  = AIMEIDAREN_DIR;
                break;
            case PluginHelper::SYSTEM_GROUPON:
                $sysPath = GROUPON_PATH;
                $sysDir  = GROUPON_DIR;
                break;
            case PluginHelper::SYSTEM_MOBILE:
                $sysPath = MOBILE_PATH;
                $sysDir  = MOBILE_DIR;
                break;
            case PluginHelper::SYSTEM_MANAGE:
                $sysPath = MANAGE_PATH;
                $sysDir  = MANAGE_DIR;
                break;
            case PluginHelper::SYSTEM_SUPPLIER:
                $sysPath = SUPPLIER_PATH;
                $sysDir  = SUPPLIER_DIR;
                break;
            case PluginHelper::SYSTEM_INSTALL:
                $sysPath = INSTALL_PATH;
                $sysDir  = INSTALL_DIR;
                break;
            default:
                throw new \InvalidArgumentException('can not init for system [' . $system . ']');
        }

        //数据路径
        if (!$f3->get('sysConfig[data_path_root]')) {
            $f3->set('sysConfig[data_path_root]', realpath($sysPath . '/../data'));
        }

        //数据 url prefix
        if (!$f3->get('sysConfig[data_url_prefix]')) {
            $f3->set(
                'sysConfig[data_url_prefix]',
                str_replace('/' . $sysDir, '/data', $f3->get('sysConfig[webroot_url_prefix]'))
            );
        }

        //图片 image_url_prefix
        if (!$f3->get('sysConfig[image_url_prefix]')) {
            $f3->set('sysConfig[image_url_prefix]', $f3->get('sysConfig[data_url_prefix]'));
        }

        // RunTime 路径
        if (!$f3->get('sysConfig[runtime_path]')) {
            $f3->set('sysConfig[runtime_path]', realpath(PROTECTED_PATH . '/Runtime'));
        }

        define('RUNTIME_PATH', $f3->get('sysConfig[runtime_path]'));

        // 设置 Tmp 路径
        $f3->set('TEMP', RUNTIME_PATH . '/Temp/');

        // 设置 Log 路径
        $f3->set('LOGS', RUNTIME_PATH . '/Log/' . $systemUpperFirst . '/');

        //开启 Cache 功能
        if (!$f3->get('CACHE')) {
            // 让 F3 自动选择使用最优的 Cache 方案，最差的情况会使用 TEMP/cache 目录文件做缓存
            $f3->set('CACHE', 'true');
        }

        //设置 smarty 工作目录
        $smarty->setCompileDir(RUNTIME_PATH . '/Smarty/' . $systemUpperFirst . '/Compile');
        $smarty->setCacheDir(RUNTIME_PATH . '/Smarty/' . $systemUpperFirst . '/Cache');

        // asset 路径，用于发布 css, js , 图片 等
        if (!$f3->get('sysConfig[asset_path_root]')) {
            $f3->set('sysConfig[asset_path_root]', realpath($sysPath . '/asset'));
        }

        if (!$f3->get('sysConfig[asset_path_url_prefix]')) {
            $f3->set('sysConfig[asset_path_url_prefix]', $f3->get('sysConfig[webroot_url_prefix]') . '/asset');
        }

    }

    private function initConsoleEnv()
    {
        global $f3;

        $sysPath          = CONSOLE_PATH;
        $sysDir           = CONSOLE_DIR;
        $systemUpperFirst = 'Console';

        //数据路径
        if (!$f3->get('sysConfig[data_path_root]')) {
            $f3->set('sysConfig[data_path_root]', realpath($sysPath . '/../data'));
        }

        //数据 url prefix
        if (!$f3->get('sysConfig[data_url_prefix]')) {
            $f3->set(
                'sysConfig[data_url_prefix]',
                str_replace('/' . $sysDir, '/data', $f3->get('sysConfig[webroot_url_prefix]'))
            );
        }

        //图片 image_url_prefix
        if (!$f3->get('sysConfig[image_url_prefix]')) {
            $f3->set('sysConfig[image_url_prefix]', $f3->get('sysConfig[data_url_prefix]'));
        }

        // RunTime 路径
        if (!$f3->get('sysConfig[runtime_path]')) {
            $f3->set('sysConfig[runtime_path]', realpath(PROTECTED_PATH . '/Runtime'));
        }

        define('RUNTIME_PATH', $f3->get('sysConfig[runtime_path]'));

        // 设置 Tmp 路径
        $f3->set('TEMP', RUNTIME_PATH . '/Temp/');

        // 设置 Log 路径
        $f3->set('LOGS', RUNTIME_PATH . '/Log/' . $systemUpperFirst . '/');

        //开启 Cache 功能
        if (!$f3->get('CACHE')) {
            // 让 F3 自动选择使用最优的 Cache 方案，最差的情况会使用 TEMP/cache 目录文件做缓存
            $f3->set('CACHE', 'true');
        }
    }

    public function initEnv($system)
    {

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
                return new LocalLog();
            case CloudHelper::CLOUD_MODULE_DB:
                return new LocalDb();
            default:
                return null;
        }
    }
} 