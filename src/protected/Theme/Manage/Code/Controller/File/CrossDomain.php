<?php

/**
 * @author QiangYu
 *
 * 用于解决 SwfUpload 跨域上传文件的问题
 *
 * */

namespace Controller\File;

class CrossDomain extends \Controller\BaseController
{

    public function get($f3)
    {

        $assetUrlPrefix = $f3->get('sysConfig[asset_path_url_prefix]');
        $urlInfo        = parse_url($assetUrlPrefix);
        $assetHost      = @$urlInfo['host'];

        $crossDomainXml = <<<XML
<?xml version="1.0"?>
<!DOCTYPE cross-domain-policy SYSTEM "http://www.macromedia.com/xml/dtds/cross-domain-policy.dtd">
<cross-domain-policy>
    <site-control permitted-cross-domain-policies="all"/>
    <allow-access-from domain="{$assetHost}" to-ports="*" secure="true"/>
    <allow-http-request-headers-from domain="{$assetHost}" headers="*" secure="true"/>
</cross-domain-policy>
XML;
        // 客户端缓存 1 个小时
        $f3->expire(3600);
        header('Content-Type:text/xml;charset=utf-8');
        echo $crossDomainXml;
    }

    public function post($f3)
    {
        $this->get($f3);
    }

}
