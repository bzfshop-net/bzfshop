<?php
/**
 * @author QiangYu
 *
 * 插件接口的定义，所有插件程序必须实现这个接口
 *
 */

namespace Core\Plugin;


interface IPlugin
{
    /**
     * 返回插件的名字，用于显示
     *
     * @return string
     */
    public function pluginGetDisplayName();

    /**
     * 返回插件的描述文本，可以返回 html ，比如 <ul><li>...</li></ul>，但是不要太长了，不然显示不下
     *
     * @return string
     */
    public function pluginGetDescText();

    /**
     * 返回插件的唯一 ID 用于插件的标识，我们建议使用 uuid 之类的生成值，确保没有 2 个插件会一样
     *
     * 插件标识用于区分不同插件的数据，切记一定要唯一
     *
     * 注意：这个方法是 static 定义
     *
     * @return string
     */
    public static function pluginGetUniqueId();

    /**
     * 返回插件的 版本号，比如 1.0.1
     *
     * @return string
     */
    public function pluginGetVersion();

    /**
     * 返回插件是否需要升级
     *
     * @return boolean  需要升级返回 true，不需要返回 false
     */
    public function pluginIsNeedUpdate();

    /**
     * 插件程序升级
     *
     * @return string|boolean  成功安装返回 true，失败返回 string 错误原因
     */
    public function pluginUpdate();

    /**
     * 插件的安装函数，在这里完成你的插件需要安装的东西，比如你需要建立一个数据表之类
     *
     * @param string $system 系统表示，表明为哪个系统安装
     *
     * @return string|boolean  成功安装返回 true，失败返回 string 错误原因
     */
    public function pluginInstall($system);

    /**
     * 插件的卸载操作，比如需要删除插件自己创建的数据表
     *
     * @param string $system 系统表示，表明为哪个系统卸载
     *
     * @return string|boolean  成功安装返回 true，失败返回 string 错误原因
     */
    public function pluginUninstall($system);

    /**
     * 激活插件会调用这个方法，在这里做你需要的操作
     *
     * @param string $system 系统表示，表明为哪个系统
     *
     * @return string|boolean  成功安装返回 true，失败返回 string 错误原因
     */
    public function pluginActivate($system);

    /**
     * 把插件设置成非激活状态会调用这个方法，在这里做你需要的操作
     *
     * @param string $system 系统表示，表明为哪个系统
     *
     * @return string|boolean  成功安装返回 true，失败返回 string 错误原因
     */
    public function pluginDeactivate($system);

    /**
     * 返回插件配置的 URL，要求是绝对地址
     *
     * @param string $system 系统表示，表明为哪个系统
     *
     * @return string
     */
    public function pluginGetConfigureUrl($system);

    /**
     * 插件每次执行之前你可能都需要做一些初始化工作，这里就是让你做初始化的地方
     *
     * @param string $system 系统表示，表明为哪个系统
     *
     * @return string|boolean  成功安装返回 true，失败返回 string 错误原因
     */
    public function pluginLoad($system);

    /**
     * 插件执行主体
     *
     * 注意：请不要在这里执行长时间的操作，这里应该只是简单的注册系统各种 filter，
     * 快速执行完成，等之后各种 filter 被执行到之后才做业务逻辑
     *
     * @param string $system 系统表示，表明为哪个系统
     *
     */
    public function pluginAction($system);

    /**
     * 插件每次执行完成之后你可能都需要做一些清理工作，比如删除临时文件，这里就是让你做清理的地方
     *
     * @param string $system 系统表示，表明为哪个系统
     *
     * @return string|boolean  成功安装返回 true，失败返回 string 错误原因
     */
    public function pluginUnload($system);


    /**
     * 插件 command 接口，用于插件之间通讯或者插件对外提供接口
     *
     * 这个 pluginCommand 是仿照 Linux 系统的 iocontrol() 和 COM 系统中的 IDispatch 接口设计的，
     * 插件实现这个接口，可以自定义自己的命令提供给外部第三方使用
     *
     * 例如：我写一个插件 proxyPlugin 用于代理抓取任何网页，然后我实现 GET 和 POST 抓取
     *
     * 其它插件可以调用我的接口来实现任何的网页抓取功能
     *
     * 1. 通过 $instance = PluginHelper::getPluginInstanceByUniqueId('proxyPlugin 的 uuid')
     * 2. 调用 proxyPlugin 的 pluginCommand 接口
     *    $pageContent = $instance->pluginCommand('GET', array('url' => 'http://www.bzfshop.net/article/1.html'));
     *
     * 通过 pluginCommand() 接口，我们提供插件无限的扩展可能性，只有你想不到的事情，没有插件做不到的事情
     *
     * @param string $command
     * @param array  $paramArray
     *
     * @return mixed
     */
    public function pluginCommand($command, $paramArray = array());

}