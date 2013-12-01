<?php
/**
 * @author
 *
 * 一个简单的资源管理类
 *
 */

namespace Core\Asset;


use Assetic\Asset\FileAsset;
use Assetic\Filter\CssImportFilter;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\CssRewriteFilter;
use Assetic\Filter\JSMinFilter;
use Core\Helper\Utility\Utils;

class SimpleManager extends AbstractManager
{
    /**
     * asset 对应的 url 链接前缀
     *
     * @var string
     */
    private $assetUrlPrefix;

    /**
     * asset 所在的根目录
     *
     * @var string
     */
    private $assetBasePath;

    /**
     * 是否启用智能发布，根据资源的最后修改时间决定是否要重新发布
     *
     * @var bool
     */
    private $smartPublish = true;

    /**
     * 是否在资源 url 上启用 hash 参数，这样万一资源发生过修改可以自动让用户的浏览器更新
     *
     * @var bool
     */
    private $fileHashUrl = true;

    /**
     * 是否 hash 修改文件名
     *
     * @var bool
     */
    private $fileHashName = false;

    /**
     * 保存注册过的模块信息
     *
     * 结构 array (
     *      'moduleUniqueId' => array('moduleUniqueId' => $moduleUniqueId,
     *                                'moduleVersion' => $moduleVersion,
     *                                'moduleBasePath' => $moduleBasePath)
     * )
     *
     * @var array
     */
    private $registerModuleArray = array();


    public function __construct($assetUrlPrefix, $assetBasePath)
    {
        $this->assetUrlPrefix = $assetUrlPrefix;
        $this->assetBasePath  = $assetBasePath;
    }

    /**
     * 是否启用智能发布，根据资源的最后修改时间决定是否要重新发布
     *
     * @param boolean $smartPublish
     */
    public function enableSmartPublish($smartPublish)
    {
        $this->smartPublish = $smartPublish;
    }

    /**
     * 是否在资源 url 上启用 hash 参数，这样万一资源发生过修改可以自动让用户的浏览器更新
     *
     * @param boolean $fileHashUrl
     * @param boolean $fileHashName
     */
    public function enableFileHashUrl($fileHashUrl, $fileHashName)
    {
        $this->fileHashUrl  = $fileHashUrl;
        $this->fileHashName = $fileHashName;
    }

    public function registerModule(
        $moduleUniqueId,
        $moduleVersion,
        $moduleBasePath
    ) {

        // 已经注册过了，不要重复注册
        if (isset($this->registerModuleArray[$moduleUniqueId])) {
            printLog('duplicate module [' . $moduleUniqueId . '] register', 'AssetManager');
            return;
        }

        $this->registerModuleArray[$moduleUniqueId] = array(
            'moduleUniqueId' => $moduleUniqueId,
            'moduleVersion'  => $moduleVersion,
            'moduleBasePath' => $moduleBasePath
        );

    }

    /**
     * 取得模块的发布 "相对" 目录
     *
     * @param string $moduleUniqueId
     */
    private function getModulePublishRelativeDir($moduleUniqueId)
    {

        if (isset($this->registerModuleArray[$moduleUniqueId])) {
            $moduleArray = $this->registerModuleArray[$moduleUniqueId];
            return $moduleArray['moduleUniqueId'] . '/' . $moduleArray['moduleVersion'];
        }

        return '';
    }

    private function getAbsoluteModuleSourcePath($moduleUniqueId, $relativeAssetPath)
    {

        if (isset($this->registerModuleArray[$moduleUniqueId])) {
            $moduleArray = $this->registerModuleArray[$moduleUniqueId];
            return $moduleArray['moduleBasePath'] . DIRECTORY_SEPARATOR . $relativeAssetPath;
        }

        return '';
    }

