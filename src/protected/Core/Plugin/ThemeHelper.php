<?php
/**
 * @author QiangYu
 *
 * 主题操作类，这里操作系统所有可以设置的主题
 *
 * 主题其实就是一个复杂的 插件
 *
 */

namespace Core\Plugin;


use Core\Plugin\Option\OptionHelper;

class ThemeHelper extends PluginHelper
{

    // 已经安装了的主题列表
    const KEY_INSTALL_THEME_ARRAY = 'install_theme_array';

    // 定义了不同系统对应的主题
    const SYSTEM_SHOP_THEME     = 'system_shop_theme';
    const SYSTEM_GROUPON_THEME  = 'system_groupon_theme';
    const SYSTEM_MANAGE_THEME   = 'system_manage_theme';
    const SYSTEM_SUPPLIER_THEME = 'system_supplier_theme';
    const SYSTEM_MOBILE_THEME   = 'system_mobile_theme';

    // 其它主题
    const SYSTEM_AIMEIDAREN_THEME = 'system_aimeidaren_theme';

    // 当前系统加载的系统主题
    private static $currentSystemThemeInstance = null;

    /**
     * 取得当前系统主题对象
     *
     * @return object
     */
    public static function getCurrentSystemThemeInstance()
    {
        return self::$currentSystemThemeInstance;
    }

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
     *
     * 返回已经安装的 Theme 列表
     *
     * @return array
     */
    public static function getInstallThemeArray()
    {
        // 配置数据缓存 1 个小时
        $installThemeArrayStr = OptionHelper::getOptionValue(ThemeHelper::KEY_INSTALL_THEME_ARRAY, 3600);
        if (empty($installThemeArrayStr)) {
            return array();
        }

        return @json_decode($installThemeArrayStr, true);
    }

    /**
     * 判断主题是否已经安装
     *
     * @param string $themeDirName
     *
     * @return bool
     */
    public static function isThemeInstall($themeDirName)
    {
        $installThemeArray = ThemeHelper::getInstallThemeArray();
        if (empty($installThemeArray)) {
            return false;
        }
        return in_array($themeDirName, $installThemeArray);
    }

    /**
     * 添加一个主题到安装列表中
     *
     * @param string $themeDirName
     */
    public static function addInstallTheme($themeDirName)
    {
        $installThemeArray = ThemeHelper::getInstallThemeArray();
        if (empty($installThemeArray)) {
            $installThemeArray = array();
        }
        $installThemeArray[] = $themeDirName;
        OptionHelper::saveOptionValue(ThemeHelper::KEY_INSTALL_THEME_ARRAY, json_encode($installThemeArray));
    }

    /**
     * 把一个主题从安装列表中删除
     *
     * @param string $themeDirName
     */
    public static function removeInstallTheme($themeDirName)
    {
        $installThemeArray = ThemeHelper::getInstallThemeArray();
        if (empty($installThemeArray)) {
            $installThemeArray = array();
        }
        // 如果不存在，就不用管
        if (!in_array($themeDirName, $installThemeArray)) {
            return;
        }
        $installThemeArray = array_diff($installThemeArray, array($themeDirName));
        OptionHelper::saveOptionValue(ThemeHelper::KEY_INSTALL_THEME_ARRAY, json_encode($installThemeArray));
    }


    /**
     * 判断主题是否已经使用 active
     *
     * @param string $themeDirName
     *
     * @return bool
     */
    public static function isThemeActive($themeDirName)
    {
        if ($themeDirName === 'Manage') {
            // 管理后台永远 active
            return true;
        }

        if ($themeDirName === OptionHelper::getOptionValue(ThemeHelper::SYSTEM_SHOP_THEME, 3600)) {
            return true;
        }
        if ($themeDirName === OptionHelper::getOptionValue(ThemeHelper::SYSTEM_GROUPON_THEME, 3600)) {
            return true;
        }
        if ($themeDirName === OptionHelper::getOptionValue(ThemeHelper::SYSTEM_SUPPLIER_THEME, 3600)) {
            return true;
        }
        if ($themeDirName === OptionHelper::getOptionValue(ThemeHelper::SYSTEM_MOBILE_THEME, 3600)) {
            return true;
        }
        if ($themeDirName === OptionHelper::getOptionValue(ThemeHelper::SYSTEM_MANAGE_THEME, 3600)) {
            return true;
        }

        // 其它主题
        if ($themeDirName === OptionHelper::getOptionValue(ThemeHelper::SYSTEM_AIMEIDAREN_THEME, 3600)) {
            return true;
        }

        return false;
    }

    /**
     * 取得 active 的主题数组
     *
     * @return array
     */
    public static function getActiveThemeArray()
    {

        $activeThemeArray = array();

        if ($themeDirName = OptionHelper::getOptionValue(ThemeHelper::SYSTEM_SHOP_THEME, 3600)) {
            $activeThemeArray[] = $themeDirName;
        }
        if ($themeDirName = OptionHelper::getOptionValue(ThemeHelper::SYSTEM_GROUPON_THEME, 3600)) {
            $activeThemeArray[] = $themeDirName;
        }
        if ($themeDirName = OptionHelper::getOptionValue(ThemeHelper::SYSTEM_SUPPLIER_THEME, 3600)) {
            $activeThemeArray[] = $themeDirName;
        }
        if ($themeDirName = OptionHelper::getOptionValue(ThemeHelper::SYSTEM_MOBILE_THEME, 3600)) {
            $activeThemeArray[] = $themeDirName;
        }
        if ($themeDirName = OptionHelper::getOptionValue(ThemeHelper::SYSTEM_MANAGE_THEME, 3600)) {
            $activeThemeArray[] = $themeDirName;
        }

        // 其它主题
        if ($themeDirName = OptionHelper::getOptionValue(ThemeHelper::SYSTEM_AIMEIDAREN_THEME, 3600)) {
            $activeThemeArray[] = $themeDirName;
        }

        return $activeThemeArray;
    }

