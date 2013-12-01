<?php

/**
 * @author QiangYu
 *
 * 订单对应的操作
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Service\BaseService;
use Core\Service\Meta\Dictionary as MetaDictionaryService;
use Core\Service\Order\Goods as OrderGoodsService;

class Order extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    /******** 输出 order_goods_status 的状态列表 ********/
    public function ListOrderGoodsStatus($f3)
    {
        $outputArray = array();

        foreach (OrderGoodsService::$orderGoodsStatusDesc as $index => $desc) {
            $outputArray[] = array('id' => $index, 'desc' => $desc);
        }

        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $outputArray);
    }

    /**
     * 输出 order_refer 中的 utm_source 列表
     *
     * @param $f3
     */
    public function ListOrderReferUtmSource($f3)
    {
        // 检查缓存
        $cacheKey    = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);
        $resultArray = $f3->get($cacheKey);
        if (!empty($resultArray)) {
            goto out;
        }

        $baseService = new BaseService();
        $queryResult = $baseService->_fetchArray(
            'order_refer',
            'distinct(utm_source)',
            array(array('utm_source is not null')),
            null,
            0,
            0
        );

        // 构造词库
        $wordArray = array('SELF'); // 加入网站自身
        foreach ($queryResult as $queryItem) {
            $wordArray[] = $queryItem['utm_source'];
        }

        // 字典服务转换显示
        $metaDictionaryService = new MetaDictionaryService();
        $resultArray           = $metaDictionaryService->getWordArray($wordArray);

        $f3->set($cacheKey, $resultArray, 600); //缓存 10 分钟

        out:
        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $resultArray);
    }

    /**
     * 输出 order_refer 中的 utm_medium 列表
     *
     * @param $f3
     */
    public function ListOrderReferUtmMedium($f3)
    {
        // 检查缓存
        $cacheKey    = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);
        $resultArray = $f3->get($cacheKey);
        if (!empty($resultArray)) {
            goto out;
        }

        $baseService = new BaseService();
        $queryResult = $baseService->_fetchArray(
            'order_refer',
            'distinct(utm_medium)',
            array(array('utm_medium is not null')),
            null,
            0,
            0
        );

        // 构造词库
        $wordArray = array('SELF'); // 加入网站自身
        foreach ($queryResult as $queryItem) {
            $wordArray[] = $queryItem['utm_medium'];
        }

        // 字典服务转换显示
        $metaDictionaryService = new MetaDictionaryService();
        $resultArray           = $metaDictionaryService->getWordArray($wordArray);

        $f3->set($cacheKey, $resultArray, 600); //缓存 10 分钟

        out:
        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $resultArray);
    }

    /**
     * 列出 order_refer 中记录的用户登陆方式
     *
     * @param $f3
     */
    public function ListOrderReferLoginType($f3)
    {
        // 检查缓存
        $cacheKey    = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);
        $resultArray = $f3->get($cacheKey);
        if (!empty($resultArray)) {
            goto out;
        }

        $baseService = new BaseService();
        $queryResult = $baseService->_fetchArray(
            'order_refer',
            'distinct(login_type)',
            array(array('login_type is not null')),
            null,
            0,
            0
        );

        if (empty($queryResult)) {
            $resultArray = array();
            goto out;
        }

        // 构造词库
        foreach ($queryResult as $queryItem) {
            $wordArray[] = $queryItem['login_type'];
        }

        // 字典服务转换显示
        $metaDictionaryService = new MetaDictionaryService();
        $resultArray           = $metaDictionaryService->getWordArray($wordArray);

        $f3->set($cacheKey, $resultArray, 600); //缓存 10 分钟

        out:
        $f3->expire(60); // 客户端缓存 1 分钟
        Ajax::header();
        echo Ajax::buildResult(null, null, $resultArray);
    }

    /**
     * 列出订单的来源系统
     *
     * @param $f3
     */
    public function ListOrderSystemId($f3)
    {
        // 检查缓存
        $cacheKey    = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);
        $resultArray = $f3->get($cacheKey);
        if (!empty($resultArray)) {
            goto out;
        }

        $baseService = new BaseService();
        $queryResult = $baseService->_fetchArray(
            'order_info',
            'distinct(system_id)',
            array(array('system_id is not null')),
            null,
            0,
            0
        );

        if (empty($queryResult)) {
            $resultArray = array();
            goto out;
        }

        // 构造词库
        foreach ($queryResult as $queryItem) {
            $wordArray[] = $queryItem['system_id'];
        }

        // 字典服务转换显示
        $metaDictionaryService = new MetaDictionaryService();
        $resultArray           = $metaDictionaryService->getWordArray($wordArray);

        $f3->set($cacheKey, $resultArray, 600); //缓存 10 分钟

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, $resultArray);
    }
}
