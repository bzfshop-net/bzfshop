<?php

/**
 * @author QiangYu
 *
 * 实现 And 逻辑，有一个失败就返回
 */

namespace Core\Base;

class ListAndPipeline implements IPipeline
{

    public $pipelineArray;

    public function execute(&$pipelineContext)
    {

        foreach ($this->pipelineArray as $pipeline) {
            $ret = false;
            try {
                if (!$pipeline instanceof IPipeline) {
                    return false;
                }
                $ret = $pipeline->execute($pipelineContext);
            } catch (Exception $e) {
                return false;
            }

            if (false === $ret) {
                return false;
            }
        }

        return true;
    }

}