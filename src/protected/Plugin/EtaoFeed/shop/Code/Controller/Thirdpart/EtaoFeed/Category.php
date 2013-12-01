<?php
/**
 *
 * 生成商品的分类列表
 *
 */

namespace Controller\Thirdpart\EtaoFeed;

use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Validator;
use Core\Service\Goods\Category as GoodsCategoryService;
use Plugin\Thirdpart\EtaoFeed\EtaoFeedPlugin;

class Category extends \Controller\BaseController
{
    // 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
    protected $isHtmlController = false;

    public function get($f3)
    {
        global $smarty;

        $smartyCacheId = 'EtaoFeed|' . md5(__NAMESPACE__ . '\\' . __CLASS__ . '_\\' . __METHOD__);

        // 判断是否有缓存
        enableSmartyCache(true, 1800); // 缓存 30 分钟
        if ($smarty->isCached('empty.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // 取得商品分类树形结构
        $goodsCategoryService   = new GoodsCategoryService();
        $goodsCategoryTreeArray = $goodsCategoryService->fetchCategoryTreeArray(0);

        $currentStamp = Time::localTimeStr();
        $sellerId     = EtaoFeedPlugin::getOptionValue('etaofeed_seller_id');

        // 生成商品分类 XML
        $categoryXmlList = '';
        foreach ($goodsCategoryTreeArray as $goodsCategoryItem) {
            $categoryXmlList .= $this->getGoodsCategoryXml($goodsCategoryItem);
        }

        $apiXml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<root>
  <version>1.0</version>
  <modified>{$currentStamp}</modified>
  <seller_id>{$sellerId}</seller_id>
  <seller_cats>{$categoryXmlList}</seller_cats>
</root>
XML;

        $smarty->assign('outputContent', $apiXml);

        out_display:
        header('Content-Type:text/xml;charset=utf-8');
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 //查询信息
        $smarty->display('empty.tpl', $smartyCacheId);
    }

    public function post($f3)
    {
        // 把 post 的值都给 GET
        $get = $f3->get('GET');
        $get = array_merge($get, $f3->get('POST'));
        $f3->set('GET', $get);

        $this->get($f3);
    }

    private function getGoodsCategoryXml($goodsCategoryItem)
    {
        $categoryXml = '<cat>' .
            '<scid>' . $goodsCategoryItem['meta_id'] . '</scid>'
            . '<name><![CDATA[' . $goodsCategoryItem['meta_name'] . ']]></name><cats>';

        if (!empty($goodsCategoryItem['child_list'])) {
            $categoryXml .= $this->getSubGoodsCategoryXml($goodsCategoryItem['child_list']);
        }

        $categoryXml .= '</cats></cat>';
        return $categoryXml;
    }

    /**
     * 一淘 只支持 2 级分类，所以我们这里只取最后一级分类
     *
     * @param $goodsChildCategoryList
     *
     * @return string
     */
    private function getSubGoodsCategoryXml($goodsChildCategoryList)
    {
        $catXml = '';

        foreach ($goodsChildCategoryList as $goodsChildCategory) {
            if (!empty($goodsChildCategory['child_list'])) {
                $catXml .= $this->getSubGoodsCategoryXml($goodsChildCategory['child_list']);
            } else {
                // 最末一级分类
                $catXml .= '<cat><scid>' . $goodsChildCategory['meta_id'] . '</scid>'
                    . '<name><![CDATA[' . $goodsChildCategory['meta_name'] . ']]></name></cat>';
            }
        }

        return $catXml;
    }
}
