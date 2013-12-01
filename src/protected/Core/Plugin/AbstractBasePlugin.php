<?php
/**
 * @author QiangYu
 *
 * plugin 接口的一个抽象实现，你可以从这个类继承从而生成自己的插件
 *
 */

namespace Core\Plugin;


use Core\Plugin\Option\OptionHelper;

/**
 * 从 Prefab 继承，这样就是单例
 *
 * Class AbstractBasePlugin
 *
 * @package Core\Plugin
 */
abstract class AbstractBasePlugin extends \Prefab implements IPlugin
{
    /**
     * 插件配置文件的名字
     */
    public static $pluginConfigFile = 'config.php';

    /**
     * 插件升级程序所在的目录名
     */
    public static $pluginUpdateCodeDir = 'update';

    /**
     * 插件版本信息保存的 key
     */
    public static $pluginVersionKey = 'version';

    /**
     * 插件目录的名字
     *
     * @var null
     */
    protected $pluginDirName = null;

    /**
     * 插件目录的绝对路径
     *
     * @var null
     */
    protected $pluginDirAbsolutePath = null;

    // 使用 UUID 作为插件的唯一 ID
    protected static $pluginUniqueId = __CLASS__;

    // 商品过滤
    protected $goodsFilterSystemArray = array();

    // 订单过滤
    protected $orderFilterSystemArray = array();

    /**
     * 取得商品需要过滤的系统列表
     *
     * @return array
     */
    public function getGoodsFilterSystemArray()
    {
        return $this->goodsFilterSystemArray;
    }

    /**
     * 取得订单需要过滤的系统列表
     *
     * @return array
     */
    public function getOrderFilterSystemArray()
    {
        return $this->orderFilterSystemArray;
    }

    /**
     * 允许哪些系统加载本插件
     */
    protected static $systemAllowLoad = array(PluginHelper::SYSTEM_MANAGE);

    public function getPluginDirName()
    {
        return $this->pluginDirName;
    }

    public function setPluginDirName($pluginDirName)
    {
        return $this->pluginDirName = $pluginDirName;
    }

    public function getPluginDirAbsolutePath()
    {
        return $this->pluginDirAbsolutePath;
    }

    public function setPluginDirAbsolutePath($pluginDirAbsolutePath)
    {
        $this->pluginDirAbsolutePath = $pluginDirAbsolutePath;
    }

    public static function pluginGetUniqueId()
    {
        return static::$pluginUniqueId;
    }

    public function pluginIsNeedUpdate()
    {
        // 没有 version key，一律认为必须升级
        if (!static::isOptionValueExist(static::$pluginVersionKey)) {
            return true;
        }

        // 根据 version 来判断是否需要升级
        return ($this->pluginGetVersion() > static::getOptionValue(static::$pluginVersionKey));
    }

    /**
     * 我们这里实现了一个通用的升级方法
     *
     * @return bool|string
     */
    public function pluginUpdate()
    {
        $errorMessge = null;

        $updateDir  = $this->getPluginDirAbsolutePath() . DIRECTORY_SEPARATOR . static::$pluginUpdateCodeDir;
        $versionKey = 'version';

        // 检查升级目录是否存在
        if (!is_dir($updateDir) || !($dirHandle = opendir($updateDir))) {
            $errorMessge = '目录 [' . $updateDir . '] 不存在或者无法打开';
            goto out_fail;
        }

        // 取得升级程序列表
        $updateFileArray = array();
        while (false !== ($entry = readdir($dirHandle))) {
            if (is_file($updateDir . DIRECTORY_SEPARATOR . $entry)) {
                $updateFileArray[] = $entry;
            }
        }
        closedir($dirHandle);
        unset($dirHandle);

        if (empty($updateFileArray)) {
            $errorMessge = '目录 [' . $updateDir . '] 不存在任何升级程序';
            goto out_fail;
        }

        // 排序，按照文件名顺序升级，比如 update_1.0.0.php , update_1.0.1.php, update_1.0.2.php
        sort($updateFileArray);

        // 取得当前 version
        $currentVersion = static::getOptionValue($versionKey, true);

        // 按照版本顺序，挨个做升级
        $updateInstanceArray = array();
        foreach ($updateFileArray as $updateFile) {

            $updateFilePath = $updateDir . DIRECTORY_SEPARATOR . $updateFile;
            $updateInstance = require_once($updateFilePath);

            if (!$updateInstance || !($updateInstance instanceof AbstractUpdate)) {
                $errorMessge = '升级程序非法 [' . $updateFilePath . ']';
                goto out_rollback;
            }

            // 不能升级这个版本
            if (!$updateInstance->isVersionAllowUpdate($currentVersion)) {
                continue;
            }

            // instance 入栈
            array_unshift($updateInstanceArray, $updateInstance);

            // 做升级
            printLog('do plugin update [' . $updateFilePath . ']', 'PLUGIN');
            if (!$updateInstance->doUpdate($currentVersion)) {
                // 当前升级失败，回滚
                $errorMessge = '程序升级失败 [' . $updateFilePath . ']';
                goto out_rollback;
            }

            // 更新 currentVersion
            $currentVersion = static::getOptionValue($versionKey, true);
            printLog('plugin currentVersion [' . $currentVersion . ']', 'PLUGIN');
        }

        // 升级成功，从这里退出
        return true;

        out_rollback:
        // 升级失败，挨个回滚
        foreach ($updateInstanceArray as $updateInstance) {
            printLog('do plugin rollback [' . $updateFilePath . ']', 'PLUGIN');
            $updateInstance->doRollBack($currentVersion);
            // 更新 currentVersion
            $currentVersion = static::getOptionValue($versionKey, true);
            printLog('plugin currentVersion [' . $currentVersion . ']', 'PLUGIN');
        }

        out_fail:
        // 返回错误信息
        return $errorMessge;
    }

