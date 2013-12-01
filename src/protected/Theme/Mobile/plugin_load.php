<?php

// 加载 MobileThemePlugin  插件加载文件，用于初始化插件同时返回一个插件的 instance 对象

use Theme\Mobile\MobileThemePlugin;

MobileThemePlugin::instance()->setPluginDirAbsolutePath(dirname(__FILE__));
return MobileThemePlugin::instance();
