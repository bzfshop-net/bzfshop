<?php

/**
 * @author QiangYu
 *
 * 提供完整的 SiteMap 信息，符合搜索引擎的 SiteMap 协议
 *
 * */

namespace Controller\Shop;


use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Time;
use Core\Plugin\ThemeHelper;
use Core\Search\SearchHelper;
use Theme\Manage\ManageThemePlugin;

class SiteMap extends \Controller\BaseController
{
    /**
     * 当前 Controller 不是输出 html，所以不要做针对 html 的任何优化
     */
    protected $isHtmlController = false;

    // 每次 API 中输出多少数据
    private $pageSize = 1000;

    private function getGoodsItemXml($goodsItem, $currentGmTime)
    {
        $goodsViewUrl =
            RouteHelper::makeUrl('/Goods/View', array('goods_id' => $goodsItem['goods_id']), false, true);

        // 如果没有 update_time 就用 add_time 代替
        $goodsItem['update_time'] =
            ($goodsItem['update_time'] > 0) ? $goodsItem['update_time'] : $goodsItem['add_time'];
        $goodsLastModTime         = date('Y-m-d\TH:i:sP', Time::gmTimeToLocalTime($goodsItem['update_time']));
        $priority                 = 0.4;

        $timeDiff = $currentGmTime - $goodsItem['update_time'];
        if ($timeDiff > 259200) {
            // 3天前的修改，优先级降低
            $priority = 0.4;
        } else {
            if ($timeDiff > 86400) {
                // 1 天到 3 天的修改
                $priority = 0.8;
            } else {
                // 最新修改，优先级最高
                $priority = 1.0;
            }
        }

        $xmlitem = <<<XMLITEM
	    <url>
	    <loc><![CDATA[{$goodsViewUrl}]]></loc>
	    <lastmod>{$goodsLastModTime}</lastmod>
	    <changefreq>always</changefreq>
	    <priority>{$priority}</priority>
        </url>
XMLITEM;
        return $xmlitem;

    }

    /**
     * 输出商品的列表
     *
     * @param $f3
     * @param $pageNo
     */
    public function outputGoodsViewListXml($f3, $pageNo)
    {
        global $smarty;

        // 缓存 1 小时
        enableSmartyCache(true, 3600, \Smarty::CACHING_LIFETIME_CURRENT);

        $smartyCacheId = 'Api|' . md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__ . '\\' . $pageNo);

        // 判断是否有缓存
        if ($smarty->isCached('empty.tpl', $smartyCacheId)) {
            goto out_display;
        }

        $currentThemeInstance = ThemeHelper::getCurrentSystemThemeInstance();

        // 查询商品
        $goodsArray = SearchHelper::search(
            SearchHelper::Module_Goods,
            'g.goods_id, g.add_time, g.update_time',
            array(
                 array(
                     QueryBuilder::buildGoodsFilterForSystem(
                         $currentThemeInstance->getGoodsFilterSystemArray(),
                         'g'
                     )
                 )
            ),
            array(array('g.goods_id', 'desc')),
            $pageNo * $this->pageSize,
            $this->pageSize
        );

        $xmlItems = '';
        if (empty($goodsArray)) {
            goto out;
        }

        $currentGmTime = Time::gmTime();

        $goodsArrayCount = count($goodsArray);
        for ($index = 0; $index < $goodsArrayCount; $index++) {
            $xmlItems .= $this->getGoodsItemXml($goodsArray[$index], $currentGmTime);
        }

        out:

        $apiXml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" >
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

    /**
     * 输出 GoodsSearch 页面的链接
     *
     * @param $f3
     * @param $pageNo
     */
    public function outputGoodsSearchListXml($f3, $pageNo)
    {
        global $smarty;

        // 缓存 1 小时
        enableSmartyCache(true, 3600, \Smarty::CACHING_LIFETIME_CURRENT);

        $smartyCacheId = 'Api|' . md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__ . '\\' . $pageNo);