    /**
     *
     * 加载配置文件信息，从配置文件里面读取到插件的所有配置
     *
     */
    public function loadConfigFile()
    {
        static $configArray = null;
        if (!$configArray) {
            $configFilePath = $this->getPluginDirAbsolutePath() . DIRECTORY_SEPARATOR . static::$pluginConfigFile;
            if (!is_file($configFilePath)) {
                throw new \InvalidArgumentException('config file [' . $configFilePath . '] does not exist');
            }
            $configArray = require_once($configFilePath);
        }
        return $configArray;
    }

    public function pluginGetDisplayName()
    {
        $configArray = $this->loadConfigFile();
        return $configArray['display_name'];
    }

    public function pluginGetDescText()
    {
        $configArray = $this->loadConfigFile();
        return $configArray['description'];
    }

    public function pluginGetVersion()
    {
        $configArray = $this->loadConfigFile();
        return $configArray[static::$pluginVersionKey];
    }

    public function pluginInstall($system)
    {
        // 生成我们需要的配置信息
        $configArray = $this->loadConfigFile();
        foreach ($configArray as $configName => $configValue) {
            self::saveOptionValue($configName, $configValue);
        }
        return true;
    }

    public function pluginUninstall($system)
    {
        // 删除我们自己的配置信息
        $configArray = $this->loadConfigFile();
        foreach ($configArray as $configName => $configValue) {
            self::removeOptionValue($configName);
        }
        return true;
    }

    public function pluginLoad($system)
    {
        return
            in_array($system, static::$systemAllowLoad) || in_array(PluginHelper::SYSTEM_ALL, static::$systemAllowLoad);
    }

    public function pluginUnload($system)
    {
        return true;
    }

    public function pluginActivate($system)
    {
        return false;
    }

    public function pluginDeactivate($system)
    {
        return false;
    }

    public function pluginGetConfigureUrl($system)
    {
        return null;
    }


    /**
     * 生成唯一的 optionKey，防止和别的插件冲突
     *
     * @param string $optionKey
     *
     * @return string
     */
    public static function makeUniqueOptionKey($optionKey)
    {
        static $optionKeyPrefix = null;
        if (null == $optionKeyPrefix) {
            // 用插件的 UniqueId 做 md5
            $optionKeyPrefix = md5(static::$pluginUniqueId);
        }
        return $optionKeyPrefix . '_' . $optionKey;
    }

    /**
     * 判断 Option 是否存在
     *
     * @param string $optionKey
     *
     * @return bool
     */
    public static function isOptionValueExist($optionKey)
    {
        $optionKey = static::makeUniqueOptionKey($optionKey);
        return OptionHelper::isOptionValueExist($optionKey);
    }

    /**
     * 取得插件的 Option 值
     *
     * @param string $optionKey
     * @param bool   $ignoreError
     * true : 如果 $optionKey 不存在则返回  null
     * false:如果 $optionKey 不存在 Debug 模式下会抛出异常
     *
     * @return string
     */
    public static function getOptionValue($optionKey, $ignoreError = false)
    {
        if ($ignoreError && !static::isOptionValueExist($optionKey)) {
            return null;
        }

        $optionKey = static::makeUniqueOptionKey($optionKey);
        // 缓存 1 小时
        return OptionHelper::getOptionValue($optionKey, 3600);
    }

    /**
     * 设置 option 的值
     *
     * @param string $optionKey
     * @param string $optionValue
     */
    public static function saveOptionValue($optionKey, $optionValue)
    {
        $optionKey = static::makeUniqueOptionKey($optionKey);
        OptionHelper::saveOptionValue($optionKey, $optionValue);
    }

    /**
     * 删除 option 的值
     *
     * @param string $optionKey
     */
    public static function removeOptionValue($optionKey)
    {
        $optionKey = static::makeUniqueOptionKey($optionKey);
        OptionHelper::removeOptionValue($optionKey);
    }


    /**
     * 实现了 Command 接口，提供了几个基本的命令实现
     *
     * @param string $command
     * @param array  $paramArray
     *
     * @return mixed|void
     */
    public function pluginCommand($command, $paramArray = array())
    {

        // 可以通过 command 调用插件已有的方法
        if (method_exists($this, $command)) {
            return call_user_func_array(array($this, $command), $paramArray);
        }

        // 可以通过 command 访问对象的属性
        if (property_exists($this, $command)) {
            return $this->$command;
        }

        return null;
    }

}