    /**
     * 取得系统 Theme 的设置
     *
     * @param string $systemTheme
     *
     * @return mixed
     */
    public static function getSystemThemeDirName($systemTheme)
    {
        return OptionHelper::getOptionValue($systemTheme, 3600);
    }

    /**
     * 设置系统的主题设置
     *
     * @param string $systemTheme
     * @param string $themeDirName
     */
    public static function setSystemThemeDirName($systemTheme, $themeDirName)
    {
        OptionHelper::saveOptionValue($systemTheme, $themeDirName);
    }

    /**
     * 加载系统主题
     *
     * @param string $systemTheme
     *
     * @return mixed|null
     */
    public static function loadSystemTheme($systemTheme)
    {
        // 取得系统 Theme 的设置
        $themeDirName = ThemeHelper::getSystemThemeDirName($systemTheme);
        if (empty($themeDirName)) {
            return false;
        }
        // 加载主题插件
        self::$currentSystemThemeInstance = ThemeHelper::loadPluginInstance($themeDirName);
        return self::$currentSystemThemeInstance;
    }

    /**
     * 加载所有安装了的 theme，一般只有 manage 系统才需要这个功能
     *
     * @param string $system
     */
    public static function loadInstallTheme($system)
    {

        $installThemeArray = ThemeHelper::getInstallThemeArray();
        if (empty($installThemeArray)) {
            return;
        }

        foreach ($installThemeArray as $installTheme) {
            $instance = static::loadPluginInstance($installTheme);
            if (!$instance) {
                printLog('theme [' . $installTheme . '] does not exist', 'PLUGIN', \Core\Log\Base::ERROR);
                continue;
            }

            //调用插件的 load 方法做初始化
            if (true !== $instance->pluginLoad($system)) {
                // 如果加载失败，就不要做后面的操作了
                printLog('theme [' . $installTheme . '] load failed', 'PLUGIN', \Core\Log\Base::ERROR);
                unset($instance);
                continue;
            }

            global $f3;
            if ($f3->get('DEBUG')) {
                printLog('load theme [' . $installTheme . '] success', 'PLUGIN', \Core\Log\Base::DEBUG);
            }

            // 把插件放入到列表中
            static::$pluginInstanceArray[$installTheme] = $instance;
            unset($instance);
        }
    }


    /**
     * 加载所有 Active 的 theme
     *
     * @param string $system
     * @param string $excludeSystemTheme 排除某个系统
     */
    public static function loadActiveTheme($system, $excludeSystemTheme = null)
    {

        $activeThemeArray = ThemeHelper::getActiveThemeArray();
        if (empty($activeThemeArray)) {
            return;
        }

        foreach ($activeThemeArray as $activeTheme) {
            // 排除某个系统
            if ($excludeSystemTheme == $activeTheme) {
                printLog('exclude theme [' . $activeTheme . ']', 'PLUGIN', \Core\Log\Base::DEBUG);
                continue;
            }

            $instance = static::loadPluginInstance($activeTheme);
            if (!$instance) {
                printLog('theme [' . $activeTheme . '] does not exist', 'PLUGIN', \Core\Log\Base::ERROR);
                continue;
            }

            //调用插件的 load 方法做初始化
            if (true !== $instance->pluginLoad($system)) {
                // 如果加载失败，就不要做后面的操作了
                printLog('load theme [' . $activeTheme . '] failed', 'PLUGIN', \Core\Log\Base::DEBUG);
                unset($instance);
                continue;
            }

            global $f3;
            if ($f3->get('DEBUG')) {
                printLog('load theme [' . $activeTheme . '] success', 'PLUGIN', \Core\Log\Base::DEBUG);
            }

            // 把插件放入到列表中
            static::$pluginInstanceArray[$activeTheme] = $instance;
            unset($instance);
        }
    }

    /**
     * 调用 theme 的 action 方法
     *
     * @param string $system
     */
    public static function doInstallThemeAction($system)
    {
        static::doActivePluginAction($system);
    }

    /**
     * 释放 theme，不确定是否某些 theme 需要释放只有
     *
     * @param string $system
     */
    public static function unloadInstallTheme($system)
    {
        static::unloadActivePlugin($system);
    }

    /**
     * 调用 theme 的 action 方法
     *
     * @param string $system
     */
    public static function doActiveThemeAction($system)
    {
        static::doActivePluginAction($system);
    }

    /**
     * 释放 theme，不确定是否某些 theme 需要释放只有
     *
     * @param string $system
     */
    public static function unloadActiveTheme($system)
    {
        static::unloadActivePlugin($system);
    }
}