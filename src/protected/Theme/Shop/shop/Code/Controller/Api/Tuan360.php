<?php

/**
 * @author QiangYu
 *
 * 360团购导航 API
 *
 * */

namespace Controller\Api;

use Core\Helper\Image\StorageImage as StorageImageHelper;
use Core\Helper\Utility\Money;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Plugin\PluginHelper;
use Core\Service\Api\Goods as ApiGoodsService;

class Tuan360 extends \Controller\BaseController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    private $siteUrl;
    private $siteName;

    public function __construct()
    {
        // 由于输出商品巨大，我们需要大内存
        @ini_set('memory_limit', '512M');

        global $f3;
        $this->siteUrl  = $f3->get('sysConfig[webroot_url_prefix]');
        $this->siteName = $f3->get('sysConfig[site_name]');
    }

    private function getGoodsItemXml($index, $goodsItem, $goodsIdToGalleryArray)
    {
        global $f3;

        static $buyNotice = "【下单说明】请在下单时留言注明尺码准确填写姓名、电话和收件地址!
【发货说明】下单后48小时内发货，快递3-5天左右到达，节假日顺延。偏远地区需要补10元邮费。
【关于尺寸】因测量手法问题，测量数据可能存在2-3CM误差，还请见谅！
【关于色差】颜色因场景拍摄及电脑显示有差异均属于正常，不属于质量问题。图色显示与实物颜色我们保证以最接近实物的颜色上传。
【关于签收】请务必本人签收。验货后，如商品有任何破损问题请当快递员面拒收！";

        $goodsTitle = mb_substr($goodsItem['goods_name_short'], 0, 20);

        $featureWords = "服饰,生活用品,礼盒,箱包,保暖,衣服,鞋子,女性,其他";

        if (!empty($goodsItem['360tuan_feature'])) {
            $featureWordArray = preg_split('#\s+#', $goodsItem['360tuan_feature']);
            $featureWords     = implode(',', $featureWordArray);
        }

        $goodsViewUrl = '';

        // 不在商城的商品一律指向团购
        if (!Utils::isTagExist(PluginHelper::SYSTEM_SHOP, $goodsItem['system_tag_list'])
        ) {
            $goodsViewUrl = RouteHelper::makeShopSystemUrl(
                PluginHelper::SYSTEM_GROUPON,
                '/Goods/View',
                array('goods_id' => $goodsItem['goods_id'])
            );
        } else {
            $goodsViewUrl =
                RouteHelper::makeUrl('/Goods/View', array('goods_id' => $goodsItem['goods_id']), false, true);
        }
        $goodsViewUrl = RouteHelper::addParam($goodsViewUrl, array('utm_source' => '360tuan'));

        $goodsWapViewUrl = RouteHelper::makeShopSystemUrl(
            PluginHelper::SYSTEM_MOBILE,
            '/Goods/View',
            array('goods_id' => $goodsItem['goods_id'])
        );
        $goodsWapViewUrl = RouteHelper::addParam($goodsWapViewUrl, array('utm_source' => '360tuan'));

        $goodsWapBuyUrl = RouteHelper::makeShopSystemUrl(
            PluginHelper::SYSTEM_MOBILE,
            '/Thirdpart/Tuan360CpsWap/GoodsBuy',
            array('goods_id' => $goodsItem['goods_id'])
        );
        $goodsWapBuyUrl = RouteHelper::addParam($goodsWapBuyUrl, array('utm_source' => '360tuan'));

        $goodsImageUrl = '';
        $bigImageUrl   = '';

        // 给 360 的头图
        if (!empty($goodsItem['360tuan_image'])) {

            // 上传了专门的 360 头图，我们用头图
            $goodsImageUrl = RouteHelper::makeImageUrl($goodsItem['360tuan_image']);

        } elseif (isset($goodsIdToGalleryArray[$goodsItem['goods_id']])) {

            // 从现有的商品头图中 crop 出来一个给 360
            $imagePath = $goodsIdToGalleryArray[$goodsItem['goods_id']][0]['img_url'];
            //如果是绝对路径，直接返回
            if (RouteHelper::isUrlAbsolute($imagePath)) {
                $goodsImageUrl = $imagePath;
            } else {
                // 切图片，保持和 360 的长宽比例
                $goodsImageUrl = RouteHelper::makeImageUrl(
                    StorageImageHelper::cropImageIfNotExist(
                        $f3->get('sysConfig[data_path_root]'),
                        $imagePath,
                        460,
                        276
                    )
                );
            }

        } else {
            // 没图片可以用
        }

        // 给 360 手机端用户的 bigImage
        if (isset($goodsIdToGalleryArray[$goodsItem['goods_id']])) {
            $bigImageUrl = RouteHelper::makeImageUrl($goodsIdToGalleryArray[$goodsItem['goods_id']][0]['img_url']);
        }

        // 解析 pins 图
        $pinsXml       = '';
        $pinImageArray = explode("\n", preg_replace('/\r\n/', "\n", $goodsItem['360tuan_pin_images']));
        foreach ($pinImageArray as $pinImageItem) {
            $pinImageItem = trim($pinImageItem);
            if (empty($pinImageItem)) {
                //跳过空行
                continue;
            }
            $pinsXml .= '<pin><![CDATA[' . $pinImageItem . ']]></pin>';
        }

        // 购买数量
        $salesNum = $goodsItem['virtual_buy_number'] + $goodsItem['user_pay_number'];

        // 转换价格显示
        $goodsItem['market_price'] = Money::toSmartyDisplay($goodsItem['market_price']);
        $goodsItem['shop_price']   = Money::toSmartyDisplay($goodsItem['shop_price']);

        $rebate = 0;
        if ($goodsItem['market_price'] > 0) {
            $rebate = 10 * round($goodsItem['shop_price'] / $goodsItem['market_price'], 2);
        }

        $today           = strtotime(date('Ymd'));
        $twoDaysLater    = $today + 86400 * 2;
        $todayStr        = date('YmdHis', $today);
        $twoDaysLaterStr = date('YmdHis', $twoDaysLater);

        $xmlitem = <<<XMLITEM
	<goods id="{$index}">
		<pid>{$goodsItem['goods_id']}</pid>
		<feature><![CDATA[$featureWords]]></feature>
		<city_name>全国</city_name>
		<site_url><![CDATA[{$this->siteUrl}]]></site_url>
		<title><![CDATA[{$goodsTitle}]]></title>
		<goods_url><![CDATA[{$goodsViewUrl}]]></goods_url>
		<goods_wapurl><![CDATA[{$goodsWapViewUrl}]]></goods_wapurl>
		<wap_buyurl><![CDATA[{$goodsWapBuyUrl}]]></wap_buyurl>
		<desc><![CDATA[{$goodsItem['goods_name']}]]></desc>
		<tip><![CDATA[{$buyNotice}]]></tip>
		<class><![CDATA[{$goodsItem['360tuan_category']}]]></class>
        <end_class><![CDATA[{$goodsItem['360tuan_category_end']}]]></end_class>
		<img_url><![CDATA[{$goodsImageUrl}]]></img_url>
		<bigimg_url><![CDATA[{$bigImageUrl}]]></bigimg_url>
		<pins>{$pinsXml}</pins>
        <original_price>{$goodsItem['market_price']}</original_price>
        <sale_price>{$goodsItem['shop_price']}</sale_price>
        <sale_rate>{$rebate}</sale_rate>
        <sales_num>{$salesNum}</sales_num>
		<start_time>{$todayStr}</start_time>
		<close_time>{$twoDaysLaterStr}</close_time>
		<spend_start_time>0</spend_start_time>
        <spend_close_time>0</spend_close_time>

		<merchant_name>{$this->siteName}</merchant_name>
		<merchant_tel>010-83487737</merchant_tel>
		<merchant_addr>北京市海淀区中关村高科技园区</merchant_addr>
        <reservation>0</reservation>

		<merchants>
			<merchant>
				<name>{$this->siteName}</name>
				<tel>010-83487737</tel>
				<addr>北京市海淀区中关村高科技园区</addr>
			</merchant>
		</merchants>

        </goods>
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

        $smartyCacheId = 'Api|' . md5(__NAMESPACE__ . '\\' . __CLASS__ . '_\\' . __METHOD__);

        // 判断是否有缓存
        if ($smarty->isCached('empty.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // 查询商品
        $condArray =
            array(
                array(
                    QueryBuilder::buildGoodsFilterForSystem(
                        array(PluginHelper::SYSTEM_GROUPON, PluginHelper::SYSTEM_SHOP)
                    )
                )
            );

        $apiGoodsService          = new ApiGoodsService();
        $goodsGalleryPromoteArray =
            $apiGoodsService->fetchGoodsGalleryPromote(
                $condArray,
                '360tuan_sort_order desc, sort_order desc, goods_id desc',
                0,
                0,
                $cacheTime
            );

        // 商品和图片
        $goodsArray            = $goodsGalleryPromoteArray['goods'];
        $goodsIdToGalleryArray = $goodsGalleryPromoteArray['goodsIdToGalleryArray'];

        $siteName = $f3->get('sysConfig[site_name]');

        $wapOrderUrl = RouteHelper::makeShopSystemUrl(PluginHelper::SYSTEM_MOBILE, '/Thirdpart/Tuan360CpsWap/MyOrder');

        $xmlItems        = '';
        $goodsArrayCount = count($goodsArray);
        for ($index = 0; $index < $goodsArrayCount; $index++) {
            $xmlItems .= $this->getGoodsItemXml($index + 1, $goodsArray[$index], $goodsIdToGalleryArray);
        }

        $apiXml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<data>
	<apiversion>4.0</apiversion>
	<site_name>{$siteName}</site_name>
	<wap_orderurl>{$wapOrderUrl}</wap_orderurl>
	<goodsdata>{$xmlItems}</goodsdata>
</data>
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
