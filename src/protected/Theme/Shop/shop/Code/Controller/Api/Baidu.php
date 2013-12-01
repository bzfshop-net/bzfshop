<?php

/**
 * @author QiangYu
 *
 * Baidu 团购导航 API
 *
 * */

namespace Controller\Api;

use Core\Helper\Utility\Money;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Plugin\ThemeHelper;
use Core\Service\Api\Goods as ApiGoodsService;

class Baidu extends \Controller\BaseController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    private $siteUrl;

    public function __construct()
    {
        // 由于输出商品巨大，我们需要大内存
        @ini_set('memory_limit', '256M');

        global $f3;
        $this->siteUrl = $f3->get('sysConfig[webroot_url_prefix]');
    }

    private function getGoodsItemXml($index, $goodsItem, $goodsIdToGalleryArray)
    {
        global $f3;

        $siteName = $f3->get('sysConfig[site_name]');

        $goodsViewUrl = RouteHelper::makeUrl('/Goods/View', array('goods_id' => $goodsItem['goods_id']), false, true);
        $goodsViewUrl = RouteHelper::addParam($goodsViewUrl, array('utm_source' => 'baidutuan'));

        $goodsImageUrl = '';
        if (isset($goodsIdToGalleryArray[$goodsItem['goods_id']])) {
            $goodsImageUrl = RouteHelper::makeImageUrl($goodsIdToGalleryArray[$goodsItem['goods_id']][0]['img_url']);
        }

        // 购买数量
        $bought = $goodsItem['virtual_buy_number'] + $goodsItem['user_pay_number'];

        // 转换价格显示
        $goodsItem['market_price'] = Money::toSmartyDisplay($goodsItem['market_price']);
        $goodsItem['shop_price']   = Money::toSmartyDisplay($goodsItem['shop_price']);

        $rebate = 0;
        if ($goodsItem['market_price'] > 0) {
            $rebate = 10 * round($goodsItem['shop_price'] / $goodsItem['market_price'], 2);
        }

        $today        = strtotime(date('Ymd'));
        $twoDaysLater = $today + 86400 * 2;

        $xmlitem = <<<XMLITEM
	<url>
	    <loc><![CDATA[{$goodsViewUrl}]]></loc>
	    <data><display>
		<website><![CDATA[{$siteName}]]></website>
		<siteurl><![CDATA[{$this->siteUrl}]]></siteurl>
		<city>全国</city>
		<title><![CDATA[{$goodsItem['goods_name']}]]></title>
		<image><![CDATA[{$goodsImageUrl}]]></image>
		<startTime>{$today}</startTime>
		<endTime>{$twoDaysLater}</endTime>
        <value>{$goodsItem['market_price']}</value>
        <price>{$goodsItem['shop_price']}</price>
        <rebate>{$rebate}</rebate>
        <bought>{$bought}</bought>
        </display></data>
        </url>
XMLITEM;
        return $xmlitem;

    }

    public function get($f3)
    {
        global $smarty;

        $cacheTime = 300; // 缓存5分钟
        enableSmartyCache(true, $cacheTime);

        $smartyCacheId = 'Api|' . md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);

        // 判断是否有缓存
        if ($smarty->isCached('empty.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // 查询商品
        $currentThemeInstance = ThemeHelper::getCurrentSystemThemeInstance();
        $condArray            =
            array(array(QueryBuilder::buildGoodsFilterForSystem($currentThemeInstance->getGoodsFilterSystemArray())));

        $apiGoodsService          = new ApiGoodsService();
        $goodsGalleryPromoteArray =
            $apiGoodsService->fetchGoodsGalleryPromote(
                $condArray,
                'aimeidaren_sort_order desc, sort_order desc, goods_id desc',
                0,
                0,
                $cacheTime
            );

        // 商品和图片
        $goodsArray            = $goodsGalleryPromoteArray['goods'];
        $goodsIdToGalleryArray = $goodsGalleryPromoteArray['goodsIdToGalleryArray'];

        $xmlItems        = '';
        $goodsArrayCount = count($goodsArray);
        for ($index = 0; $index < $goodsArrayCount; $index++) {
            $xmlItems .= $this->getGoodsItemXml($index + 1, $goodsArray[$index], $goodsIdToGalleryArray);
        }

        $apiXml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<urlset>
	{$xmlItems}
</urlset>
XML;

        unset($xmlItems);
        $smarty->assign('outputContent', $apiXml);

        out_display:
        header('Content-Type:text/xml;charset=utf-8');
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 //查询信息
        $smarty->display('empty.tpl', $smartyCacheId);
    }

    public function post($f3)
    {
        $this->get($f3);
    }
}
