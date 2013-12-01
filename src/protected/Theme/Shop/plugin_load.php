<?php

// 加载 ShopThemePlugin  插件加载文件，用于初始化插件同时返回一个插件的 instance 对象

use Theme\Shop\ShopThemePlugin;

ShopThemePlugin::instance()->setPluginDirAbsolutePath(dirname(__FILE__));
return ShopThemePlugin::instance();
