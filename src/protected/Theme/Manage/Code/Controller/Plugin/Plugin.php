<?php

/**
 * @author QiangYu
 *
 * 数据统计首页
 *
 * */

namespace Controller\Plugin;

use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Plugin\PluginHelper;

class Plugin extends \Controller\AuthController
{

    /**
     * 列出系统中的插件
     *
     * @param $f3
     */
    public function ListPlugin($f3)
    {
        global $smarty;

        $pluginInstanceArray = PluginHelper::listPluginArray();

        if (empty($pluginInstanceArray)) {
            goto out_display;
        }

        // 构造显示数组
        $pluginArray = array();
        foreach ($pluginInstanceArray as $pluginInstanceItem) {
            $pluginInstance    = $pluginInstanceItem['pluginInstance'];
            $instanceClassName = get_class($pluginInstance);
            $pluginArray[]     = array(
                // 显示输出
                'pluginDirName'      => $pluginInstanceItem['pluginDirName'],
                'pluginDisplayName'  => $pluginInstance->pluginGetDisplayName(),
                'pluginDescText'     => $pluginInstance->pluginGetDescText(),
                'pluginUniqueId'     => $instanceClassName::pluginGetUniqueId(),
                // 当前安装的版本
                'installVersion'     => $instanceClassName::getOptionValue('version', true),
                // 当前代码的版本
                'pluginVersion'      => $pluginInstance->pluginGetVersion(),
                'pluginConfigureUrl' => $pluginInstance->pluginGetConfigureUrl(PluginHelper::SYSTEM_MANAGE),
                // 插件状态
                'pluginIsInstall'    => PluginHelper::isPluginInstall($pluginInstanceItem['pluginDirName']),
                'pluginIsActive'     => PluginHelper::isPluginActive($pluginInstanceItem['pluginDirName']),
                'pluginIsNeedUpdate' => $pluginInstance->pluginIsNeedUpdate(),

            );
        }

        $smarty->assign('pluginArray', $pluginArray);

        out_display:
        $smarty->display('plugin_plugin_listplugin.tpl');
    }

    /**
     * 插件安装
     *
     * @param $f3
     */
    public function InstallPlugin($f3)
    {

        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_installplugin');

        // 参数验证
        $validator     = new Validator($f3->get('GET'));
        $pluginDirName = $validator->required()->validate('pluginDirName');

        if (!$this->validate($validator)) {
            goto out;
        }

        $pluginInstance = PluginHelper::loadPluginInstance($pluginDirName);
        if (!$pluginInstance) {
            $this->addFlashMessage('插件[' . $pluginDirName . ']无效');
            goto out;
        }

        // 调用插件操作
        $ret = $pluginInstance->pluginInstall(PluginHelper::SYSTEM_ALL);
        if (true !== $ret) {
            $this->addFlashMessage('插件[' . $pluginDirName . ']安装失败:' . $ret);
            goto out;
        }

        PluginHelper::addInstallPlugin($pluginDirName);
        $this->addFlashMessage('插件安装成功，下一步请“启用”插件让它开始工作');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    /**
     * 插件卸载
     *
     * @param $f3
     */
    public function UninstallPlugin($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_uninstallplugin');

        // 参数验证
        $validator     = new Validator($f3->get('GET'));
        $pluginDirName = $validator->required()->validate('pluginDirName');

        if (!$this->validate($validator)) {
            goto out;
        }

        $pluginInstance = PluginHelper::loadPluginInstance($pluginDirName);
        if (!$pluginInstance) {
            $this->addFlashMessage('插件[' . $pluginDirName . ']无效');
            goto out_uninstall;
        }

        // 调用插件操作
        $ret = $pluginInstance->pluginUninstall(PluginHelper::SYSTEM_ALL);
        if (true !== $ret) {
            $this->addFlashMessage('插件[' . $pluginDirName . ']卸载失败:' . $ret);
            goto out;
        }

        out_uninstall:

        PluginHelper::removeInstallPlugin($pluginDirName);
        $this->addFlashMessage('插件卸载成功，您现在可以安全删除插件目录所有文件');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    /**
     * 插件激活
     *
     * @param $f3
     */
    public function ActivatePlugin($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_activateplugin');

        // 参数验证
        $validator     = new Validator($f3->get('GET'));
        $pluginDirName = $validator->required()->validate('pluginDirName');

        if (!$this->validate($validator)) {
            goto out;
        }

        $pluginInstance = PluginHelper::loadPluginInstance($pluginDirName);
        if (!$pluginInstance) {
            $this->addFlashMessage('插件[' . $pluginDirName . ']无效');
            goto out;
        }

        // 调用插件操作
        $ret = $pluginInstance->pluginActivate(PluginHelper::SYSTEM_ALL);
        if (true !== $ret) {
            $this->addFlashMessage('插件[' . $pluginDirName . ']启用失败:' . $ret);
            goto out;
        }

        PluginHelper::addActivePlugin($pluginDirName);
        $this->addFlashMessage('插件启用成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    /**
     * 取消插件激活
     *
     * @param $f3
     */
    public function DeactivatePlugin($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_plugin_deactivateplugin');

        // 参数验证
        $validator     = new Validator($f3->get('GET'));
        $pluginDirName = $validator->required()->validate('pluginDirName');

        if (!$this->validate($validator)) {
            goto out;
        }

        $pluginInstance = PluginHelper::loadPluginInstance($pluginDirName);
        if (!$pluginInstance) {
            $this->addFlashMessage('插件[' . $pluginDirName . ']无效');
            goto out_deactivate;
        }

        // 调用插件操作
        $ret = $pluginInstance->pluginDeactivate(PluginHelper::SYSTEM_ALL);
        if (true !== $ret) {
            $this->addFlashMessage('插件[' . $pluginDirName . ']停用失败:' . $ret);
            goto out;
        }

        out_deactivate:

        PluginHelper::removeActivePlugin($pluginDirName);
        $this->addFlashMessage('插件停用成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    public function UpdatePlugin($f3)
    {
        // 权限检查，有权限安装就有权限升级
        $this->requirePrivilege('manage_plugin_plugin_installplugin');

        // 参数验证
        $validator     = new Validator($f3->get('GET'));
        $pluginDirName = $validator->required()->validate('pluginDirName');

        if (!$this->validate($validator)) {
            goto out;
        }

        $pluginInstance = PluginHelper::loadPluginInstance($pluginDirName);
        if (!$pluginInstance) {
            $this->addFlashMessage('插件[' . $pluginDirName . ']无效');
            goto out;
        }

        // 调用插件操作
        $ret = $pluginInstance->pluginUpdate();
        if (true !== $ret) {
            $this->addFlashMessage('插件[' . $pluginDirName . ']升级失败:' . $ret);
            goto out;
        }

        $this->addFlashMessage('插件升级成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }


}
