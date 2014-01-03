<?php
/**
 * @author QiangYu
 *
 * 插件的操作工具，用于插件的安装、卸载、激活、加载 ...
 *
 */

namespace Core\Plugin;


use Core\Plugin\Option\OptionHelper;

class PluginHelper
{

    // 这里定义我们已有的系统
    const SYSTEM_ALL = 'all'; //所有系统
    const SYSTEM_SHOP     = 'shop';
    const SYSTEM_GROUPON  = 'groupon';
    const SYSTEM_MANAGE   = 'manage';
    const SYSTEM_SUPPLIER = 'supplier';
    const SYSTEM_MOBILE   = 'mobile';
    const SYSTEM_CONSOLE  = 'console';
    const SYSTEM_INSTALL  = 'install';

    // 额外的系统
    const SYSTEM_AIMEIDAREN = 'aimeidaren';

    // 已经安装了的插件列表
    const KEY_INSTALL_PLUGIN_ARRAY = 'install_plugin_array';

    // 已经激活了的插件列表
    const KEY_ACTIVE_PLUGIN_ARRAY = 'active_plugin_array';

    /**
     * 插件目录下插件加载的文件名
     *
     * @var string
     */
    public static $pluginLoadFileName = 'plugin_load.php';

    /**
     * 插件的搜索目录，可以设置多个目录
     * @var array
     */
    protected static $pluginDirArray = array();

    /**
     * 插件实例数组，里面存放所有加载上来的插件
     *
     * @var array
     *
     * 格式 array('pluginDirName' => $pluginInstance)
     *
     */
    protected static $pluginInstanceArray = array();

    /**
     * 取得已经安装的插件列表
     *
     * @return array
     */
    public static function getInstallPluginArray()
    {
        // 配置数据缓存 1 个小时
        $installPluginArrayStr = OptionHelper::getOptionValue(PluginHelper::KEY_INSTALL_PLUGIN_ARRAY, 3600);
        if (empty($installPluginArrayStr)) {
            return array();
        }

        return @json_decode($installPluginArrayStr, true);
    }

    /**
     * 判断插件是否已经安装
     *
     * @param string $pluginDirName
     *
     * @return bool
     */
    public static function isPluginInstall($pluginDirName)
    {
        $installPluginArray = PluginHelper::getInstallPluginArray();
        if (empty($installPluginArray)) {
            return false;
        }
        return in_array($pluginDirName, $installPluginArray);
    }

    /**
     * 记录一个插件已经安装
     *
     * @param string $pluginDirName
     */
    public static function addInstallPlugin($pluginDirName)
    {
        $installPluginArray = PluginHelper::getInstallPluginArray();
        if (empty($installPluginArray)) {
            $installPluginArray = array();
        }
        $installPluginArray[] = $pluginDirName;
        OptionHelper::saveOptionValue(PluginHelper::KEY_INSTALL_PLUGIN_ARRAY, json_encode($installPluginArray));
    }

    /**
     * 删除一个插件的安装记录
     *
     * @param string $pluginDirName
     */
    public static function removeInstallPlugin($pluginDirName)
    {
        $installPluginArray = PluginHelper::getInstallPluginArray();
        if (empty($installPluginArray)) {
            $installPluginArray = array();
        }
        // 如果不存在，就不用管
        if (!in_array($pluginDirName, $installPluginArray)) {
            return;
        }
        $installPluginArray = array_diff($installPluginArray, array($pluginDirName));
        OptionHelper::saveOptionValue(PluginHelper::KEY_INSTALL_PLUGIN_ARRAY, json_encode($installPluginArray));
    }

    /**
     * 取得 active 的插件列表
     *
     * @return array
     */
    public static function getActivePluginArray()
    {
        // 配置数据缓存 1 个小时
        $activePluginArrayStr = OptionHelper::getOptionValue(PluginHelper::KEY_ACTIVE_PLUGIN_ARRAY, 3600);
        if (empty($activePluginArrayStr)) {
            return;
        }

        return @json_decode($activePluginArrayStr, true);
    }

    /**
     * 判断插件是否已经激活
     *
     * @param string $pluginDirName
     *
     * @return bool
     */
    public static function isPluginActive($pluginDirName)
    {
        $activePluginArray = PluginHelper::getActivePluginArray();
        if (empty($activePluginArray)) {
            return false;
        }
        return in_array($pluginDirName, $activePluginArray);
    }

    /**
     * 记录一个 plugin  是 active 的
     *
     * @param string $pluginDirName
     */
    public static function addActivePlugin($pluginDirName)
    {
        $activePluginArray = PluginHelper::getActivePluginArray();
        if (empty($activePluginArray)) {
            $activePluginArray = array();
        }
        $activePluginArray[] = $pluginDirName;
        OptionHelper::saveOptionValue(PluginHelper::KEY_ACTIVE_PLUGIN_ARRAY, json_encode($activePluginArray));
    }

    /**
     * 删除一个插件的活跃记录
     *
     * @param string $pluginDirName
     */
    public static function removeActivePlugin($pluginDirName)
    {
        $activePluginArray = PluginHelper::getActivePluginArray();
        if (empty($activePluginArray)) {
            $activePluginArray = array();
        }
        // 如果不存在，就不用管
        if (!in_array($pluginDirName, $activePluginArray)) {
            return;
        }
        $activePluginArray = array_diff($activePluginArray, array($pluginDirName));
        OptionHelper::saveOptionValue(PluginHelper::KEY_ACTIVE_PLUGIN_ARRAY, json_encode($activePluginArray));
    }

