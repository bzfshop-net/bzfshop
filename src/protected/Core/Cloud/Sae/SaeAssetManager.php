<?php
/**
 * @author QiangYu
 *
 * Sae 平台的 Asset 管理类，由于 Sae 平台不支持目录写操作，Storage 效率非常低，所以我们采用 KVDB 来做 Asset 存储
 *
 */

namespace Core\Cloud\Sae;


use Core\Asset\AbstractManager;
use Core\Cache\AbstractClear;
use Core\Cache\ClearHelper;

class SaeAssetManager extends AbstractManager
{
    /**
     * Sae 的 KVDB 对象
     */
    private $saeKv = null;

    /**
     * SaeKvDB 的 key 前缀，用于识别 Asset 资源
     */
    public static $saeKeyPrefix = '[SaeAsset]';

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

    public function __construct($system)
    {
        global $f3;

        $this->assetUrlPrefix = $f3->get('sysConfig[webroot_url_prefix]') . '/asset';
        $this->assetBasePath  = '/' . $system . '/asset';

        // 注册 F3 的路由，所有 /asset 请求都由我们自己处理
        global $f3;
        $f3->route('GET /asset/*', __CLASS__ . '->fetchAsset');

        // 注册 Clear方法，用于清除 Asset 资源
        ClearHelper::registerInstanceClass(__NAMESPACE__ . '\\' . 'SaeAssetClear');
    }

    /**
     * 实现从 Sae KvDb 中读取数据然后输出 asset
     *
     * @param $f3
     */
    public function fetchAsset($f3)
    {
        // 注册 F3 的路由，所有 /asset 请求都由我们自己处理
        $pattern      = '!^(' . $f3->get('BASE') . '/asset)(/[^\?]*)(\?.*)?$!';
        $patternMatch = array();
        preg_match($pattern, $f3->get('URI'), $patternMatch);
        if (empty($patternMatch)) {
            goto out_fail;
        }

        // 资源文件的相对路径
        $relativeAssetPath = @$patternMatch[2];

        $targetPath =
            $this->assetBasePath . $relativeAssetPath;

        $targetKey = self::$saeKeyPrefix . md5($targetPath);

        $saeKv        = $this->getSaeKv();
        $assetContent = $saeKv->get($targetKey);

        if (!$assetContent) {
            goto out_fail;
        }

        // 输出 content-type header 信息
        header('Content-Type: ' . \Web::instance()->mime($targetPath));

        // 静态资源缓存 1 天
        $f3->expire(86400);

        // 输出asset 内容
        echo $assetContent;

        return; // 正确从这里返回

        out_fail: // 错误返回 404
        $f3->error(404);

    }

    private function getSaeKv()
    {
        if (!$this->saeKv) {
            $this->saeKv = new \SaeKV();
            $this->saeKv->init();
        }

        return $this->saeKv;
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

        if (!file_exists($sourcePath)) {
            // 源文件不存在
            printLog($sourcePath . ' does not exist', 'AssetManager', \Core\Log\Base::ERROR);
            return false;
        }

        $targetKey = self::$saeKeyPrefix . md5($targetPath);

        $saeKv = $this->getSaeKv();

        if (!$saeKv->get($targetKey)) {

            // 如果是目录，递归发布下面的文件
            if (is_dir($sourcePath)) {
                $saeKv->add($targetKey, $sourcePath);

                $directory = dir($sourcePath);
                while (false !== ($readdirectory = $directory->read())) {
                    if ($readdirectory == '.' || $readdirectory == '..') {
                        continue;
                    }
                    // 递归发布资源
                    $this->publishAsset($moduleUniqueId, $relativeAssetPath . DIRECTORY_SEPARATOR . $readdirectory);
                }

            } else {
                // 普通文件把文件内容放进去
                $saeKv->add($targetKey, @file_get_contents($sourcePath));
            }
        }

        return true;
    }

    public function getAssetUrl(
        $moduleUniqueId,
        $relativeAssetPath
    ) {
        return $this->assetUrlPrefix . '/' . $this->getModulePublishRelativeDir($moduleUniqueId)
        . '/' . $relativeAssetPath;
    }

    /**
     * 清除所有的 Asset
     */
    public function clearAllAsset()
    {
        $saeKv = $this->getSaeKv();

        $clearCount = 0;

        for (; ;) {
            // 每次取 100 个
            $keyValueArray = $saeKv->pkrget(self::$saeKeyPrefix, 100, false);
            if (empty($keyValueArray)) {
                goto out;
            }

            // 清除数据
            foreach ($keyValueArray as $key => $value) {
                $saeKv->delete($key);
                $clearCount++;
            }
        }

        out:
        printLog('clear SaeAsset success count [' . $clearCount . ']', __CLASS__);
    }

}

/**
 * 用于清除已经发布的 Asset 资源
 *
 * @package Core\Cloud\Sae
 */
class SaeAssetClear extends AbstractClear
{
    public function clearAllCache()
    {
        SaeAssetManager::instance()->clearAllAsset();
    }
}