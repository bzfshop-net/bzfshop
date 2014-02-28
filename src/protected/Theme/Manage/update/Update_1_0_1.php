<?php

/**
 * 升级程序，做基本的升级
 */

// 定义自己的 namespace ，防止和别的插件冲突
namespace Theme\Manage {

    use Core\Helper\Utility\Sql as SqlHelper;
    use Core\Modal\SqlMapper as DataMapper;
    use Core\Plugin\AbstractUpdate;
    use Core\Service\Meta\Privilege as MetaPrivilegeService;

    class Update_1_0_1 extends AbstractUpdate
    {

        /**
         * 1.0.0 是最初始的版本
         */
        protected $sourceVersionAllowed = array('1.0.0');
        /**
         * 把版本升级到 1.0.1
         */
        protected $targetVersion = '1.0.1';

        public function doUpdate($currentVersion)
        {

            // 更新数据库表
            $sqlFileContent = <<<SQL
-- 创建 cron 任务列表
CREATE TABLE IF NOT EXISTS `bzf_cron_task` (
  `task_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(32) DEFAULT NULL COMMENT '哪个用户添加的',
  `task_name` varchar(16) DEFAULT NULL COMMENT '任务名称',
  `task_desc` varchar(128) DEFAULT NULL COMMENT '任务描述',
  `task_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '任务设定时间',
  `task_run_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '任务实际运行时间',
  `task_class` varchar(128) NOT NULL COMMENT '任务的PHP Class',
  `task_param` text DEFAULT NULL,
  `search_param` varchar(64) DEFAULT NULL COMMENT '用于任务搜索',
  `return_code` int DEFAULT 0 COMMENT '任务设定时间',
  `return_message` varchar(128) DEFAULT NULL COMMENT '任务返回消息',

  PRIMARY KEY (`task_id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `bzf_cron_task` ADD INDEX ( `task_name` ) ;
ALTER TABLE `bzf_cron_task` ADD INDEX ( `task_time` ) ;
ALTER TABLE `bzf_cron_task` ADD INDEX ( `task_run_time` ) ;
ALTER TABLE `bzf_cron_task` ADD INDEX ( `search_param` ) ;
ALTER TABLE `bzf_cron_task` ADD INDEX ( `return_code` ) ;

-- 记录管理员的行为
CREATE TABLE IF NOT EXISTS `bzf_admin_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL ,
  `user_name` varchar(60) DEFAULT NULL ,
  `operate` varchar(16) DEFAULT NULL COMMENT '操作名称',
  `operate_desc` varchar(128) DEFAULT NULL COMMENT '操作描述',
  `operate_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '操作时间',
  `operate_data` text DEFAULT NULL COMMENT '操作的数据记录',
  PRIMARY KEY (`log_id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
ALTER TABLE `bzf_admin_log` ADD INDEX ( `user_id` ) ;
ALTER TABLE `bzf_admin_log` ADD INDEX ( `operate` ) ;
ALTER TABLE `bzf_admin_log` ADD INDEX ( `operate_time` ) ;

-- 修改价格表，增加价格说明
ALTER TABLE `bzf_goods` ADD `shop_price_notice` VARCHAR( 32 ) NULL DEFAULT NULL COMMENT '价格说明' AFTER `shop_price`;
ALTER TABLE `bzf_goods` ADD `type_id` SMALLINT( 5 ) NOT NULL DEFAULT '0' COMMENT '商品类型' AFTER `cat_id`;

-- 修改 brand 表
ALTER TABLE `bzf_brand` CHANGE `sort_order` `sort_order` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `bzf_brand` CHANGE `brand_logo` `brand_logo` VARCHAR( 128 ) NULL DEFAULT NULL ;
ALTER TABLE `bzf_brand` CHANGE `brand_desc` `brand_desc` VARCHAR( 255 ) NULL DEFAULT NULL ;
ALTER TABLE `bzf_brand` ADD `is_custom` int NULL DEFAULT 0 COMMENT '是否自定义页面';
ALTER TABLE `bzf_brand` ADD `custom_page` TEXT NULL DEFAULT NULL COMMENT '品牌自定义页面';

-- 增加 goods_attr 表记录商品的属性值
CREATE TABLE IF NOT EXISTS `bzf_goods_attr` (
  `goods_attr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0',
  `attr_item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `attr_item_value` varchar(128) NULL,
  PRIMARY KEY (`goods_attr_id`),
  KEY `goods_id` (`goods_id`),
  KEY `attr_item_id` (`attr_item_id`),
  KEY `attr_item_value` (`attr_item_value`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

SQL;

            $dbEngine = DataMapper::getDbEngine();

            // 解析 sql 文件，导入数据
            $sqlFileContent = SqlHelper::removeComment($sqlFileContent);
            $sqlArray       = SqlHelper::splitToSqlArray($sqlFileContent, ';');
            unset($sqlFileContent);
            foreach ($sqlArray as $sqlQuery) {
                $queryObject = $dbEngine->prepare($sqlQuery);
                $queryObject->execute();
                unset($sqlQuery);
                unset($queryObject);
            }
            unset($sqlArray);

            // 权限管理
            $metaPrivilegeService = new MetaPrivilegeService();

            // 删除不用的权限
            $metaPrivilegeService->removePrivilegeItem('manage_goods_attrgroup_listattrgroup');
            $metaPrivilegeService->removePrivilegeItem('manage_goods_attrgroup_create');
            $metaPrivilegeService->removePrivilegeItem('manage_goods_attrgroup_edit');

            // 添加执行权限
            $privilegeGroup = $metaPrivilegeService->loadPrivilegeGroup('manage_misc');
            $metaPrivilegeService->savePrivilegeItem(
                $privilegeGroup['meta_id'],
                'manage_misc_cron',
                '定时任务',
                '管理系统的定时任务'
            );

            $privilegeGroup = $metaPrivilegeService->loadPrivilegeGroup('manage_account');
            $metaPrivilegeService->savePrivilegeItem(
                $privilegeGroup['meta_id'],
                'manage_account_admin_listlog',
                '管理员日志',
                '查看管理员的操作日志'
            );

            $privilegeGroup = $metaPrivilegeService->loadPrivilegeGroup('manage_goods');
            $metaPrivilegeService->savePrivilegeItem(
                $privilegeGroup['meta_id'],
                'manage_goods_brand_listbrand',
                '商品品牌',
                '管理商品品牌'
            );
            $metaPrivilegeService->savePrivilegeItem(
                $privilegeGroup['meta_id'],
                'manage_goods_type_listtype',
                '商品类型',
                '管理商品类型'
            );

            // 把版本设置为 1.0.1
            ManageThemePlugin::saveOptionValue('version', $this->targetVersion);

            return true;
        }
    }
}


// 全局命名空间代码，我们在这里生成一个插件的实例返回给加载程序
namespace {
    // 返回 update instance
    return new Theme\Manage\Update_1_0_1();
}