    /**
     * 添加一个插件的搜索目录
     *
     * @param string $dir
     */
    public static function addPluginDir($dir)
    {
        $dir = realpath($dir);
        if (!is_dir($dir)) {
            printLog('[' . $dir . '] is not directory', 'PLUGIN', \Core\Log\Base::ERROR);
            return;
        }
        static::$pluginDirArray[] = $dir;
        static::$pluginDirArray   = array_unique(static::$pluginDirArray);
    }


    /**
     * 加载一个插件
     *
     * @return mixed 成功返回插件对象，失败返回 false
     *
     * @param string $pluginDirName 插件的名字，对应着插件的目录名，注意这里区分大小写
     */
    public static function loadPluginInstance($pluginDirName)
    {
        // 如果插件已经加载过了，从这里取得
        if (array_key_exists($pluginDirName, static::$pluginInstanceArray)) {
            return static::$pluginInstanceArray[$pluginDirName];
        }

        foreach (static::$pluginDirArray as $dir) {
            $loadFilePath =
                $dir . DIRECTORY_SEPARATOR . $pluginDirName . DIRECTORY_SEPARATOR . static::$pluginLoadFileName;
            if (is_file($loadFilePath)) {
                $pluginInstance = require_once($loadFilePath);
                if ($pluginInstance && $pluginInstance instanceof IPlugin) {
                    // 设置插件的路径
                    $pluginInstance->setPluginDirName($pluginDirName);
                    $pluginInstance->setPluginDirAbsolutePath($dir . DIRECTORY_SEPARATOR . $pluginDirName);
                    return $pluginInstance;
                }
                return false;
            }
        }
        return false;
    }

    /**
     * 搜索插件目录列出 插件 列表
     *
     * @return array
     *
     * 格式为  array(
     *      array('pluginDirName' => 插件目录名， 'pluginInstance' => 插件对象)
     *      array('pluginDirName' => 插件目录名， 'pluginInstance' => 插件对象)
     * )
     *
     */
    public static function listPluginArray()
    {

        $pluginArray = array();

        foreach (static::$pluginDirArray as $dir) {

            if (!is_dir($dir)) {
                continue;
            }

            if ($dirHandle = opendir($dir)) {
                while (false !== ($pluginDirName = readdir($dirHandle))) {

                    // 插件肯定有自己的目录，如果不是目录就返回
                    if ('.' == $pluginDirName || '..' == $pluginDirName || !is_dir($dir . '/' . $pluginDirName)) {
                        continue;
                    }

                    $pluginInstance = static::loadPluginInstance($pluginDirName);
                    if ($pluginInstance) {
                        // 加入插件列表中
                        $pluginArray[] = array('pluginDirName' => $pluginDirName, 'pluginInstance' => $pluginInstance);
                    }

                }
                closedir($dirHandle);
            }

        }

        return $pluginArray;
    }

    /**
     * 加载所有 active 的插件
     *
     * @param string $system 系统的标识
     *
     */
    public static function loadActivePlugin($system)
    {

        $activePluginArray = PluginHelper::getActivePluginArray();
        if (empty($activePluginArray)) {
            return;
        }

        foreach ($activePluginArray as $activePlugin) {
            $instance = static::loadPluginInstance($activePlugin);
            if (!$instance) {
                printLog('load plugin [' . $activePlugin . '] failed', 'PLUGIN', \Core\Log\Base::ERROR);
                continue;
            }

            //调用插件的 load 方法做初始化
            if (true !== $instance->pluginLoad($system)) {
                // 如果加载失败，就不要做后面的操作了
                unset($instance);
                continue;
            }

            global $f3;
            if ($f3->get('DEBUG')) {
                printLog('load plugin [' . $activePlugin . '] success', 'PLUGIN', \Core\Log\Base::DEBUG);
            }

            // 把插件放入到列表中
            static::$pluginInstanceArray[$activePlugin] = $instance;
            unset($instance);
        }
    }

    /**
     * 调用插件的 action 操作，插件在这里注册各种 filter
     *
     * @param string $system 系统的标识
     *
     */
    public static function doActivePluginAction($system)
    {

        if (empty(static::$pluginInstanceArray)) {
            return;
        }
        foreach (static::$pluginInstanceArray as $instance) {
            $ret = $instance->pluginAction($system);
            printLog(
                'call pluginAction ' . get_class($instance) . ' ['
                . $system . '] return [' . var_export($ret, true) . ']',
                'PLUGIN',
                \Core\Log\Base::DEBUG
            );
        }
    }

    /**
     * 释放所有的 plugin ，某些 plugin 可能需要做一些资源的释放工作
     *
     * @param string $system 系统的标识
     *
     */
    public static function unloadActivePlugin($system)
    {
        if (empty(static::$pluginInstanceArray)) {
            return;
        }
        foreach (static::$pluginInstanceArray as $instance) {
            printLog(
                'call pluginUnload ' . get_class($instance) . ' [' . $system . ']',
                'PLUGIN',
                \Core\Log\Base::DEBUG
            );
            $instance->pluginUnload($system);
        }
    }

    /**
     * 根据插件的 uniqueId 取得插件实例，其它插件可以通过这个接口取得别的插件的实例，从而实现“插件相互之间通讯”
     *
     * @param string $uniqueId
     *
     * @return IPlugin|null
     */
    public static function getPluginInstanceByUniqueId($uniqueId)
    {
        foreach (static::$pluginInstanceArray as $instance) {
            if ($uniqueId == call_user_func(array(get_class($instance), 'pluginGetUniqueId'))) {
                return $instance;
            }
        }
        return null;
    }
}