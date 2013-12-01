<?php
/**
 *
 * 生成商品的分类列表
 *
 */

namespace Controller\Thirdpart\EtaoFeed;

use Core\Helper\Utility\Money;
use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Utils;
use Core\Helper\Utility\Validator;
use Core\Plugin\ThemeHelper;
use Core\Search\SearchHelper;
use Core\Service\Goods\Gallery as GoodsGalleryService;
use Plugin\Thirdpart\EtaoFeed\EtaoFeedPlugin;

class Item extends \Controller\BaseController
{
    // 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
    protected $isHtmlController = false;

    // 每页商品数据量
    public static $pageSize = 1000;

    // 商家账号
    private $sellerId = '';

    // 字段过滤
    private $fieldSelector = 'goods_id, goods_sn, cat_id, goods_name, shop_price, goods_desc, shipping_fee';

    public function __construct()
    {
        $this->sellerId = EtaoFeedPlugin::getOptionValue('etaofeed_seller_id');
    }

    public function get($f3)
    {
        global $smarty;

        // 解析传入的 fileName, 文件名的格式应该是 /1.xml，最后的数字为页号
        $fileName = $f3->get('PARAMS.fileName');

        $smartyCacheId = 'EtaoFeed|' . md5(__NAMESPACE__ . '\\' . __CLASS__ . '_\\' . __METHOD__ . '\\' . $fileName);

        // 判断是否有缓存
        enableSmartyCache(true, 1200); // 缓存 20 分钟
        if ($smarty->isCached('empty.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // 文件一般为  00.xml 10.xml 20.xml
        // 第一个数字 0: upload,  1: delete , 2: update

        //去掉文件扩展名
        $extPos = strrpos($fileName, '.');
        if ($extPos) {
            $fileName = substr($fileName, 0, $extPos);
        }

        // 解析文件名
        $itemType = intval($fileName[0]);
        $pageNo   = intval(substr($fileName, 1));

        // 根据调用类型查询商品
        $goodsArray = array();
        switch ($itemType) {
            case 0:
                $goodsArray = $this->queryUploadGoods($pageNo);
                break;
            case 1:
                $goodsArray = $this->queryDeleteGoods($pageNo);
                break;
            case 2:
                $goodsArray = $this->queryUpdateGoods($pageNo);
                break;
            default:
                break;
        }

        $apiXml = $this->getGoodsArrayXml($goodsArray);

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


    /**
     * 取得所有的商品
     *
     * @param $pageNo
     */
    private function queryUploadGoods($pageNo)
    {
        $currentThemeInstance = ThemeHelper::getCurrentSystemThemeInstance();
        return SearchHelper::search(
            SearchHelper::Module_Goods,
            $this->fieldSelector,
            array(
                 array('is_on_sale = 1'),
                 array(QueryBuilder::buildGoodsFilterForSystem($currentThemeInstance->getGoodsFilterSystemArray()))
            ),
            array(array('g.goods_id', 'desc')),
            $pageNo * self::$pageSize,
            self::$pageSize
        );
    }

    /**
     * 查询更新过的商品
     *
     * @param $pageNo
     *
     * @return mixed
     */
    private function queryUpdateGoods($pageNo)
    {
        $currentThemeInstance = ThemeHelper::getCurrentSystemThemeInstance();
        return SearchHelper::search(
            SearchHelper::Module_Goods,
            $this->fieldSelector,
            array(
                 array('is_on_sale = 1'),
                 array('update_time', '>=', EtaoFeedPlugin::getOptionValue('etaofeed_query_timestamp')),
                 array(QueryBuilder::buildGoodsFilterForSystem($currentThemeInstance->getGoodsFilterSystemArray()))
            ),
            array(array('g.goods_id', 'desc')),
            $pageNo * self::$pageSize,
            self::$pageSize
        );
    }

    /**
     * 查询下线的商品
     *
     * @param $pageNo
     *
     * @return mixed
     */
    private function queryDeleteGoods($pageNo)
    {
        $currentThemeInstance = ThemeHelper::getCurrentSystemThemeInstance();
        return SearchHelper::search(
            SearchHelper::Module_Goods,
            $this->fieldSelector,
            array(
                 array('is_on_sale = 0'),
                 array('update_time', '>=', EtaoFeedPlugin::getOptionValue('etaofeed_query_timestamp')),
                 array(QueryBuilder::buildGoodsFilterForSystem($currentThemeInstance->getGoodsFilterSystemArray()))
            ),
            array(array('g.goods_id', 'desc')),
            $pageNo * self::$pageSize,
            self::$pageSize
        );
    }

    private function getGoodsArrayXml($goodsArray)
    {
        $itemXmlList = '';

        // 没有商品，退出
        if (empty($goodsArray)) {
            goto out_output;
        }

        // 查询商品图片
        $goodsIdArray = array();
        foreach ($goodsArray as $goodsItem) {
            $goodsIdArray[] = $goodsItem['goods_id'];
        }

        $goodsGalleryService = new GoodsGalleryService();
        $goodsGalleryArray   = $goodsGalleryService->fetchGoodsGalleryArrayByGoodsIdArray($goodsIdArray);

        // 建立 goods_id --> goods_gallery 的反查表
        $goodsIdToGalleryArray = array();
        foreach ($goodsGalleryArray as $goodsGalleryItem) {
            if (!isset($goodsIdToGalleryArray[$goodsGalleryItem['goods_id']])) {
                $goodsIdToGalleryArray[$goodsGalleryItem['goods_id']] = array();
            }
            $goodsIdToGalleryArray[$goodsGalleryItem['goods_id']][] = $goodsGalleryItem;
        }

        // 生成 商品列表
        foreach ($goodsArray as $goodsItem) {
            $itemXmlList .= $this->getGoodsItemXml($goodsItem, $goodsIdToGalleryArray);
        }

        out_output:

        $apiXml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<items>
  {$itemXmlList}
</items>
XML;
        return $apiXml;
    }

    private function getGoodsItemXml($goodsItem, $goodsIdToGalleryArray)
    {

        // 截取描述，不能太长
        $goodsItem['goods_desc'] =
            mb_substr($goodsItem['goods_name'] . ' ' . strip_tags($goodsItem['goods_desc']), 0, 1000);

        // 截取商品标题，标题不能太长了
        $goodsItem['goods_name'] = mb_substr($goodsItem['goods_name'], 0, 60);

        $goodsViewUrl =
            RouteHelper::makeUrl('/Goods/View', array('goods_id' => $goodsItem['goods_id']), false, true);

        // 增加额外的链接参数
        $goodsViewUrl .= EtaoFeedPlugin::getOptionValue('etaofeed_goods_url_extra_param');

        // 处理图片列表
        $goodsGalleryArray = array();
        if (array_key_exists($goodsItem['goods_id'], $goodsIdToGalleryArray)) {
            $goodsGalleryArray = $goodsIdToGalleryArray[$goodsItem['goods_id']];
        }

        $goodsItemImageXml = '';
        if (!empty($goodsGalleryArray)) {
            $goodsItemImageXml =
                '<image is_default="true">' . RouteHelper::makeImageUrl(
                    $goodsGalleryArray[0]['img_original']
                ) . '</image>';
            array_shift($goodsGalleryArray); // 去掉第一个图片
            $goodsItemImageXml .= '<more_images>';

            // 图片集中的图片
            foreach ($goodsGalleryArray as $goodsGalleryItem) {
                $goodsItemImageXml .= '<img>' . RouteHelper::makeImageUrl($goodsGalleryItem['img_original']) . '</img>';
            }

            $goodsItemImageXml .= '</more_images>';
        }

        // 转换数据显示
        $goodsItem['shop_price']   = Money::toSmartyDisplay($goodsItem['shop_price']);
        $goodsItem['shipping_fee'] = Money::toSmartyDisplay($goodsItem['shipping_fee']);

        $goodsItemXml = <<<XML
<item>
	<seller_id><![CDATA[{$this->sellerId}]]></seller_id>
	<outer_id>{$goodsItem['goods_id']}</outer_id>
	<title><![CDATA[{$goodsItem['goods_name']}]]></title>
	<product_id>{$goodsItem['goods_sn']}</product_id>
	<type>fixed</type>
	<available>1</available>
	<price>{$goodsItem['shop_price']}</price>
	<desc><![CDATA[{$goodsItem['goods_desc']}]]></desc>
	{$goodsItemImageXml}
	<scids>{$goodsItem['cat_id']}</scids>
	<post_fee>{$goodsItem['shipping_fee']}</post_fee>
	<href><![CDATA[{$goodsViewUrl}]]></href>
</item>
XML;
        return $goodsItemXml;
    }

}
