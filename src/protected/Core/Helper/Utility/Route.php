<?php

/**
 * @author QiangYu
 *
 * 智能处理路由，包括网址的伪静态化，记住用户上一次从哪里浏览过来的，现在可以跳转回去
 *
 * */

namespace Core\Helper\Utility;

use Core\Helper\Utility\Utils;
use Theme\Manage\ManageThemePlugin;

class Route
{

    /**
     * 网站独一无二的 key，用于保存信息
     * */
    public static $uniqueKey = '';

    /**
     * 开启 URL 的伪静态化
     * */
    public static $isMakeStaticUrl = false;

    /**
     * 伪静态化的时候你希望的后缀是什么，一般会是 .html 之类
     * */
    public static $suffix = '.html';

    /**
     * 不同参数之间的分割符，比如 '&'，仅限使用单字符
     * */
    public static $paramDelimiter = '~';

    /**
     * 参数 key --> value 之间的分割符，比如 '='，仅限使用单字符
     * */
    public static $paramKeyValueDelimiter = '-';

    /**
     * 解析伪静态地址时候 Pattern 的分隔符，必须不同于 $paramDelimiter  $paramKeyValueDelimiter
     * 仅限使用单字符
     */
    public static $staticUrlPatternDelimiter = '!';

    /**
     * 开启在 URL 中直接包含 session id
     */
    public static $enableSessionIdUrl = false;

    /**
     * 生成唯一的 session key
     *
     * @return string
     * */
    public static function getUniqueKey()
    {
        return 'Route' . Route::$uniqueKey;
    }

    /**
     * 从一个长的 URL 中去除某个参数，比如 team.php?id=123&name=xxx&age=10，我们可以去除 name=xxx 这个查询参数
     *
     * @return string 去除了参数之后的 Url
     *
     * @param string $url           Url
     * @param string $param         参数名，比如 name
     * @param bool   $isStaticParam 这个参数是否参与 URL 静态化
     *
     */
    public static function removeParam($url, $param, $isStaticParam = false)
    {
        if (self::$isMakeStaticUrl && $isStaticParam) {
            // 静态化 URL 中参数的处理
            goto static_param;
        }

        // 参数的前位置
        $prePos = strpos($url, $param . '=');
        if ($prePos <= 0) {
            return $url;
        }

        $newUrl = substr($url, 0, $prePos);

        // 参数的后位置
        $postPos = strpos($url, '&', $prePos);
        if ($postPos > 0) {
            $newUrl .= substr($url, $postPos + 1);
        }

        return rtrim($newUrl, '\?\&');

        static_param:
        $urlInfo = self::parseStaticUrl($url);
        if (isset($urlInfo['path_param_array'])) {
            unset($urlInfo['path_param_array'][$param]);
        }

        return self::makeStaticUrl($urlInfo);
    }

    /**
     * 给一个长的 URL 增加某个参数，比如 team.php?id=123&name=xxx&age=10，我们可以增加 gender=male 这个查询参数
     *
     * @return string 去除了参数之后的 Url
     *
     * @param string $url         Url
     * @param array  $paramArray  参数列表，比如 array('name'=>'xxxx','age'=>20)
     * * @param bool $isStaticParam 这些参数是否参与 URL 静态化
     *
     */
    public static function addParam($url, array $paramArray, $isStaticParam = false)
    {
        global $f3;

        if (self::$isMakeStaticUrl && $isStaticParam && $f3->get('IS_STATIC_URI')) {
            // 静态化 URL 中参数的处理
            goto static_param;
        }

        $urlInfo = parse_url($url);

        $url = (isset($urlInfo['scheme']) ?
                $urlInfo['scheme'] . '://' . @$urlInfo['host']
                    . (('80' == @$urlInfo['port']) ? '' : @$urlInfo['port']) : '')
            . @$urlInfo['path'];

        if (@$urlInfo['query']) {
            $pattern      = '!([^&=]*)=([^&=]*)!';
            $patternMatch = array();
            preg_match_all($pattern, $urlInfo['query'], $patternMatch, PREG_SET_ORDER);
            $tmpParamArray = array();
            foreach ($patternMatch as $matchItem) {
                if (count($matchItem) >= 2) {
                    $tmpParamArray[@$matchItem[1]] = @$matchItem[2];
                }
            }
            $paramArray =
                array_merge($tmpParamArray, $paramArray);
        }

        return $url . '?' . Route::combineParam('&', '=', $paramArray)
        . (isset($urlInfo['fragment']) ? '#' . $urlInfo['fragment'] : '');

        static_param:
        $urlInfo = self::parseStaticUrl($url);
        if (!isset($urlInfo['path_param_array'])) {
            $urlInfo['path_param_array'] = array();
        }

        $urlInfo['path_param_array'] = array_merge($urlInfo['path_param_array'], $paramArray);

        return self::makeStaticUrl($urlInfo);
    }

