<?php

/**
 * @author QiangYu
 *
 * 所有 Controller 的基类
 *
 * */

namespace Core\Controller;

use Core\Helper\Utility\Validator;

class BaseController
{
    /**
     * 当前是否为 html Controller
     */
    protected $isHtmlController = true;

    public function isHtmlController()
    {
        return $this->isHtmlController;
    }

    /**
     * 用于返回到页面的消息提示
     * */
    protected $flashMessageArray = array();

    /**
     * 添加一条 flash message
     *
     * @param string $msg 消息内容
     * */
    public function addFlashMessage($msg)
    {
        // 最多放 20 条消息，太多了会超过 Cookie 的最大长度
        if (count($this->flashMessageArray) < 20) {
            $this->flashMessageArray[] = $msg;
        }
    }

    /**
     * 批量加入 flash message
     *
     * @param $messageArray
     */
    public function addFlashMessageArray($messageArray)
    {
        foreach ($messageArray as $message) {
            if (is_scalar($message)) {
                $this->addFlashMessage($message);
            }
        }
    }

    /**
     * 取得 flash message 数组
     *
     * @return array
     */
    public function getFlashMessageArray()
    {
        return $this->flashMessageArray;
    }

    /**
     * 验证是否有失败的 validate，失败的 validate 对应的消息会自动被添加到 flash message 中
     *
     * @return boolean
     *
     * @param object $validator  validator 对象
     * */
    protected function validate(Validator $validator)
    {
        $hasError = $validator->hasErrors();

        if (!$hasError) {
            // 没有错误，成功返回
            return true;
        }

        // 有错误，把错误消息放入到 flash Message 中
        $errorArray = $validator->getAllErrors();
        foreach ($errorArray as $errorField => $errorMsg) {
            $this->addFlashMessage($errorMsg);
        }

        return false;
    }

    public function beforeRoute($f3)
    {
        global $f3;
        global $smarty;

        /**
         *
         * 很多时候，我们发现页面有错误就需要返回页面给用户，同时保证用户之前提交的信息仍然保留
         * 这里我们自动把用户 提交的数据复制到 smarty 中，确保页面回显的时候这些变量都还在
         *
         * */
        $smarty->assign($f3->get('GET'));
        $smarty->assign($f3->get('POST'));

        // 是否是处于调试模式，方便页面做一些特殊的显示
        $smarty->assign('DEBUG', $f3->get('DEBUG'));

        // 设置当前的 controller，方便在模板中使用
        $smarty->assign('currentController', $this);
        $f3->set('currentController', $this);

        // 优化网页输出，去除所有 html 注释和多余的空格，让网页更精简
        if ($this->isHtmlController && !$smarty->debugging && !$f3->get('DEBUG')) {
            $smarty->loadFilter("output", "trimwhitespace");
        }
    }

    public function afterRoute($f3)
    {
        //do nothing
    }

}
