<?php
/**
 * @author QiangYu
 *
 * IManager 的一个抽象实现
 *
 */

namespace Core\Asset;


abstract class AbstractManager extends \Prefab implements IManager
{

    protected $registerCssArray = array();
    protected $registerJsArray = array();

    public function registerCss($cssFileUrl)
    {
        if (!empty($cssFileUrl)) {
            $this->registerCssArray[] = $cssFileUrl;
        }
    }

    public function getRegisterCssArray()
    {
        return $this->registerCssArray;
    }

    public function registerJs($jsFileUrl)
    {
        if (!empty($jsFileUrl)) {
            $this->registerJsArray[] = $jsFileUrl;
        }
    }

    public function getRegisterJsArray()
    {
        return $this->registerJsArray;
    }

    public function getMergedAssetJsUrl(
        $moduleUniqueId,
        array $jsFileRelativePathArray
    ) {
        return 'not implemented';
    }

    public function getMergedAssetCssUrl(
        $moduleUniqueId,
        array $cssFileRelativePathArray
    ) {
        return 'not implemented';
    }
}