    /**
     * 把参数用分隔符组合起来
     *
     * @return string
     *
     * @param string $paramDelimiter     不同参数之间的分隔符，例如 '&'
     * @param string $keyValueDelimiter  一个参数自身的 key->value 之间的分隔符，例如 '='
     *
     * */
    protected static function combineParam($paramDelimiter, $keyValueDelimiter, $paramArray)
    {
        if (empty($paramArray)) {
            return '';
        }

        // 参数按照 key 排序，确保同样的参数产生的 URL 是一样的
        $paramKeyArray = array_keys($paramArray);
        sort($paramKeyArray);

        $valueArray = array();
        foreach ($paramKeyArray as $key) {
            $valueArray[] = $key . $keyValueDelimiter . $paramArray[$key];
        }
        return implode($paramDelimiter, $valueArray);
    }

    /**
     * 根据提供的 控制器 + 参数 构造对应的伪静态 URL
     *
     * @return 返回构造好的 URL
     *
     * @$siteBase  网站的根目录，比如  http://www.bangzhufu.com:8080
     *
     * @param string  $siteBase    网站路径，比如 http://www.bangzhufu.com
     * @param string  $controller  控制器 比如 '/User/Login'
     * @param array   $paramArray  参数列表，比如 array('username'=>'xxx', 'password'=>'xxx')
     * @param boolean $ignoreBase  是否忽略 BASE，一个网站的地址可能是 http://xxxx.com/tuan/team/index  其中 /tuan 就是 BASE，后面才是 Controller
     * @param boolean $withScheme  是否带 scheme，带scheme 为 http://www.xxx.com:82/ ，不带 scheme 就没有域名
     * @param boolean $static      是否生成静态参数，缺省为 null 不做任何操作，如果是 false 则生成  ?username=xxx&password=xxx, true 生成静态URL
     *
     * */

    public static function makeUrlWithSiteBase(
        $siteBase,
        $controller,
        array $paramArray = null,
        $ignoreBase = false,
        $withScheme = false,
        $static = null
    ) {
        global $f3;

        $makeStaticUrl = (true === $static || (null === $static && self::$isMakeStaticUrl));

        $url = '';

        $controller = trim($controller);
        if (Utils::isBlank($controller)) {
            $url = '#';
            goto out;
        }

        if (!$ignoreBase && '/' == $controller[0]) {
            $url .= $siteBase;
        }

        $url .= $controller;

        if (!empty($paramArray)) {
            if (!$makeStaticUrl) {
                // 生成标准的动态链接
                $url .= '?' . Route::combineParam('&', '=', $paramArray);
            } else {
                // 生成静态链接
                $url .= '/' . Route::combineParam(Route::$paramDelimiter, Route::$paramKeyValueDelimiter, $paramArray)
                    . Route::$suffix;
            }
        } else {
            // 没有参数，除了首页之外也需要生成静态链接  /Cart/Show  生成为  /Cart/Show.html
            $url .= ($makeStaticUrl
                && '/' != $controller
                // 如果已经有 后缀了，不要重复添加
                && strrpos($controller, self::$suffix) !== (strlen($controller) - strlen(self::$suffix)))
                ? Route::$suffix : '';
        }

        if ($withScheme) {
            $url = Route::getDomain(true) . $url;
        }

        if (Route::$enableSessionIdUrl) {
            $url = Route::addParam($url, array(session_name() => session_id()));
        }

        out:
        return $url;
    }

