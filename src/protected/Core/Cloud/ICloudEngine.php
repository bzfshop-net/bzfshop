<?php
/**
 * @author QiangYu
 *
 * 云平台的抽象接口，用于实现不同的云平台基础服务，比如 SAE，BAE，...
 *
 */

namespace Core\Cloud;


interface ICloudEngine
{

    /**
     * 检查当前环境是否是“这个” 云平台，比如 SAE 检查是否是自己
     *
     * @return boolean
     */
    public function detectCloudEnv();

    /**
     * 初始化云平台环境
     *
     * @param string $system 当前系统，定义在 PluginHelper 里面， 比如 SYSTEM_SHOP
     *
     * @return boolean
     */
    public function initCloudEnv($system);

    /**
     * 每个云平台都会提供一些模块，比如 FetchUrl，Storage 之类，这里是用于取得对应模块的处理程序
     *
     * @param string $module
     *
     * @return mixed
     */
    public function getCloudModule($module);
} 