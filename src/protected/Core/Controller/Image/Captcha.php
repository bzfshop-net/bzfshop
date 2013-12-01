<?php
/**
 * @author QiangYu
 *
 * 生成验证码的 Controller
 *
 * */

namespace Core\Controller\Image;

class Captcha
{

    protected $captchaKey = 'SESSION[CAPTCHA]';

    /**
     *
     * 根据用户的请求是否 ajax 生成不同的验证码
     *
     * */
    public function get($f3)
    {
        // 生成验证码
        $captchaHelper = new \Core\Helper\Image\Captcha($this->captchaKey);
        $image         = $captchaHelper->generateCaptcha();

        // 如果是 ajax 请求，则返回 json 数据
        if ($f3->get('AJAX') || $f3->get('REQUEST.ajax')) {
            header('Content-type: application/json');
            echo json_encode(array('data' => $f3->base64($image, 'image/png')));
            return;
        }

        // 直接输出图片
        header("Content-type: image/png");
        echo $image;
    }

    /**
     *
     * 验证验证码是否正确
     *
     * @return boolean
     *
     * */
    public function validateCaptcha($captchaInput)
    {
        $captchaHelper = new \Core\Helper\Image\Captcha($this->captchaKey);
        return $captchaHelper->validateCaptcha($captchaInput, false);
    }

    /**
     * 返回当前的验证码
     *
     * @return string
     */
    public function getCaptcha()
    {
        $captchaHelper = new \Core\Helper\Image\Captcha($this->captchaKey);
        return $captchaHelper->getCaptcha();
    }
}