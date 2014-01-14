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
  `task_name` varchar(16) DEFAULT NULL COMMENT '任务名称',
  `task_desc` varchar(128) DEFAULT NULL COMMENT '任务描述',
  `task_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '任务设定时间',
  `task_run_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '任务实际运行时间',
  `task_class` varchar(128) NOT NULL COMMENT '任务的PHP Class',
  `task_param` text DEFAULT NULL,
  `return_code` int DEFAULT 0 COMMENT '任务设定时间',
  `return_message` varchar(128) DEFAULT NULL COMMENT '任务返回消息',

  PRIMARY KEY (`task_id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `bzf_cron_task` ADD INDEX ( `task_name` ) ;
ALTER TABLE `bzf_cron_task` ADD INDEX ( `task_time` ) ;
ALTER TABLE `bzf_cron_task` ADD INDEX ( `return_code` ) ;
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

            // 添加执行权限
            $metaPrivilegeService = new MetaPrivilegeService();
            $privilegeGroup       = $metaPrivilegeService->loadPrivilegeGroup('manage_misc');
            $metaPrivilegeService->savePrivilegeItem(
                $privilegeGroup['meta_id'],
                'manage_misc_cron',
                '定时任务',
                '管理系统的定时任务'
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

