<?php

/**
 * @author QiangYu
 *
 * 用户认证授权工具类
 *
 * */

namespace Core\Helper\Utility;

use Core\Service\User\User as UserBasicService;

class Auth
{

    /**
     * 网站独一无二的 key，用于保存用户信息
     */
    public static $uniqueKey = '';

    /**
     * 用户登录类型的记录
     */
    public static $loginTypeKey = 'loginType';

    /**
     * 某些情况下我们需要用 Cookie 来做认证
     *
     * 比如 swfupload 不支持 session，我们只能用 Cookie 了
     *
     */
    public static $cookieAuthKey = 'bzfshop_auth_cookie_key';

    /**
     * 缺省情况下我们不允许做 CookieAuth，考虑到安全性
     *
     * @var bool
     */
    public static $enableCookieAuth = false;

    /**
     * 生成唯一的 key
     *
     * @return string 返回 key 值
     * */
    protected static function getUniqueKey()
    {
        return 'Auth' . Auth::$uniqueKey;
    }

    /**
     *
     * @return string
     *
     * 计算一个用于 Cookie 认证的加密值
     *
     */
    public static function calcCookieAuthKey($user)
    {
        return md5(json_encode($user));
    }

    /**
     * 用户如果登录了，我们需要记录用户的登陆信息，这样后面我才知道用户是谁
     *
     * @param array  $user       用户信息数组
     * @param string $login_type 用户登录类型
     * */
    public static function saveAuthUser($user, $login_type = null)
    {
        global $f3;
        $f3->set('SESSION[' . Auth::getUniqueKey() . ']', $user);

        if (Auth::$enableCookieAuth) {
            //设置 cookie auth
            setcookie(Auth::$cookieAuthKey, session_id(), 0, $f3->get('BASE') . '/', Route::getCookieDomain());
        }

        if (!empty($login_type)) {
            $f3->set('SESSION[' . Auth::$loginTypeKey . ']', $login_type);
        }
    }

    /**
     * 返回用户的登陆类型
     *
     * @return string
     */
    public static function getLoginType()
    {
        global $f3;
        return $f3->get('SESSION[' . Auth::$loginTypeKey . ']');
    }

    /**
     *
     * @return array 返回记录的用户信息
     *
     * */
    public static function getAuthUser()
    {
        global $f3;
        return $f3->get('SESSION.' . Auth::getUniqueKey());
    }

    /**
     * 判断用户是否登陆
     *
     * @return boolean
     * */
    public static function isAuthUser()
    {
        $authUser = Auth::getAuthUser();
        return !empty($authUser);
    }

    /**
     * 判读 POST 认证是否有效，解决 flash 无法传输 Session 的 bug
     *
     * @return boolean
     */
    public static function isPostCookieAuth()
    {

        // 没有开启 CookieAuth
        if (!Auth::$enableCookieAuth) {
            return false;
        }

        global $f3;

        $cookieAuthValue = $f3->get('POST.' . Auth::$cookieAuthKey);

        if (empty($cookieAuthValue)) {
            return false;
        }

        // 关闭旧的 session
        session_write_close();
        //打开新的 session
        session_id($cookieAuthValue);
        session_start();

        return Auth::isAuthUser();
    }

    /**
     * 清除用户的登陆状态，用户算是退出了
     *
     * */
    public static function removeAuthUser()
    {
        global $f3;
        $f3->clear('SESSION.' . Auth::getUniqueKey());
        setcookie(Auth::$cookieAuthKey, null, 0, $f3->get('BASE') . '/', Route::getCookieDomain());
    }

    /**
     * 重新加载用户信息
     */
    public static function reloadAuthUser()
    {
        $authUser = Auth::getAuthUser();
        if (empty($authUser) || !isset($authUser['user_id']) || empty($authUser['user_id'])) {
            return;
        }

        $basicUserService = new UserBasicService();
        $user             = $basicUserService->loadUserById($authUser['user_id']);
        if ($user->isEmpty()) {
            return;
        }
        Auth::saveAuthUser($user->toArray());
    }

}