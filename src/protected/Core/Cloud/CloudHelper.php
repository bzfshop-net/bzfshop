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

    /**
     * 当前的云平台引擎
     */
    private static $cloudEngine = null;

    public static function detectCloudEnv($system)
    {
        global $f3;

        $cloudEngine = null;

        $cloudEngineStr = $f3->get('sysConfig[cloudEngine]');

        // 根据用户的配置初始化 云引擎
        if (!empty($cloudEngineStr)) {
            $engineClassName = '\Core\Cloud\\' . $cloudEngineStr . '\\' . $cloudEngineStr . 'Engine';
            if (!class_exists($engineClassName)) {
                throw new \InvalidArgumentException('cloud engine [' . $engineClassName . '] does not exist');
            }

            $cloudEngine = new $engineClassName();
            if (!$cloudEngine instanceof ICloudEngine) {
                throw new \InvalidArgumentException('cloud engine [' . $engineClassName
                    . '] is not instance of ICloudEngine');
            }

            goto init_engine;
        }

        // 如果用户没有配置，我们这里自动检测云平台环境

        //TODO: do it later

        init_engine: // 这里初始化引擎

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