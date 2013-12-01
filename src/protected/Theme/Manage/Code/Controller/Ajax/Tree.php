<?php

/**
 * @author QiangYu
 *
 * 方便取得 Meta Tree 中的数据
 *
 * */

namespace Controller\Ajax;

use Core\Helper\Utility\Ajax;
use Core\Helper\Utility\Validator;
use Core\Service\Meta\Tree as MetaTreeService;

class Tree extends \Controller\AuthController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    public function ListTreeNode($f3)
    {
        // 参数验证
        $validator = new Validator($f3->get('GET'));

        $errorMessage = '';

        $treeKey  = $validator->required()->validate('treeKey');
        $parentId = $validator->digits()->min(0)->validate('parentId');
        $parentId = $parentId ? : 0;

        if (!$this->validate($validator)) {
            $errorMessage = implode('|', $this->flashMessageArray);
            goto out_fail;
        }

        // 检查缓存
        $cacheKey      = md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__ . '\\' . $treeKey . '\\' . $parentId);
        $treeNodeArray = $f3->get($cacheKey);
        if (!empty($treeNodeArray)) {
            goto out;
        }

        $metaTreeService = new MetaTreeService();
        $treeNodeArray   = $metaTreeService->fetchTreeNodeArray($treeKey, $parentId);

        $f3->set($cacheKey, $treeNodeArray, 300); //缓存 5 分钟

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, $treeNodeArray);
        return;

        out_fail: // 失败，返回出错信息
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }


    public function ListChildTreeNodeAllStr($f3)
    {
        // 参数验证
        $validator = new Validator($f3->get('GET'));

        $errorMessage = '';

        $treeKey  = $validator->required()->validate('treeKey');
        $parentId = $validator->digits()->min(0)->validate('parentId');
        $parentId = $parentId ? : 0;
        // 用户也可以通过 treeNodeName 来做查询
        $treeNodeName = $validator->validate('treeNodeName');

        if (!$this->validate($validator)) {
            $errorMessage = implode('|', $this->flashMessageArray);
            goto out_fail;
        }

        // 检查缓存
        $cacheKey = md5(
            __NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__ . '\\' . $treeKey . '\\' . $parentId . '\\'
            . $treeNodeName
        );

        $outputArray = $f3->get($cacheKey);
        if (!empty($outputArray)) {
            goto out;
        }

        $metaTreeService = new MetaTreeService();

        if (!empty($treeNodeName)) {
            $treeNode = $metaTreeService->loadTreeNodeWithTreeKeyAndName($treeKey, $treeNodeName);
            if (!$treeNode->isEmpty()) {
                $parentId = $treeNode['meta_id'];
            }
        }

        // 取得树形的层级结构
        $treeNodeArray = $metaTreeService->fetchChildTreeNodeArrayAll($treeKey, $parentId);

        // 构建显示输出
        $outputArray = array();

        function buildHierarchyArray(&$outputArray, $treeNodeArray, $separator = '')
        {
            $hierarchySeparator = '---------->';
            foreach ($treeNodeArray as $treeNodeItem) {
                $outputItem                 = array();
                $outputItem['meta_id']      = $treeNodeItem['meta_id'];
                $outputItem['meta_name']    = $treeNodeItem['meta_name'];
                $outputItem['display_text'] = $separator . $treeNodeItem['meta_name'];
                $outputArray[]              = $outputItem;

                // 有子节点，递归建立子节点
                if (isset($treeNodeItem['child_list'])) {
                    buildHierarchyArray($outputArray, $treeNodeItem['child_list'], $separator . $hierarchySeparator);
                }
            }
        }

        buildHierarchyArray($outputArray, $treeNodeArray, '');

        $f3->set($cacheKey, $outputArray, 600); //缓存 10 分钟

        out:
        Ajax::header();
        echo Ajax::buildResult(null, null, $outputArray);
        return;

        out_fail: // 失败，返回出错信息
        Ajax::header();
        echo Ajax::buildResult(-1, $errorMessage, null);
    }

}
