<?php

/**
 * @author QiangYu
 *
 * 系统的 Theme 管理
 *
 * */

namespace Controller\Plugin;

use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Validator;
use Core\Plugin\PluginHelper;
use Core\Plugin\ThemeHelper;

class Theme extends \Controller\AuthController
{

    /**
     * 列出系统中的主题
     *
     * @param $f3
     */
    public function ListTheme($f3)
    {
        global $smarty;

        $themeInstanceArray = ThemeHelper::listPluginArray();

        if (empty($themeInstanceArray)) {
            goto out_display;
        }

        // 构造显示数组
        $themeArray = array();
        foreach ($themeInstanceArray as $themeInstanceItem) {
            $themeInstance     = $themeInstanceItem['pluginInstance'];
            $instanceClassName = get_class($themeInstance);
            $themeArray[]      = array(
                // 显示输出
                'pluginDirName'      => $themeInstanceItem['pluginDirName'],
                'pluginDisplayName'  => $themeInstance->pluginGetDisplayName(),
                'pluginDescText'     => $themeInstance->pluginGetDescText(),
                'pluginUniqueId'     => $instanceClassName::pluginGetUniqueId(),
                // 当前安装的版本
                'installVersion'     => $instanceClassName::getOptionValue('version', true),
                // 当前代码的版本
                'pluginVersion'      => $themeInstance->pluginGetVersion(),
                'pluginConfigureUrl' => $themeInstance->pluginGetConfigureUrl(PluginHelper::SYSTEM_MANAGE),
                // 主题状态
                'pluginIsInstall'    => ThemeHelper::isThemeInstall($themeInstanceItem['pluginDirName']),
                'pluginIsActive'     => ThemeHelper::isThemeActive($themeInstanceItem['pluginDirName']),
                'pluginIsNeedUpdate' => $themeInstance->pluginIsNeedUpdate(),
            );
        }

        $smarty->assign('themeArray', $themeArray);

        out_display:
        $smarty->display('plugin_theme_listtheme.tpl');
    }

    /**
     * 主题安装
     *
     * @param $f3
     */
    public function InstallTheme($f3)
    {

        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_installtheme');

        // 参数验证
        $validator    = new Validator($f3->get('GET'));
        $themeDirName = $validator->required()->validate('themeDirName');

        if (!$this->validate($validator)) {
            goto out;
        }

        $themeInstance = ThemeHelper::loadPluginInstance($themeDirName);
        if (!$themeInstance) {
            $this->addFlashMessage('主题[' . $themeDirName . ']无效');
            goto out;
        }

        // 调用主题操作
        $ret = $themeInstance->pluginInstall(PluginHelper::SYSTEM_ALL);
        if (true !== $ret) {
            $this->addFlashMessage('主题[' . $themeDirName . ']安装失败:' . $ret);
            goto out;
        }

        ThemeHelper::addInstallTheme($themeDirName);
        $this->addFlashMessage('主题安装成功，下一步请“启用”主题让它开始工作');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    /**
     * 主题卸载
     *
     * @param $f3
     */
    public function UninstallTheme($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_uninstalltheme');

        // 参数验证
        $validator    = new Validator($f3->get('GET'));
        $themeDirName = $validator->required()->validate('themeDirName');

        if (!$this->validate($validator)) {
            goto out;
        }

        $themeInstance = ThemeHelper::loadPluginInstance($themeDirName);
        if (!$themeInstance) {
            $this->addFlashMessage('主题[' . $themeDirName . ']无效');
            goto out_uninstall;
        }

        // 调用主题操作
        $ret = $themeInstance->pluginUninstall(PluginHelper::SYSTEM_ALL);
        if (true !== $ret) {
            $this->addFlashMessage('主题[' . $themeDirName . ']卸载失败:' . $ret);
            goto out;
        }

        out_uninstall:

        ThemeHelper::removeInstallTheme($themeDirName);
        $this->addFlashMessage('主题卸载成功，您现在可以安全删除主题目录所有文件');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    /**
     * 主题激活
     *
     * @param $f3
     */
    public function ActivateTheme($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_activatetheme');

        // 参数验证
        $validator    = new Validator($f3->get('GET'));
        $themeDirName = $validator->required()->validate('themeDirName');

        if (!$this->validate($validator)) {
            goto out;
        }

        $themeInstance = ThemeHelper::loadPluginInstance($themeDirName);
        if (!$themeInstance) {
            $this->addFlashMessage('主题[' . $themeDirName . ']无效');
            goto out;
        }

        // 调用主题操作
        $ret = $themeInstance->pluginActivate(PluginHelper::SYSTEM_ALL);
        if (true !== $ret) {
            $this->addFlashMessage('主题[' . $themeDirName . ']启用失败:' . $ret);
            goto out;
        }

        $this->addFlashMessage('主题启用成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    public function DeactivateTheme($f3)
    {
        // 权限检查
        $this->requirePrivilege('manage_plugin_theme_activatetheme');

        // 参数验证
        $validator    = new Validator($f3->get('GET'));
        $themeDirName = $validator->required()->validate('themeDirName');

        if (!$this->validate($validator)) {
            goto out;
        }

        $themeInstance = ThemeHelper::loadPluginInstance($themeDirName);
        if (!$themeInstance) {
            $this->addFlashMessage('主题[' . $themeDirName . ']无效');
            goto out;
        }

        // 调用主题操作
        $ret = $themeInstance->pluginDeactivate(PluginHelper::SYSTEM_ALL);
        if (true !== $ret) {
            $this->addFlashMessage('主题[' . $themeDirName . ']停用失败:' . $ret);
            goto out;
        }

        $this->addFlashMessage('主题停用成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }

    public function UpdateTheme($f3)
    {
        // 权限检查，有权限安装就有权限升级
        $this->requirePrivilege('manage_plugin_theme_installtheme');

        // 参数验证
        $validator    = new Validator($f3->get('GET'));
        $themeDirName = $validator->required()->validate('themeDirName');

        if (!$this->validate($validator)) {
            goto out;
        }

        $themeInstance = ThemeHelper::loadPluginInstance($themeDirName);
        if (!$themeInstance) {
            $this->addFlashMessage('主题[' . $themeDirName . ']无效');
            goto out;
        }

        // 调用主题操作
        $ret = $themeInstance->pluginUpdate();
        if (true !== $ret) {
            $this->addFlashMessage('主题[' . $themeDirName . ']升级失败:' . $ret);
            goto out;
        }

        $this->addFlashMessage('主题升级成功');

        out:
        RouteHelper::reRoute($this, RouteHelper::getRefer(), false);
    }


}