    /**
     * 生成网站的 URL 链接
     *
     * @param  string $controller
     * @param array   $paramArray
     * @param bool    $ignoreBase
     * @param bool    $withSchema
     * @param boolean $static      是否生成静态参数，缺省为 null 不做任何操作，如果是 false 则生成  ?username=xxx&password=xxx, true 生成静态URL
     *
     * @return string
     */
    public static function makeUrl(
        $controller,
        array $paramArray = null,
        $ignoreBase = false,
        $withSchema = false,
        $static = null
    ) {
        global $f3;
        return self::makeUrlWithSiteBase($f3->get('BASE'), $controller, $paramArray, $ignoreBase, $withSchema, $static);
    }


    /**
     * 解析系统自身的 URL，这里能够处理静态化之后的URL
     *
     * 注意：这个方法仅仅用于解析当前网站自身的 URL
     *
     * @param string $url
     *
     * @return array
     *
     * 数组返回：
     * scheme - e.g. http
     * host
     * port
     * path
     * query - after the question mark ?
     * fragment - after the hashmark #
     *
     * 静态化 URL 返回扩展数据
     * path_base   当前系统的 BASE
     * path_controller   URL 对应的 Controller
     * path_param_array   静态化 URL 中包含的查询参数
     *
     */
    public static function parseStaticUrl($url)
    {
        $urlInfo = parse_url($url);

        global $f3;
        if (!self::$isMakeStaticUrl) {
            goto out;
        }

        // 解析静态化之后的 URL, 主要是解析 path
        $pattern      = self::$staticUrlPatternDelimiter
            . '^(' . $f3->get('BASE') . ')(/.*)/([^/]*)' . preg_quote(self::$suffix) . '$'
            . self::$staticUrlPatternDelimiter;
        $patternMatch = array();
        preg_match($pattern, urldecode($urlInfo['path']), $patternMatch);
        if (empty($patternMatch)) {
            goto out;
        }

        // 得到 controller 和 参数列表
        $controller = @$patternMatch[2];
        $argListStr = @$patternMatch[3];

        // 静态 URL 中保护的查询参数
        $pararmArray = array();

        // 没有文件名，不用解析
        if (empty($argListStr)) {
            goto out_path_ext;
        }

        // 没有任何参数，某些时候我们可能没有参数，比如  /Goods/Index.html ，这个时候 Index 不是参数，而是 Controller 的一部分
        if (false === strpos($argListStr, self::$paramKeyValueDelimiter)) {
            // 这个不是参数列表，而是 controller 的一部分
            $controller .= '/' . $argListStr;
            goto out_path_ext;
        }

        // 解析静态URL中包含的查询参数
        // 解析静态化的参数
        $excludeStr   = preg_quote(self::$paramKeyValueDelimiter) . preg_quote(self::$paramDelimiter);
        $pattern      = self::$staticUrlPatternDelimiter
            . '('
            . '([^' . $excludeStr . ']+)' // key
            . preg_quote(self::$paramKeyValueDelimiter) // =
            . '([^' . preg_quote(self::$paramDelimiter) . ']*)' // value
            . ')'
            . '(' . preg_quote(self::$paramDelimiter) . ')?' // &
            . self::$staticUrlPatternDelimiter;
        $patternMatch = array();
        preg_match_all($pattern, $argListStr, $patternMatch, PREG_SET_ORDER);

        // 设置 $pararmArray
        foreach ($patternMatch as $matchItem) {
            $paramKey = @$matchItem[2];
            if (!empty($paramKey)) {
                $pararmArray[$paramKey] = @$matchItem[3];
            }
        }

        out_path_ext: //输出额外的 path 信息
        $urlInfo['path_base']        = $f3->get('BASE');
        $urlInfo['path_controller']  = $controller;
        $urlInfo['path_param_array'] = $pararmArray;

        out:
        return $urlInfo;
    }

