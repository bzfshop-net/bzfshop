<?php

/**
 * @author QiangYu
 *
 *  创建字典数据用于系统的各种显示
 *
 */

use Core\Service\Meta\Dictionary as MetaDictionaryService;

class CreateDictionary implements \Clip\Command
{

    public function run(array $params)
    {

        printLog('Begin CreateDictionary', 'CreateDictionary');

        $metaDictionaryService = new MetaDictionaryService();

        // 用于 order_refer 中显示订单来源渠道 utm_source
        $metaDictionaryService->saveWord('SELF', '网站自身', '网站自身', '');
        $metaDictionaryService->saveWord('TUAN360CPS', '360团购导航', '360团购导航', '');
        $metaDictionaryService->saveWord('YIQIFACPS', '亿起发', '亿起发', '');
        $metaDictionaryService->saveWord('DUOMAICPS', '多麦', '多麦', '');

        // 用于 order_refer 中显示订单来源渠道 utm_medium
        $metaDictionaryService->saveWord('QQCAIBEI', 'QQ彩贝', 'QQ彩贝', '');
        $metaDictionaryService->saveWord('TUAN360TEQUAN', '360特权', '360特权', '');
        $metaDictionaryService->saveWord('TUAN360WAP', '360手机WAP', '360手机WAP', '');

        // 用于 order_refer 中显示订单登陆方式 login_type
        $metaDictionaryService->saveWord('normal', '普通登陆', '普通登陆', '');
        $metaDictionaryService->saveWord('qqcaibei', 'QQ彩贝登陆', 'QQ彩贝登陆', '');
        $metaDictionaryService->saveWord('qqlogin', 'QQ登陆', 'QQ登陆', '');
        $metaDictionaryService->saveWord('tuan360auth', '360联合登陆', '360联合登陆', '');

        printLog('Finish CreateDictionary', 'CreateDictionary');
    }

    public function help()
    {
        echo "Create Privilege Data\r\n";
    }
}
