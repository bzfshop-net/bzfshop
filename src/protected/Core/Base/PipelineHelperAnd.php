<?php
/**
 * Pipeline Helper 基类
 *
 * 很多时候，我们需要做一串操作，比如 清除缓存，涉及到 Groupon, Shop, Mobile, ...
 * 这个 Helper 负责调用各个 注册 的 instance 做统一的操作
 *
 * 实现了 Pipeline 的 And 逻辑，即：只有这个 pipeline 运行成功，我们才会继续下一个 pipline 的执行
 *
 * @author QiangYu
 */

namespace Core\Base;

abstract class PipelineHelperAnd extends RegisterHelper
{
    /**
     * 调用对应的对象方法
     *
     * @param       $func
     * @param array $args
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    static function __callstatic($func, array $args)
    {
        $ret = true;

        foreach (static::$registerClassArray as $className => $initParam) {
            $instance = static::loadClassInstance($className, $initParam);
            // 检查方法是否存在
            if (!method_exists($instance, $func)) {
                throw new \InvalidArgumentException('class [' . $className . '] has no method [' . $func . ']');
            }

            global $f3;
            if ($f3->get('DEBUG') > 2) {
                printLog('call [' . $className . '] method [' . $func . ']', __CLASS__, \Core\Log\Base::DEBUG);
            }

            // 调用对象方法
            $ret = ($ret && call_user_func_array(array($instance, $func), $args));

            // 释放内存
            unset($instance);

            // 如果当前 pipeline 执行失败，我们就不再往下执行了
            if (!$ret) {
                break;
            }
        }

        return $ret;
    }
}