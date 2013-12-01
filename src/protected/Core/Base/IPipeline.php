<?php
/**
 * @author QiangYu
 *
 * 流水处理基础接口
 */

namespace Core\Base;

interface  IPipeline
{
    /**
     * 执行流水处理
     *
     * @return mixed  执行失败返回 false， 执行成功返回值取决于自定义的实现
     *
     */
    public function execute($pipelineContext);
}