    public function publishAsset(
        $moduleUniqueId,
        $relativeAssetPath
    ) {

        $sourcePath = $this->getAbsoluteModuleSourcePath($moduleUniqueId, $relativeAssetPath);
        $targetPath =
            $this->assetBasePath . DIRECTORY_SEPARATOR . $this->getModulePublishRelativeDir($moduleUniqueId)
            . DIRECTORY_SEPARATOR . $relativeAssetPath;

        if (!file_exists($targetPath)) {

            if (!file_exists($sourcePath)) {
                // 源文件不存在
                printLog($sourcePath . ' does not exist', 'AssetManager', \Core\Log\Base::ERROR);
                return false;
            }

            // 资源不存在，整个复制资源过去
            Utils::copyFile($sourcePath, $targetPath);
            printLog('publish asset [' . $sourcePath . '] To [' . $targetPath . ']', 'AssetManager');
            return true;
        }

        // 智能判断资源文件是否被修改过
        $sourceModifyTime = filemtime($sourcePath);
        $targetModifyTime = filemtime($targetPath);
        if ($this->smartPublish && $sourceModifyTime > $targetModifyTime) {
            // 源文件的最后修改时间 > 目标文件最后修改时间，说明文件被改过，需要重新发布
            Utils::copyFile($sourcePath, $targetPath);
            printLog('smart re-publish asset [' . $sourcePath . '] To [' . $targetPath . ']', 'AssetManager');
            return true;
        }

        return true;
    }

    public function getAssetUrl(
        $moduleUniqueId,
        $relativeAssetPath
    ) {

        $targetPath =
            $this->assetBasePath . DIRECTORY_SEPARATOR . $this->getModulePublishRelativeDir($moduleUniqueId)
            . DIRECTORY_SEPARATOR . $relativeAssetPath;

        // 简单的取根目录的地址
        if (empty($relativeAssetPath)) {
            goto out;
        }

        // 智能发布资源
        $this->publishAsset($moduleUniqueId, $relativeAssetPath);
        $targetModifyTime = filemtime($targetPath);

        out:

        $hashParam = '';
        if ($this->fileHashUrl && is_file($targetPath)) {
            if (!$this->fileHashName) {
                // 只有文件才启用 hash 参数，其它不启用
                $hashParam = $relativeAssetPath . '?hash=' . $targetModifyTime;
            } else {
                // hash 文件名，而不是在后面添加参数
                $pathInfo = pathinfo($relativeAssetPath);
                $hashFile =
                    $this->assetBasePath . DIRECTORY_SEPARATOR . $this->getModulePublishRelativeDir($moduleUniqueId)
                    . DIRECTORY_SEPARATOR . $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $targetModifyTime
                    . '.' . $pathInfo['basename'];

                // hash 文件不存在，复制一个
                if (!is_file($hashFile)) {
                    Utils::copyFile($targetPath, $hashFile);
                }

                // 指向 hash 文件名
                $hashParam = $pathInfo['dirname'] . '/' . $targetModifyTime . '.' . $pathInfo['basename'];
            }
        } else {
            $hashParam = $relativeAssetPath;
        }

        return $this->assetUrlPrefix . '/' . $this->getModulePublishRelativeDir($moduleUniqueId)
        . '/' . $hashParam;
    }