        // 判断是否有缓存
        if ($smarty->isCached('empty.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // 商品搜索链接
        $goodsSearchUrl =
            RouteHelper::makeUrl(
                '/Goods/Search',
                array('orderBy' => 'add_time', 'orderDir' => 'desc'),
                false,
                true,
                false
            );

        // 商品分类列表
        $ajaxCategoryUrl =
            RouteHelper::makeUrl('/Ajax/Category', null, false, true, false);

        $lastModifyTime = date('Y-m-d\TH:i:sP', time());

        $apiXml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" >
	<url>
	    <loc><![CDATA[{$goodsSearchUrl}]]></loc>
	    <lastmod>{$lastModifyTime}</lastmod>
	    <changefreq>always</changefreq>
	    <priority>1.0</priority>
    </url>
	<url>
	    <loc><![CDATA[{$ajaxCategoryUrl}]]></loc>
	    <lastmod>{$lastModifyTime}</lastmod>
	    <changefreq>always</changefreq>
	    <priority>1.0</priority>
    </url>
</urlset>
XML;

        $smarty->assign('outputContent', $apiXml);

        out_display:
        header('Content-Type:text/xml;charset=utf-8');
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 //查询信息
        $smarty->display('empty.tpl', $smartyCacheId);
    }


    /**
     * 输出文章的列表
     *
     * @param $f3
     * @param $pageNo
     */
    public function outputArticleViewListXml($f3, $pageNo)
    {
        global $smarty;

        // 缓存 1 小时
        enableSmartyCache(true, 3600, \Smarty::CACHING_LIFETIME_CURRENT);

        $smartyCacheId = 'Api|' . md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__ . '\\' . $pageNo);

        // 判断是否有缓存
        if ($smarty->isCached('empty.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // 查询商品
        $articleArray = SearchHelper::search(
            SearchHelper::Module_Article,
            'a.article_id, a.update_time',
            QueryBuilder::buildSearchParamArray(array('a.is_open' => 1)),
            array(array('a.article_id', 'desc')),
            $pageNo * $this->pageSize,
            $this->pageSize
        );

        $xmlItems = '';
        if (empty($articleArray)) {
            goto out;
        }

        foreach ($articleArray as $articleItem) {
            $articleViewUrl     =
                RouteHelper::makeUrl('/Article/View', array('article_id' => $articleItem['article_id']), false, true);
            $articleLastModTime = date('Y-m-d\TH:i:sP', Time::gmTimeToLocalTime($articleItem['update_time']));

            $xmlItems .= <<<XMLITEM
	    <url>
	    <loc><![CDATA[{$articleViewUrl}]]></loc>
	    <lastmod>{$articleLastModTime}</lastmod>
	    <changefreq>always</changefreq>
        </url>
XMLITEM;
        }

        out:

        $apiXml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" >
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


    /**
     * 输出 sitemapIndex 文件
     */
    public function outputSiteMapXml($f3, $fileName)
    {
        global $smarty;

        //缓存 60 分钟
        enableSmartyCache(true, 3600, \Smarty::CACHING_LIFETIME_CURRENT);

        $smartyCacheId = 'Api|' . md5(__NAMESPACE__ . '\\' . __CLASS__ . '\\' . __METHOD__);

        // 判断是否有缓存
        if ($smarty->isCached('empty.tpl', $smartyCacheId)) {
            goto out_display;
        }

        // sitemap 列表
        $siteMapFileList = '';
        // 当前时间
        $currentTime = time();

        /***************** 生成 /Goods/View 列表 *******************/
        // 查询商品数量，决定分页有多少页
        $currentThemeInstance = ThemeHelper::getCurrentSystemThemeInstance();
        $totalGoodsCount      = SearchHelper::count(
            SearchHelper::Module_Goods,
            array(
                 array(
                     QueryBuilder::buildGoodsFilterForSystem(
                         $currentThemeInstance->getGoodsFilterSystemArray(),
                         'g'
                     )
                 )
            )
        );
        $pageCount            = ceil($totalGoodsCount / $this->pageSize);

        // 取得当前的目录路径
        $currentUrl = RouteHelper::getFullURL();
        $currentUrl = substr($currentUrl, 0, strrpos($currentUrl, $fileName));

        // 生成 goods 页面索引
        for ($index = 0; $index < $pageCount; $index++) {
            $siteMapFileList .=
                '<sitemap><loc>' . $currentUrl . 'GoodsView_' . $currentTime . '_' . $index . '.xml</loc></sitemap>';
        }

        /***************** 生成 /Goods/Search 列表 *******************/
        // 生成 search 页面索引
        $siteMapFileList .=
            '<sitemap><loc>' . $currentUrl . 'GoodsSearch_' . $currentTime . '_0.xml</loc></sitemap>';

        /***************** 生成 /Article/View 列表 *******************/
        // 查询商品数量，决定分页有多少页
        $totalArticleCount = SearchHelper::count(
            SearchHelper::Module_Article,
            QueryBuilder::buildSearchParamArray(array('a.is_open' => 1))
        );
        $pageCount         = ceil($totalArticleCount / $this->pageSize);

        // 生成 Article 页面索引
        for ($index = 0; $index < $pageCount; $index++) {
            $siteMapFileList .=
                '<sitemap><loc>' . $currentUrl . 'ArticleView_' . $currentTime . '_' . $index . '.xml</loc></sitemap>';
        }

        $apiXml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{$siteMapFileList}
</sitemapindex>
XML;

        $smarty->assign('outputContent', $apiXml);

        out_display:
        header('Content-Type:text/xml;charset=utf-8');
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 //查询信息
        $smarty->display('empty.tpl', $smartyCacheId);
    }

    public function get($f3)
    {
        // 解析传入的 fileName, 文件名的格式应该是 bangzhufu_2012100112_1.xml，最后的数字为页号
        $fileName = $f3->get('PARAMS.fileName');
        //去掉文件扩展名
        $fileName = substr($fileName, 0, strrpos($fileName, '.'));

        $fileNamePart     = explode('_', $fileName);
        $fileNamePartSize = count($fileNamePart);

        // 这里定义文件名对应的输出函数
        $fileNameToMethodArray = array(
            'GoodsSearch' => 'outputGoodsSearchListXml',
            'GoodsView'   => 'outputGoodsViewListXml',
            'ArticleView' => 'outputArticleViewListXml',
        );

        // 如果不符合文件结构，则输出 sitemap.xml
        if ($fileNamePartSize <= 0
            || !is_numeric($fileNamePart[$fileNamePartSize - 1])
            || !in_array(
                $fileNamePart[0],
                array_keys($fileNameToMethodArray)
            )
        ) {
            $this->outputSiteMapXml($f3, $fileName);
            return;
        }

        // 输出每页的API数据
        $pageNo = abs(intval($fileNamePart[$fileNamePartSize - 1]));
        $pageNo = ($pageNo > 0) ? $pageNo : 0;

        $methodName = $fileNameToMethodArray[$fileNamePart[0]];

        // 调用对应的输出函数
        call_user_func_array(array($this, $methodName), array($f3, $pageNo));
    }

    public function post($f3)
    {
        $this->get($f3);
    }

    // 重定向到 get
    public function redirect($f3)
    {
        RouteHelper::reRoute($this, '/Shop/SiteMap/sitemap.xml');
    }
}
