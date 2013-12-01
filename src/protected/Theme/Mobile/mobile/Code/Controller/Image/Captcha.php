<?php
/**
 * @author QiangYu
 *
 * 生成验证码的 Controller
 *
 * */

namespace Controller\Image;

class Captcha extends \Core\Controller\Image\Captcha {

	function __construct(){
		$this->captchaKey = 'SESSION[MOBILE_CAPTCHA]';
	}

}