    /**
     * 根据 parseStaticUrl 的结果重新拼接 URL
     *
     * @param array $urlInfo
     *
     * @return string
     */
    public static function makeStaticUrl($urlInfo)
    {
        if (empty($urlInfo) || !is_array($urlInfo)) {
            return '';
        }

        // 非静态化 URL
        if (!self::$isMakeStaticUrl) {
            return (isset($urlInfo['shceme']) ?
                $urlInfo['shceme'] . '://' . @$urlInfo['host']
                    . (('80' == @$urlInfo['port']) ? '' : @$urlInfo['port']) : '')
            . @$urlInfo['path']
                . (isset($urlInfo['query']) ? '?' . $urlInfo['query'] : '')
                . (isset($urlInfo['fragment']) ? '#' . $urlInfo['fragment'] : '');
        }

        // URL 静态化，需要特殊处理
        // Trick，由于 makeUrl 会自动添加 session_id，我们这里需要临时禁止它
        $old                       = Route::$enableSessionIdUrl;
        Route::$enableSessionIdUrl = false;
        $staticUrl                 = (isset($urlInfo['shceme']) ?
                $urlInfo['shceme'] . '://' . @$urlInfo['host']
                    . (('80' == @$urlInfo['port']) ? '' : @$urlInfo['port']) : '')
            . @$urlInfo['path_base'] . self::makeUrl(@$urlInfo['path_controller'], @$urlInfo['path_param_array'], true)
                . (isset($urlInfo['query']) ? '?' . $urlInfo['query'] : '')
                . (isset($urlInfo['fragment']) ? '#' . $urlInfo['fragment'] : '');
        Route::$enableSessionIdUrl = $old;
        return $staticUrl;
    }

    /**
     * 解析 伪静态 URL，生成 F3 能够识别的 URI 和对应的参数
     */
    public static function processStaticUrl()
    {
        // 如果没有开启 URL 静态化 退出
        if (!self::$isMakeStaticUrl) {
            return;
        }

        global $f3;

        $staticURI = $f3->get('URI');

        if (empty($staticURI) || '/' == $staticURI) {
            return;
        }

        $urlInfo = self::parseStaticUrl($staticURI);
        if (!isset($urlInfo['path_controller'])
            || empty($urlInfo['path_controller'])
            || '/' == $urlInfo['path_controller']
        ) {
            // 首页，不要处理
            return;
        }

        // 设置 GET 参数
        $f3->set('GET', array_merge($urlInfo['path_param_array'], $f3->get('GET')));

        $urlQueryStr = @$urlInfo['query'];

        // 拼接标准的 URI ， /Goods/Index/goods_id--128.html  ---->  /Goods/Index?goods_id=128
        $argListStr = self::combineParam('&', '=', $urlInfo['path_param_array']);
        if (empty($argListStr)) {
            $argListStr = $urlQueryStr;
        } else {
            if (!empty($urlQueryStr)) {
                $argListStr = $argListStr . '&' . $urlQueryStr;
            }
        }

        if (!empty($argListStr)) {
            $argListStr = '?' . $argListStr;
        }

        // 加入参数中有 #xxx 之类的页内跳转
        $urlFragment = @$urlInfo['fragment'];
        if (!empty($urlFragment)) {
            $argListStr .= '#' . $urlFragment;
        }

        // 设置 URI，F3 根据这个去寻找路由
        $f3->set('ORIGINAL_URI', $f3->get('URI')); // 保存之前真实的 URI
        $f3->set('IS_STATIC_URI', true); // 当前 URI 是静态化的
        $f3->set('URI', $urlInfo['path_base'] . $urlInfo['path_controller'] . $argListStr);
    }

