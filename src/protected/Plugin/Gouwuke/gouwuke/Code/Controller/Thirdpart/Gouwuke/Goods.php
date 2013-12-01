<?php

/**
 * @author QiangYu
 *
 * 亿起发 购物客 API
 *
 * */

namespace Controller\Thirdpart\Gouwuke;

use Core\Helper\Utility\Money;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Plugin\ThemeHelper;
use Core\Service\Api\Goods as ApiGoodsService;
use Core\Service\Goods\Category as GoodsCategoryService;

class Goods extends \Controller\BaseController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    private $siteUrl;

    private $siteIdentify;

    private $goodsCategoryService;

    public function __construct()
    {
        // 由于输出商品巨大，我们需要大内存
        @ini_set('memory_limit', '256M');

        global $f3;
        $this->siteUrl              = $f3->get('sysConfig[webroot_url_prefix]');
        $this->siteIdentify         = str_replace('.', '-', $f3->get('HOST'));
        $this->goodsCategoryService = new GoodsCategoryService();
    }

    private function getGoodsItemXml($index, $goodsItem, $goodsIdToGalleryArray)
    {
        global $f3;

        static $buyNotice = "【下单说明】请在下单时留言注明尺码准确填写姓名、电话和收件地址!
【发货说明】下单后48小时内发货，快递3-5天左右到达，节假日顺延。偏远地区需要补10元邮费。
【关于尺寸】因测量手法问题，测量数据可能存在2-3CM误差，还请见谅！
【关于色差】颜色因场景拍摄及电脑显示有差异均属于正常，不属于质量问题。图色显示与实物颜色我们保证以最接近实物的颜色上传。
【关于签收】请务必本人签收。验货后，如商品有任何破损问题请当快递员面拒收！";

        $siteName = $f3->get('sysConfig[site_name]');

        $goodsViewUrl = RouteHelper::makeUrl('/Goods/View', array('goods_id' => $goodsItem['goods_id']), false, true);
        $goodsViewUrl = RouteHelper::addParam($goodsViewUrl, array('utm_source' => 'gouwuke'));

        $goodsImageUrlList = '<picurls>';
        $goodsGalleryArray = @$goodsIdToGalleryArray[$goodsItem['goods_id']];
        if (!empty($goodsGalleryArray)) {
            foreach ($goodsGalleryArray as $goodsGalleryItem) {
                $goodsImageUrlList .= '<picurllist>';
                $goodsImageUrlList .= '<picurl><![CDATA['
                    . RouteHelper::makeImageUrl($goodsGalleryItem['img_url'])
                    . ']]></picurl>';
                $goodsImageUrlList .= '<bigpicurl><![CDATA['
                    . RouteHelper::makeImageUrl($goodsGalleryItem['img_original'])
                    . ']]></bigpicurl>';
                $goodsImageUrlList .= '</picurllist>';
            }
        }
        $goodsImageUrlList .= '</picurls>';

        // 取得商品的分类层级
        $goodsCategoryLevelArray = array();
        $categoryLevel           = 5; // 最多取 5 层分类
        $currentCategoryId       = $goodsItem['cat_id'];
        for (; $categoryLevel > 0; $categoryLevel--) {
            $category = $this->goodsCategoryService->loadCategoryById($currentCategoryId, 1800);
            if ($category->isEmpty()) {
                break;
            }
            array_unshift($goodsCategoryLevelArray, $category);
            if ($category['parent_meta_id'] <= 0) {
                break;
            }
            $currentCategoryId = $category['parent_meta_id'];
        }

        $goodsCategoryLevelStr = '';
        foreach ($goodsCategoryLevelArray as $goodsCategoryItem) {
            $goodsCategoryLevelStr .= $goodsCategoryItem['meta_name'] . ' > ';
        }
        $goodsCategoryLevelStr .= '当前商品';

        // 转换价格显示
        $goodsItem['market_price'] = Money::toSmartyDisplay($goodsItem['market_price']);
        $goodsItem['shop_price']   = Money::toSmartyDisplay($goodsItem['shop_price']);

        $xmlitem = <<<XMLITEM
	<urlset>
	    <ident><![CDATA[{$this->siteIdentify}_{$goodsItem['goods_id']}]]></ident>
	    <productname><![CDATA[{$goodsItem['goods_name']}]]></productname>
	    <refprice>{$goodsItem['market_price']}</refprice>
        <price_1>{$goodsItem['shop_price']}</price_1>
        <zhekou_price><![CDATA[]]></zhekou_price>
        <zhekou><![CDATA[]]></zhekou>
        <ifcuxiao><![CDATA[false]]></ifcuxiao>
        <quehuo><![CDATA[false]]></quehuo>
        {$goodsImageUrlList}
	    <url><![CDATA[{$goodsViewUrl}]]></url>
	    <shortintro><![CDATA[{$goodsItem['goods_name']}]]></shortintro>
	    <shortintrohtml><![CDATA[{$buyNotice}]]></shortintrohtml>
	    <orifenlei><![CDATA[{$goodsCategoryLevelStr}]]></orifenlei>
	    <pinpai><![CDATA[]]></pinpai>
	    <color><![CDATA[]]></color>
        <chandi><![CDATA[]]></chandi>
    </urlset>
XMLITEM;
        return $xmlitem;

    }

    public function get($f3)
    {
        global $smarty;

        $noCache   = @$_GET['nocache'];
        $cacheTime = 300; // 缓存5分钟
        if (!empty($noCache)) {
            $cacheTime = 2; // 缓存 2 秒钟，防止被过度调用从而变成一种攻击
            enableSmartyCache(true, $cacheTime, \Smarty::CACHING_LIFETIME_CURRENT);
        } else {
            enableSmartyCache(true, $cacheTime);
        }

        $smartyCacheId = 'Api|' . md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);

        // 判断是否有缓存
        if ($smarty->isCached('gouwuke_empty.tpl', $smartyCacheId)) {
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
                'sort_order desc, goods_id desc',
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
<boot>
	{$xmlItems}
</boot>
XML;

        unset($xmlItems);
        $smarty->assign('outputContent', $apiXml);

        out_display:
        header('Content-Type:text/xml;charset=utf-8');
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 //查询信息
        $smarty->display('gouwuke_empty.tpl', $smartyCacheId);
    }

    public function post($f3)
    {
        $this->get($f3);
    }
}
