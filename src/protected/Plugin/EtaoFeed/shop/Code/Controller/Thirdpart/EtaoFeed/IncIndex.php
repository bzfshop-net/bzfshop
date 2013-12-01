<?php
/**
 *
 * 增量索引
 *
 */

namespace Controller\Thirdpart\EtaoFeed;


use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Plugin\ThemeHelper;
use Core\Search\SearchHelper;
use Plugin\Thirdpart\EtaoFeed\EtaoFeedPlugin;

class IncIndex extends \Controller\BaseController
{
    // 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
    protected $isHtmlController = false;

    public function __construct()
    {
        // URL 输出为动态值
        RouteHelper::$isMakeStaticUrl = false;
    }

    public function get($f3)
    {
        global $smarty;

        $smartyCacheId = 'EtaoFeed|' . md5(__NAMESPACE__ . '\\' . __CLASS__ . '_\\' . __METHOD__);

        // 判断是否有缓存
        enableSmartyCache(true, 1200); // 缓存 20 分钟
        if ($smarty->isCached('empty.tpl', $smartyCacheId)) {
            goto out_display;
        }

        $currentStamp = Time::localTimeStr();
        $sellerId     = EtaoFeedPlugin::getOptionValue('etaofeed_seller_id');
        $categoryUrl  = RouteHelper::makeUrl('/Thirdpart/EtaoFeed/Category', null, false, true);
        $itemDir      = RouteHelper::makeUrl('/Thirdpart/EtaoFeed/Item', null, false, true);

        $itemIdXmlList = '';

        // 处理 delete 的商品
        $currentThemeInstance = ThemeHelper::getCurrentSystemThemeInstance();
        $totalGoodsCount      = SearchHelper::count(
            SearchHelper::Module_Goods,
            array(
                 array('is_on_sale = 0'),
                 array('update_time', '>=', EtaoFeedPlugin::getOptionValue('etaofeed_query_timestamp')),
                 array(QueryBuilder::buildGoodsFilterForSystem($currentThemeInstance->getGoodsFilterSystemArray()))
            )
        );

        if ($totalGoodsCount <= 0) {
            goto query_update_goods;
        }

        $totalPageCount = ceil($totalGoodsCount / Item::$pageSize);
        for ($index = 0; $index < $totalPageCount; $index++) {
            $itemIdXmlList .= '<outer_id action="delete">1' . $index . '</outer_id>';
        }

        query_update_goods:

        // 处理修改过的商品
        $totalGoodsCount = SearchHelper::count(
            SearchHelper::Module_Goods,
            array(
                 array('is_on_sale = 1'),
                 array('update_time', '>=', EtaoFeedPlugin::getOptionValue('etaofeed_query_timestamp')),
                 array(QueryBuilder::buildGoodsFilterForSystem($currentThemeInstance->getGoodsFilterSystemArray()))
            )
        );

        if ($totalGoodsCount <= 0) {
            goto out_output;
        }

        $totalPageCount = ceil($totalGoodsCount / Item::$pageSize);
        for ($index = 0; $index < $totalPageCount; $index++) {
            $itemIdXmlList .= '<outer_id action="upload">2' . $index . '</outer_id>';
        }

        out_output:

        $apiXml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<root>
  <version>1.0</version>
  <modified>{$currentStamp}</modified>
  <seller_id>{$sellerId}</seller_id>
  <cat_url>{$categoryUrl}</cat_url>
  <dir>{$itemDir}/</dir>
  <item_ids>{$itemIdXmlList}</item_ids>
</root>
XML;

        $smarty->assign('outputContent', $apiXml);

        // 更新查询时间
        //EtaoFeedPlugin::saveOptionValue('etaofeed_query_timestamp', Time::gmTime());

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

}
