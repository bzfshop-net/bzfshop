<?php
/**
 * @author QiangYu
 *
 * 静态资源的管理接口
 *
 */

namespace Core\Asset;


interface IManager
{
    /**
     * 注册模块，模块的资源后面都相对于这个模块自己
     *
     * @param string $moduleUniqueId          模块的唯一识别标识
     * @param string $moduleVersion           模块的版本号
     * @param string $moduleBasePath          模块的 根目录地址，后面资源都是相对这个地址
     *
     */
    public function registerModule(
        $moduleUniqueId,
        $moduleVersion,
        $moduleBasePath
    );

    /**
     * 发布资源
     *
     * @param string $moduleUniqueId          模块的唯一识别标识
     * @param string $relativeAssetPath       需要发布的资源地址，相对于上面的 $moduleBasePath 的地址
     *
     * @return boolean 成功返回 true，失败返回 false
     */
    public function publishAsset(
        $moduleUniqueId,
        $relativeAssetPath
    );


    /**
     * 取得资源对应的 URL
     *
     * @param string $moduleUniqueId          模块的唯一识别标识
     * @param string $relativeAssetPath       需要发布的资源地址，相对于上面的 $moduleBasePath 的地址
     *
     * @return string 成功返回资源对应的URL，失败返回 空
     */
    public function getAssetUrl(
        $moduleUniqueId,
        $relativeAssetPath
    );

    /**
     * 对一个模块中的 JavaScript 做合并，并且返回 URL
     *
     * 调用这个方法之前你的所有 JS 文件已经成功 Publish 了，这里只做合并，不做任何 Publish 操作
     *
     * @param   string $moduleUniqueId
     * @param array    $jsFileRelativePathArray
     *
     * @return string 成功返回资源对应的URL，失败返回 空
     */
    public function getMergedAssetJsUrl(
        $moduleUniqueId,
        array $jsFileRelativePathArray
    );

    /**
     * 对一个模块中的 Css 做合并，并且返回 URL
     *
     * 调用这个方法之前你的所有 Css 文件已经成功 Publish 了，这里只做合并，不做任何 Publish 操作
     *
     * @param   string $moduleUniqueId
     * @param array    $cssFileRelativePathArray
     *
     * @return string 成功返回资源对应的URL，失败返回 空
     */
    public function getMergedAssetCssUrl(
        $moduleUniqueId,
        array $cssFileRelativePathArray
    );

    /**
     * 注册一个 CSS 文件 URL，页面可以从这里取得你希望使用的 css
     *
     * @param string $cssFileUrl
     *
     */
    public function registerCss($cssFileUrl);

    /**
     * 返回所有注册过的 css 文件
     *
     * @return array
     */
    public function getRegisterCssArray();

    /**
     * 注册一个 JavaScript 文件
     *
     * @param string $jsFileUrl
     *
     */
    public function registerJs($jsFileUrl);

    /**
     * 返回所有注册过的 JavaScript 文件
     *
     * @return array
     */
    public function getRegisterJsArray();
}