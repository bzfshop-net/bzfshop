<?php
/**
 * @author QiangYu
 *
 * 抽象的插件升级基类， 在这里我们实现了统一的插件升级方法
 *
 */

namespace Core\Plugin;


abstract class AbstractUpdate extends \Prefab
{

    /**
     * 允许从这些版本做升级
     */
    protected $sourceVersionAllowed = array('1.0.1');

    /**
     * 升级完成之后的目标 版本
     */
    protected $targetVersion = '1.0.2';

    /**
     * 是否允许对这个版本升级
     *
     * @param string $currentVersion
     *
     * @return bool
     */
    public function isVersionAllowUpdate($currentVersion)
    {
        return in_array($currentVersion, $this->sourceVersionAllowed);
    }

    /**
     * 你必须重载这个方法实现你自己的升级
     *
     * @param string $currentVersion
     *
     * @return bool   如果返回 false，则下面的升级序列就不会被执行
     *
     * @throws \InvalidArgumentException
     */
    public function doUpdate($currentVersion)
    {
        throw new \InvalidArgumentException('can not update from version [' . $currentVersion . ']');
    }

    /**
     * 升级失败，挨个回滚
     *
     * @param string $currentVersion
     */
    public function doRollBack($currentVersion)
    {
        // do nothing here
    }

}