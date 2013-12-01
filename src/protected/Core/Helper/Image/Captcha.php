<?php

/**
 * @author QiangYu
 *
 * 验证码生成工具类
 *
 * */

namespace Core\Helper\Image;

class Captcha
{

    private $captchaKey = 'SESSION[CAPTCHA]';

    /**
     * 返回验证码图片二进制编码数据
     *
     * @return 二进制编码数据
     *
     * @param int $size  字体多大，比如 20 号字体
     * @param int $len   多少个字符，比如使用 5 个字符
     *
     * */
    public function generateCaptcha($size = 20, $len = 5)
    {
        global $f3;
        $img = new \Image();
        return $img->captcha('Font/thunder.ttf', $size, $len, $this->captchaKey)->dump();
    }

    /**
     * 验证验证码是否正确
     *
     * @return boolean
     *
     * @param string  $captchaInput  用户输入的验证码字符串
     * @param boolean $caseSensitive 是否大小写敏感
     *
     * */
    public function validateCaptcha($captchaInput, $caseSensitive = true)
    {
        global $f3;
        $captcha = $f3->get($this->captchaKey);
        if (empty($captcha)) {
            return false;
        }

        if ($caseSensitive) {
            return $captcha == $captchaInput;
        }

        $captchaLowerCase      = strtolower($captcha);
        $captchaInputLowerCase = strtolower($captchaInput);
        return $captchaLowerCase == $captchaInputLowerCase;
    }

    /**
     * 返回当前的验证码
     *
     * @return string
     */
    public function getCaptcha()
    {
        global $f3;
        return $f3->get($this->captchaKey);
    }

    /**
     *
     * @param string $captchaKey  用于存储验证码的 key，比如放在 session['$key'] = 你的验证码
     *
     * */
    function __construct($captchaKey)
    {
        if (isset($captchaKey)) {
            $this->captchaKey = $captchaKey;
        }
    }

}
