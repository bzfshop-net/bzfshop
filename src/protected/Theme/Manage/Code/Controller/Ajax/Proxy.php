<?php

/**
 * @author QiangYu
 *
 * Ajax 调用代理，用于解决 ajax 的跨域限制
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;

class Proxy extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    public function Proxy($f3)
    {
        $url = $f3->get('GET.url');
        $url = trim($url);

        $errorMessage = '';
        if (empty($url)) {
            $errorMessage = 'url is empty';
            goto out_fail;
        }

        $request = \Web::instance()->request($url);
        if (!$request) {
            $errorMessage = 'request fail for url [' . $url . ']';
            goto out_fail;
        }

        // 成功返回数据
        Ajax::header();
        echo Ajax::buildResult(null, null, $request['body']);
        return;

        out_fail:
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }

    public function Json($f3)
    {
        $url = $f3->get('GET.url');
        $url = trim($url);

        $cache = $f3->get('GET.cache'); // 缓存时间
        $cache = trim($cache);
        $cache = intval($cache);

        if (!$cache || $cache <= 0) { // 不要缓存
            goto fetch_data;
        }

        // 处理缓存
        $cacheId  = md5(__CLASS__ . '\\' . __METHOD__ . '\\' . $url);
        $jsonData = $f3->get($cacheId);
        if (!empty($jsonData)) {
            goto out;
        }

        fetch_data:

        $errorMessage = '';
        if (empty($url)) {
            $errorMessage = 'url is empty';
            goto out_fail;
        }

        $request = \Web::instance()->request($url);
        if (!$request) {
            $errorMessage = 'request fail for url [' . $url . ']';
            goto out_fail;
        }

        $jsonData = json_decode($request['body']);
        if (!empty($jsonData) && $cache > 0) { // 放入缓存中
            $f3->set($cacheId, $jsonData, $cache);
        }

        out:

        // 成功返回数据
        Ajax::header();
        echo Ajax::buildResult(null, null, $jsonData);
        return;

        out_fail:
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }

}
