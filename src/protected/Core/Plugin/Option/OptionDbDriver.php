<?php
/**
 * @author QiangYu
 *
 * 使用 数据表 来保存 option 值
 *
 * 注意： 为了实现 Cache 共享，我手动的修改了 F3 中的 Cache 实现，增加了 2 个方法
 *
 *     public function getPrefix(){
 *       return $this->prefix;
 *     }
 *
 *     public function setPrefix($prefix){
 *       $this->prefix = $prefix;
 *     }
 *
 *
 */

namespace Core\Plugin\Option;

use Core\Cache\ShareCache;
use Core\Service\Meta\Meta as MetaService;

class OptionDbDriver implements IOptionDriver
{

    const META_TYPE = 'plugin_option';

    private $cacheIdPrefix = null;

    private function makeCacheId($optionName)
    {
        if (!$this->cacheIdPrefix) {
            $this->cacheIdPrefix = md5(__FILE__);
        }
        return $this->cacheIdPrefix . '_' . $optionName;
    }

    public function isOptionValueExist($optionName)
    {
        // 从数据库查询数据
        $metaService = new MetaService();
        $optionItem  = $metaService->loadMetaByTypeAndKey(OptionDbDriver::META_TYPE, $optionName);
        return !$optionItem->isEmpty();
    }

    public function getOptionValue($optionName, $ttl = 0)
    {
        $optionValue = null;
        $cacheId     = $this->makeCacheId($optionName);

        if ($ttl > 0) {
            // 首先检查缓存
            if ($optionValue = ShareCache::get($cacheId)) {
                goto out;
            }
        }

        // 从数据库查询数据
        $metaService = new MetaService();
        $optionItem  = $metaService->loadMetaByTypeAndKey(OptionDbDriver::META_TYPE, $optionName);

        if (!$optionItem->isEmpty()) {
            $optionValue = $optionItem['meta_data'];
            if ($ttl > 0) {
                ShareCache::set($cacheId, $optionValue, $ttl);
            }
        } else {
            global $f3;
            if ($f3->get('DEBUG')) {
                // debug 模式，我们对不存在的 optionName 报错，方便发现错误
                throw new \InvalidArgumentException('optionName [' . $optionName . '] does not exist');
            }
        }

        out:
        return $optionValue;
    }

    public function saveOptionValue($optionName, $optionValue)
    {
        // 更新或者插入数据库记录
        $metaService           = new MetaService();
        $optionItem            = $metaService->loadMetaByTypeAndKey(OptionDbDriver::META_TYPE, $optionName);
        $optionItem->meta_type = OptionDbDriver::META_TYPE;
        $optionItem->meta_key  = $optionName;
        $optionItem->meta_data = $optionValue;
        $optionItem->save();

        // 清除缓存，为什么这里是 clear 而不是 set ？
        // 我们希望下一次 get 是直接从数据库中取得数据，以防万一某种情况导致了数据不一致，我们好歹能在下一步实现数据同步
        ShareCache::clear($this->makeCacheId($optionName));
    }

    public function removeOptionValue($optionName)
    {
        // 取得数据库记录
        $metaService = new MetaService();
        $optionItem  = $metaService->loadMetaByTypeAndKey(OptionDbDriver::META_TYPE, $optionName);
        if (!$optionItem->isEmpty()) {
            $optionItem->erase();
        }

        // 清除缓存
        ShareCache::clear($this->makeCacheId($optionName));
    }


    public function fetchOptionValueArray($optionName, $ttl = 0)
    {
        $optionValueArray = array();
        $cacheId          = $this->makeCacheId($optionName);

        if ($ttl > 0) {
            // 首先检查缓存
            if ($optionValueArray = ShareCache::get($cacheId)) {
                goto out;
            }
        }

        // 从数据库查询数据
        $metaService = new MetaService();
        $optionArray = $metaService->fetchMetaArrayByTypeAndKey(OptionDbDriver::META_TYPE, $optionName);

        if (!empty($optionArray)) {
            foreach ($optionArray as $optionItem) {
                $optionValueArray[] = array(
                    'id'    => $optionItem['meta_id'],
                    'name'  => $optionItem['meta_key'],
                    'value' => $optionItem['meta_data']
                );
            }
            if ($ttl > 0) {
                ShareCache::set($cacheId, $optionValueArray, $ttl);
            }
        } else {
            global $f3;
            if ($f3->get('DEBUG')) {
                // debug 模式，我们对不存在的 optionName 报错，方便发现错误
                throw new \InvalidArgumentException('optionName [' . $optionName . '] does not exist');
            }
        }

        out:
        return $optionValueArray;
    }

    public function removeOptionValueArray($optionName)
    {
        $metaService = new MetaService();
        $metaService->removeMetaArrayByTypeAndKey(OptionDbDriver::META_TYPE, $optionName);

        // 清除缓存
        ShareCache::clear($this->makeCacheId($optionName));
    }

    public function saveOptionValueById($optionId, $optionName, $optionValue)
    {

        // 更新或者插入数据库记录
        $metaService = new MetaService();
        $optionItem  = $metaService->loadMetaById($optionId);

        if (!$optionItem->isEmpty() && $optionItem->meta_type != OptionDbDriver::META_TYPE) {
            // 如果不是 option 数据抛出异常
            throw new \InvalidArgumentException('optionId[' . $optionId . '] is not ' . OptionDbDriver::META_TYPE);
        }

        $optionItem->meta_type = OptionDbDriver::META_TYPE;
        $optionItem->meta_key  = $optionName;
        $optionItem->meta_data = $optionValue;
        $optionItem->save();

        // 清除缓存
        ShareCache::clear($this->makeCacheId($optionName));
    }

    public function removeOptionValueById($optionId)
    {
        $metaService = new MetaService();
        $optionItem  = $metaService->loadMetaById($optionId);

        if ($optionItem->isEmpty()) {
            return;
        }

        if ($optionItem->meta_type != OptionDbDriver::META_TYPE) {
            // 如果不是 option 数据抛出异常
            throw new \InvalidArgumentException('optionId[' . $optionId . '] is not ' . OptionDbDriver::META_TYPE);
        }

        $optionItem->erase();

        // 清除缓存
        ShareCache::clear($this->makeCacheId($optionItem->meta_key));
    }
}