    /**
     * 合并文件，并且返回合并之后的文件名
     *
     * @param  string  $moduleUniqueId
     * @param array    $fileRelativePathArray
     * @param string   $fileExt                     合并之后的文件扩展名
     * @param callback $fileContentFilter           对每个文件的内容做 filter
     *
     * @return string 合并之后的文件名
     */
    private function mergeAssetCssJsFile(
        $moduleUniqueId,
        array $fileRelativePathArray,
        $fileExt,
        $fileContentFilter = null
    ) {

        if (empty($fileRelativePathArray)) {
            throw new \InvalidArgumentException('fileRelativePathArray can not be empty');
        }

        $targetModuleBasePath =
            $this->assetBasePath . DIRECTORY_SEPARATOR . $this->getModulePublishRelativeDir($moduleUniqueId)
            . DIRECTORY_SEPARATOR;

        // 计算合并之后的文件名
        $targetFileNameSource = '';
        foreach ($fileRelativePathArray as $relativeAssetPath) {
            // 智能发布资源
            $this->publishAsset($moduleUniqueId, $relativeAssetPath);
            $sourcePath = $targetModuleBasePath . $relativeAssetPath;
            if (!is_file($sourcePath)) {
                printLog(
                    'Calculate FileNameSource Error : [' . $sourcePath . '] is not a regular file',
                    'AssetManager',
                    \Core\Log\Base::ERROR
                );
                continue;
            }
            $targetModifyTime = filemtime($sourcePath);
            $targetFileNameSource .= '{' . $sourcePath . '[' . $targetModifyTime . ']}';
        }

        // 目标文件名
        $targetFileNameSourceMd5 = md5($targetFileNameSource);
        $targetFileName          = $targetFileNameSourceMd5 . '.min' . $fileExt;

        // 合并文件已经存在，直接退出就可以了
        if (is_file($targetModuleBasePath . $targetFileName)) {
            goto out;
        }

        // 打印 log
        printLog($targetFileNameSource, 'AssetManager');

        // 做文件的合并

        $filterArray = array();
        // 建立对应的 filter
        if ('.js' == $fileExt) {
            $filterArray = array(new JSMinFilter());
        } elseif ('.css' == $fileExt) {
            $filterArray = array(new CssImportFilter(), new CssRewriteFilter(), new CssMinFilter());
        } else {
            // do nothing
        }

        $mergeFileContent = '';
        foreach ($fileRelativePathArray as $relativeAssetPath) {
            $sourcePath = $targetModuleBasePath . $relativeAssetPath;
            if (!is_file($sourcePath)) {
                printLog(
                    'Merge File Error : [' . $sourcePath . '] is not a regular file',
                    'AssetManager',
                    \Core\Log\Base::ERROR
                );
                continue;
            }

            // 我们采用 Assetic 来做文件处理
            $fileAsset = new FileAsset($targetModuleBasePath
            . $relativeAssetPath, $filterArray, $targetModuleBasePath, $relativeAssetPath);
            $fileAsset->setTargetPath('.');
            $mergeFileContent .= $fileContentFilter ?
                call_user_func_array($fileContentFilter, array($relativeAssetPath, $fileAsset->dump()))
                : $fileAsset->dump();
            unset($fileAsset); // 释放内存
        }

        // 生成合并之后的文件
        file_put_contents(
            $targetModuleBasePath . $targetFileName,
            $mergeFileContent
        );

        out:
        return $targetFileName;
    }

    public function getMergedAssetJsUrl(
        $moduleUniqueId,
        array $jsFileRelativePathArray
    ) {

        $targetFileName = $this->mergeAssetCssJsFile(
            $moduleUniqueId,
            $jsFileRelativePathArray,
            '.js',
            function ($relativeAssetPath, $fileContent) {
                // 文件末尾加上 ; 防止一些不规范的文件出错
                return "/**  " . $relativeAssetPath . "  **/\n\n" . $fileContent . ";\n\n\n";
            }
        );
        return $this->assetUrlPrefix . '/' . $this->getModulePublishRelativeDir($moduleUniqueId)
        . '/' . $targetFileName;
    }

    public function getMergedAssetCssUrl(
        $moduleUniqueId,
        array $cssFileRelativePathArray
    ) {
        $targetFileName = $this->mergeAssetCssJsFile(
            $moduleUniqueId,
            $cssFileRelativePathArray,
            '.css',
            function ($relativeAssetPath, $fileContent) {
                return "/**  " . $relativeAssetPath . "  **/\n\n" . $fileContent . "\n\n\n";
            }
        );
        return $this->assetUrlPrefix . '/' . $this->getModulePublishRelativeDir($moduleUniqueId)
        . '/' . $targetFileName;
    }

}