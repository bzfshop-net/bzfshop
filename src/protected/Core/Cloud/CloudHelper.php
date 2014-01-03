<?php
/**
 * @author QiangYu
 *
 * 云平台处理的 Helper 类
 *
 */

namespace Core\Cloud;


class CloudHelper
{

    // 云引擎的模块定义

    // 日志系统
    const CLOUD_MODULE_Log = 'Log';
    // 文件系统操作模块
    const CLOUD_MODULE_FILESYSTEM = 'FileSystem';
    // 数据库系统
    const CLOUD_MODULE_DB = 'Db';

    // 云引擎定义
    const CLOUD_ENGINE_LOCAL = 'Local';
    const CLOUD_ENGINE_SAE   = 'Sae';

    /**
     * 当前的云平台引擎
     */
    private static $cloudEngine = null;

    // 当前使用的引擎
    public static $currentEngineStr = null;

    public static function detectCloudEnv($system)
    {
        global $f3;

        $cloudEngine = null;

        self::$currentEngineStr = $f3->get('sysConfig[cloudEngine]');

        // 如果用户没有配置，我们这里自动检测云平台环境
        if (empty($cloudEngineStr)) {
            // 缺省为 Local
            self::$currentEngineStr = self::CLOUD_ENGINE_LOCAL;

            if (function_exists('sae_debug')) {
                self::$currentEngineStr = self::CLOUD_ENGINE_SAE;
                goto init_engine;
            }
        }

        init_engine:

        $engineClassName = '\Core\Cloud\\' . self::$currentEngineStr . '\\' . self::$currentEngineStr . 'Engine';
        if (!class_exists($engineClassName)) {
            throw new \InvalidArgumentException('cloud engine [' . $engineClassName . '] does not exist');
        }

        $cloudEngine = new $engineClassName();
        if (!$cloudEngine instanceof ICloudEngine) {
            throw new \InvalidArgumentException('cloud engine [' . $engineClassName
                . '] is not instance of ICloudEngine');
        }

        // 初始化 云引擎
        if (!$cloudEngine->initEnv($system)) {
            throw new \InvalidArgumentException('cloudEngine initEnv fail');
        }

        self::$cloudEngine = $cloudEngine;
    }


    /**
     * 取得云平台的操作模块
     *
     * @param string $module
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public static function getCloudModule($module)
    {
        if (!self::$cloudEngine) {
            throw new \InvalidArgumentException('cloudEngine does not init properly');
        }

        $module = self::$cloudEngine->getCloudModule($module);

        if (!$module) {
            throw \InvalidArgumentException(
                'get module [' . $module . '] failed from engine [' . get_class(self::$cloudEngine) . ']'
            );
        }

        return $module;
    }

} 