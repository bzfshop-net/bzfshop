<?php
/**
 * 棒主妇开源 调试工具类，这个工具类开启了整个系统的调试功能
 *
 * @author QiangYu
 *
 */

final class BzfDebug
{

    private static $logCollector = null;

    public static function startDebugLogCollector()
    {

        if (static::$logCollector) {
            return static::$logCollector;
        }

        static::$logCollector = new \Core\Log\Collector();
        global $logger;
        $logger->addLogger(static::$logCollector); // 把 $logCollector 放到 全局的 logger 中
    }

    public static function enableDebug()
    {
        // 设置 PHP 调试环境
        ini_set('display_errors', 1);
        error_reporting(E_ALL | E_STRICT);

        // 开启 debug log
        BzfDebug::startDebugLogCollector();

        // 当前目录 autoload
        \Core\Plugin\SystemHelper::addAutoloadPath(realpath(dirname(__FILE__)));

        $handler = new \Whoops\Handler\PrettyPageHandler();

        // 增加额外的日志输出
        $handler->addDataTableCallback(
            'LOGS',
            function () {
                // 取得 logCollector
                $logCollector = BzfDebug::startDebugLogCollector();
                $logArray     = $logCollector->getLogArray();

                // 由于 debug 只支持 key-->value 显示，我们只能简单一点了
                $displayArray = array();
                $index        = 0;
                foreach ($logArray as $logItem) {
                    $displayArray[
                    '' . sprintf('%02d', $index) . '|' . str_pad($logItem['level'], 10, ' ', STR_PAD_LEFT) . '|'
                    . $logItem['source']] = $logItem['msg'];
                    $index++;
                }

                return $displayArray;
            }
        );

        $run = new \Whoops\Run();
        $run->pushHandler($handler);
        $run->register();
    }

    public static function smartyLogOutputFilter($source, Smarty_Internal_Template $smartyTemplate)
    {

        global $smarty;
        $currentController = $smarty->getTemplateVars('currentController');

        // 如果不是 htmlController，我们不加 weblog ，以防破坏文件结构
        // 比如你输出的是 xml, json，如果加了 weblog 你的文件就会出现解析错误
        if ($currentController && $currentController instanceof \Core\Controller\BaseController
            && !$currentController->isHtmlController()
        ) {
            return $source;
        }

        $header = <<<EOF
<!-- 调试信息 -->
<style>
#dfwloetw_log_table, #dfwloetw_log_table tr, #dfwloetw_log_table th, #dfwloetw_log_table tbody tr td {
	text-align:left;
	background-color:white;
	border: 2px solid gray;
	color:black;
	font-size:16px;
	padding:0px 5px;
}
#dfwloetw_log_table tbody tr td.breakword {
	word-wrap:break-word;
	word-break:break-all;
}
</style>
<table id="dfwloetw_log_table" width="100%" >
	<thead>
		<tr>
		    <th>Id</th>
			<th>Level</th>
			<th>Source</th>
			<th>Message</th>
		</tr>
	</thead>
	<tbody>
EOF;

        $footer = <<<EOF
	</tbody>
</table><!-- /调试信息 -->
EOF;

        $logCollector = BzfDebug::startDebugLogCollector();
        $logMsgArray  = $logCollector->getLogArray();
        $content      = '';
        $index        = 0;
        foreach ($logMsgArray as $info) {
            $content .=
                '<tr><td>' . ($index++) . '</td><td>' . htmlentities($info['level']) . '</td><td>' . htmlentities(
                    $info['source']
                ) . '</td><td class="breakword">'
                . htmlentities(
                    $info['msg']
                ) . '</td></tr>';
        }

        return $source . $header . $content . $footer;
    }

    public static function enableSmartyWebLog()
    {
        global $smarty;

        //注册 smarty output filter
        $smarty->registerFilter('output', array(__CLASS__, 'smartyLogOutputFilter'));
    }

}
