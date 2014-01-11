<?php
/**
 * @author QiangYu
 *
 * 本地操作引擎，如果你的程序不是在任何的云平台上而是在服务器自身上面操作，这里实现了一个 本地引擎
 *
 */

namespace Core\Cloud\Bae3;


use Core\Cloud\CloudHelper;
use Core\Cloud\ICloudEngine;
use Core\Cloud\Local\LocalStorage;
use Core\Plugin\PluginHelper;

class Bae3Engine implements ICloudEngine
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

        // 预先加载一些模块，提高后面的加载效率
        require_once(PROTECTED_PATH . '/Core/Asset/SimpleManager.php');

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
                // install use tmp directory for cache
                $f3->set('sysConfig[cache]', 'folder=' . sys_get_temp_dir());
                break;
            default:
                throw new \InvalidArgumentException('can not init for system [' . $system . ']');
        }

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

        // 没有自己的目录，比如把 shop 放在根目录了
        if (empty($sysDir)) {

            //数据路径
            if (!$f3->get('sysConfig[data_path_root]')) {
                $f3->set('sysConfig[data_path_root]', realpath($sysPath . '/data'));
            }

            //数据 url prefix
            if (!$f3->get('sysConfig[data_url_prefix]')) {
                $f3->set(
                    'sysConfig[data_url_prefix]',
                    $f3->get('sysConfig[webroot_url_prefix]') . '/data'
                );
            }

        } else {
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
        }

        //图片 image_url_prefix
        if (!$f3->get('sysConfig[image_url_prefix]')) {
            $f3->set('sysConfig[image_url_prefix]', $f3->get('sysConfig[data_url_prefix]'));
        }

        // -------------------- 2. 设置 runtime 路径 --------------------------------------

        // RunTime 路径
        if (!$f3->get('sysConfig[runtime_path]')) {
            $f3->set('sysConfig[runtime_path]', realpath(PROTECTED_PATH . '/Runtime'));
        }

        define('RUNTIME_PATH', $f3->get('sysConfig[runtime_path]'));

        // 设置 Tmp 路径
        $f3->set('TEMP', RUNTIME_PATH . '/Temp/');

        // 设置 Log 路径, BAE3 指定了 log 路径
        $f3->set('LOGS', '/home/bae/log/');

        //开启 Cache 功能
        if ($f3->get('sysConfig[cache]')) {
            $f3->set('CACHE', $f3->get('sysConfig[cache]'));
        } else {
            // 让 F3 自动选择使用最优的 Cache 方案，最差的情况会使用 TEMP/cache 目录文件做缓存
            $f3->set('CACHE', 'true');
        }

        // -------------------- 3. 设置 Smarty --------------------------------------

        // 初始化 smarty 模板引擎
        $smarty->debugging     = $f3->get('sysConfig[smarty_debug]');
        $smarty->force_compile = $f3->get('sysConfig[smarty_force_compile]');
        $smarty->use_sub_dirs  = $f3->get('sysConfig[smarty_use_sub_dirs]');

        //设置 smarty 工作目录
        $smarty->setCompileDir(RUNTIME_PATH . '/Smarty/' . $systemUpperFirst . '/Compile');
        $smarty->setCacheDir(RUNTIME_PATH . '/Smarty/' . $systemUpperFirst . '/Cache');

        // -------------------- 4. 设置 Asset 管理 --------------------------------------

        // asset 路径，用于发布 css, js , 图片 等
        if (!$f3->get('sysConfig[asset_path_root]')) {
            $f3->set('sysConfig[asset_path_root]', realpath($sysPath . '/asset'));
        }

        if (!$f3->get('sysConfig[asset_path_url_prefix]')) {
            $f3->set('sysConfig[asset_path_url_prefix]', $f3->get('sysConfig[webroot_url_prefix]') . '/asset');
        }

        \Core\Asset\SimpleManager::instance(
            $f3->get('sysConfig[asset_path_url_prefix]'),
            $f3->get('sysConfig[asset_path_root]')
        );

        // 开启 asset 智能重新发布功能
        \Core\Asset\SimpleManager::instance()->enableSmartPublish($f3->get('sysConfig[enable_asset_smart_publish]'));

        // asset 文件 url 开启 hash，文件名采用 时间戳.文件名 的方式
        \Core\Asset\SimpleManager::instance()->enableFileHashUrl(
            $f3->get('sysConfig[enable_asset_hash_url]'),
            $f3->get('sysConfig[enable_asset_hash_name]')
        );

        \Core\Asset\ManagerHelper::setAssetManager(\Core\Asset\SimpleManager::instance());
    }

    private function initConsoleEnv()
    {
        global $f3;

        $sysPath          = CONSOLE_PATH;
        $sysDir           = CONSOLE_DIR;
        $systemUpperFirst = 'Console';

        // -------------------- 1. 设置 data 路径 --------------------------------------

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

        // -------------------- 2. 设置 runtime 路径 --------------------------------------

        // RunTime 路径
        if (!$f3->get('sysConfig[runtime_path]')) {
            $f3->set('sysConfig[runtime_path]', realpath(PROTECTED_PATH . '/Runtime'));
        }

        define('RUNTIME_PATH', $f3->get('sysConfig[runtime_path]'));

        // 设置 Tmp 路径
        $f3->set('TEMP', RUNTIME_PATH . '/Temp/');

        // 设置 Log 路径, BAE3 指定了 log 路径
        $f3->set('LOGS', '/home/bae/log/');

        //开启 Cache 功能
        if ($f3->get('sysConfig[cache]')) {
            $f3->set('CACHE', $f3->get('sysConfig[cache]'));
        } else {
            // 让 F3 自动选择使用最优的 Cache 方案，最差的情况会使用 TEMP/cache 目录文件做缓存
            $f3->set('CACHE', 'true');
        }
    }

    public function initCloudEnv($system)
    {

        global $f3;

        $this->system = $system;

        // BAE 系统一些环境变量有问题，我们在这里修复它
        $_SERVER["SERVER_PORT"] = 80;
        $f3->set('PORT', 80);

        // 设置系统运行环境
        Bae3Log::instance()->system = $this->system;

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
                return Bae3Log::instance();
            case CloudHelper::CLOUD_MODULE_DB:
                return Bae3Db::instance();
            case CloudHelper::CLOUD_MODULE_STORAGE:
                return LocalStorage::instance();
            default:
                return null;
        }
    }
} 
