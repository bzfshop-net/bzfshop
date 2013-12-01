<?php

// 加载 SupplierThemePlugin   插件加载文件，用于初始化插件同时返回一个插件的 instance 对象

use Theme\Supplier\SupplierThemePlugin;

SupplierThemePlugin::instance()->setPluginDirAbsolutePath(dirname(__FILE__));
return SupplierThemePlugin::instance();