    /**
     * 判断 Url 是不是绝对地址
     *
     * @param string $url
     *
     * @return bool
     */
    public static function isUrlAbsolute($url)
    {
        $url = trim($url);
        if (empty($url)) {
            return false;
        }
        $lowerCase = strtolower($url);
        //如果是绝对路径，直接返回
        if ('http://' == substr($lowerCase, 0, 7) || 'https://' == substr($lowerCase, 0, 8)) {
            return true;
        }

        return false;
    }

    /**
     * 生成图片的 URL 链接
     *
     * @return string 图片的 URL 链接
     *
     * @param string $imagePath  图片存储在数据库中的链接
     */
    public static function makeImageUrl($imagePath)
    {
        global $f3;
        $imagePath = trim($imagePath);
        if (empty($imagePath)) {
            return '';
        }

        //如果是绝对路径，直接返回
        if (self::isUrlAbsolute($imagePath)) {
            return $imagePath;
        }

        // 如果开头没有 '/' 则加上
        if ('/' != $imagePath[0]) {
            $imagePath = '/' . $imagePath;
        }

        if (!Utils::isBlank($f3->get('sysConfig[image_url_prefix]'))) {
            return $f3->get('sysConfig[image_url_prefix]') . $imagePath;
        }

        return $f3->get('BASE') . $imagePath;
    }

    /**
     * 取得当前网页的来路域名
     *
     * @return string
     *
     * */
    public static function getRefer()
    {
        return (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
    }

    /**
     * 返回当前的请求路径，不包括查询参数，不包括域名
     * @return string
     * */
    public static function getRequestURLNoParam()
    {
        $url = Route::getRequestURL();
        $pos = strpos($url, '?');
        if ($pos > 0) {
            return substr($url, 0, $pos);
        }
        return $url;
    }

    /**
     * 返回完整的路径，包括查询参数， 不包括域名
     *
     * @return string 比如 User/Login?username=xxx&password=xxx
     * */
    public static function getRequestURL()
    {
        global $f3;
        if (self::$isMakeStaticUrl && $f3->get('ORIGINAL_URI')) {
            return $f3->get('ORIGINAL_URI');
        }
        return $f3->get('URI');
    }

    /**
     * 取得网站当前域名
     *
     * @return string 域名
     *
     * @param boolean shceme 是否需要 shcma，例如：不带scheme为 www.aaa.com，带 shceme 为 http://www.aaa.com:81
     */
    public static function getDomain($scheme = false)
    {

        if (!$scheme) {
            return $_SERVER["SERVER_NAME"];
        }

        $pageURL = 'http';

        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"];
        }

        return $pageURL;
    }

    /**
     * 返回 Cookie 设置对应的 domain
     *
     * 我们允许设置一个  sysConfig[cookie_domain]="bangzhufu.com" ，这样可以实现你的多个网站统一登录
     * 即： 在 www.bangzhufu.com 登录了之后， tuan.bangzhufu.com , mobile.bangzhufu.com 也同时登录了
     *
     * @return string
     */
    public static function getCookieDomain()
    {
        global $f3;
        if ($f3->get('sysConfig[cookie_domain]')) {
            return $f3->get('sysConfig[cookie_domain]');
        }
        return Route::getDomain(false);
    }

    /**
     * 返回当前页面的 URL ，带查询参数，包括域名
     *
     * @return string 比如 http://www.aaa.com/User/Login?username=xxx&password=xxx
     *
     * */
    public static function getFullURL()
    {
        return Route::getDomain(true) . Route::getRequestURL();
    }

    /**
     * 设置让用户跳回来的 url
     *
     * @param $url
     */
    public static function setJumpBackUrl($url)
    {
        global $f3;
        $saveKey = Route::getUniqueKey();
        // 把 URL 记录到 Session 里面
        $f3->set('SESSION.' . $saveKey, $url);
    }

    /**
     * 路由到另外的页面
     *
     * @param object  $controller       当前跳转的控制器
     * @param string  $url              你想路由到的页面的 URL
     * @param boolean $rememberCurrent  是否记录当前页面的 URL，方便你之后用 jumpBack（） 回到这个页面， 比如现在你需要让用户去登陆，登陆完之后你希望能返回当前页面
     *
     * */
    public static function reRoute($controller, $url, $rememberCurrent = false)
    {
        global $f3;

        if ($rememberCurrent) {
            self::setJumpBackUrl(Route::getFullURL());
        }

        // reroute 之前调用 afterRoute() 方法
        if (isset($controller) && is_object($controller) && method_exists($controller, 'afterRoute')) {
            $controller->afterRoute($f3);
        }

        // 如果 URL 不是绝对地址，我们生成绝对地址（静态化的时候保持地址格式一致）
        if (self::$isMakeStaticUrl && !Route::isUrlAbsolute($url) && false === strpos($url, '?')) {
            $url = Route::makeUrl($url, null, false, true);
        }

        if (Route::$enableSessionIdUrl) {
            $url = Route::addParam($url, array(session_name() => session_id()));
        }

        $f3->reroute($url);
    }

    /**
     * 返回之前的URL，如果不存在之前记录的URL，则跳转到缺省页面
     *
     * @param object  $controller  当前跳转的控制器
     * @param string  $defaultUrl  如果之前没有记录任何页面URL，则跳转到这个 URL
     * @param boolean $delete      跳转完之后是否删除记忆的URL，不然之后每次都会跳转
     *
     * */
    public static function jumpBack($controller, $defaultUrl = '/', $delete = true)
    {
        global $f3;

        $dstUrl  = $defaultUrl;
        $saveKey = Route::getUniqueKey();
        $backUrl = $f3->get('SESSION.' . $saveKey);

        if (!empty($backUrl)) {
            $dstUrl = $backUrl;
        }
        if ($delete) {
            $f3->clear('SESSION.' . $saveKey);
        }

        Route::reRoute($controller, $dstUrl, false);
    }

    /**
     * 是否是已经有记住的回跳 URL
     *
     * @return boolean
     * */
    public static function hasRememberUrl()
    {
        global $f3;
        $uniqueKey = $f3->get('SESSION.' . Route::getUniqueKey());
        return !Utils::isBlank($uniqueKey);
    }


    /**
     * 生成不同系统的 URL，比如生成 Shop, Groupon, Mobile 系统的 URL
     * 在 Manage 或者 Supplier 中很有用
     *
     * @param  string $system
     * @param  string $controller
     * @param array   $paramArray
     * @param bool    $static
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function makeShopSystemUrl(
        $system,
        $controller,
        array $paramArray = null,
        $static = null
    ) {
        if (empty($system)) {
            throw new \InvalidArgumentException('system can not be empty');
        }

        $systemUrlBase = ManageThemePlugin::getSystemUrlBase($system);

        global $f3;

        if (empty($systemUrlBase) || !isset($systemUrlBase['base'])) {
            if ($f3->get('DEBUG')) {
                throw new \InvalidArgumentException('system [' . $system . '] is invalid');
            }
            return '';
        }

        if (null === $static) {
            $static = $f3->get('sysConfig[enable_static_url][' . $system . ']');
            if (empty($static)) {
                $static = false;
            }
        }

        return self::makeUrlWithSiteBase($systemUrlBase['base'], $controller, $paramArray, false, false, $static);
    }

}
