<?php

/**
 * @author QiangYu
 *
 * 404 错误
 *
 * */

namespace Controller\Install;

use Core\Cloud\CloudHelper;
use Core\Helper\Utility\Route as RouteHelper;
use Core\Helper\Utility\Sql as SqlHelper;
use Core\Helper\Utility\Validator;

class Step3 extends \Controller\BaseController
{

    public function get($f3)
    {
        global $smarty;

        // 设置缺省参数
        $smarty->assign('dbHost', 'localhost');
        $smarty->assign('dbPort', '3306');
        $smarty->assign('dbName', 'bzfshop');
        $smarty->assign('dbUserName', 'root');

        if (CloudHelper::CLOUD_ENGINE_SAE == CloudHelper::$currentEngineStr) {
            $smarty->assign('cloud_message', strtoupper(CloudHelper::$currentEngineStr) . ' 不需要配置数据库，这里随便填什么都可以');
        }

        // 页面显示
        $smarty->display('install_step3.tpl');
    }

    public function post($f3)
    {
        // 参数验证
        $validator = new Validator($f3->get('POST'));

        $dbHost      = $validator->required('数据库地址不能为空')->validate('dbHost');
        $dbPort      = $validator->required('数据库端口不能为空')->digits('数据库端口非法')->validate('dbPort');
        $dbName      = $validator->required('数据库名不能为空')->validate('dbName');
        $dbUsersName = $validator->required('用户名不能为空')->validate('dbUserName');
        $dbPassword  = $validator->required('密码不能为空')->validate('dbPassword');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        try {

            // 对云平台要做特殊处理
            if (CloudHelper::CLOUD_ENGINE_SAE == CloudHelper::$currentEngineStr) {
                $dbPdo       = 'mysql:host=' . SAE_MYSQL_HOST_M . ';port=' . SAE_MYSQL_PORT . ';dbname=' . SAE_MYSQL_DB;
                $dbUsersName = SAE_MYSQL_USER;
                $dbPassword  = SAE_MYSQL_PASS;
                goto import_data;
            }

            // 检查是否需要创建数据库
            $dbPdo    = 'mysql:host=' . $dbHost . ';port=' . $dbPort;
            $dbEngine = new \Core\Modal\DbEngine($dbPdo, $dbUsersName, $dbPassword);

            // 检查数据库是否存在，从而确定我们是否应该新建一个数据库
            $shouldCreateDatabase = true;
            $databaseArray        = $dbEngine->exec('SHOW DATABASES');
            foreach ($databaseArray as $databaseItem) {
                if ($databaseItem['Database'] == $dbName) {
                    $shouldCreateDatabase = false;
                    break;
                }
            }

            if ($shouldCreateDatabase) {
                // 在这里创建一个新的数据库
                $dbEngine->exec('CREATE DATABASE ' . $dbName);
            }

            // 重新初始化数据库连接
            unset($dbPdo);
            unset($dbEngine);
            $dbPdo = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName;


            // 更新配置文件 env.cfg
            $filePath    = INSTALL_PATH . '/../protected/Config/env.cfg';
            $fileContent = file_get_contents($filePath);
            $fileContent = preg_replace('/sysConfig\[env\]="[^"]*"/', 'sysConfig[env]="prod"', $fileContent);
            file_put_contents($filePath, $fileContent);

            // 更新配置文件 common-prod.cfg
            $filePath    = INSTALL_PATH . '/../protected/Config/common-prod.cfg';
            $fileContent = file_get_contents($filePath);
            $fileContent =
                preg_replace('/sysConfig\[db_pdo\]="[^"]*"/', 'sysConfig[db_pdo]="' . $dbPdo . '"', $fileContent);
            $fileContent =
                preg_replace(
                    '/sysConfig\[db_username\]="[^"]*"/',
                    'sysConfig[db_username]="' . $dbUsersName . '"',
                    $fileContent
                );
            $fileContent =
                preg_replace(
                    '/sysConfig\[db_password\]="[^"]*"/',
                    'sysConfig[db_password]="' . $dbPassword . '"',
                    $fileContent
                );
            $fileContent =
                preg_replace(
                    '/CACHE="[^"]*"/',
                    'CACHE=""',
                    $fileContent
                );
            file_put_contents($filePath, $fileContent);

            // 更新配置文件 manage-prod.cfg
            $filePath    = INSTALL_PATH . '/../protected/Config/manage-prod.cfg';
            $fileContent = file_get_contents($filePath);
            $fileContent =
                preg_replace(
                    '/sysConfig\[manage_change_password\]=0/',
                    'sysConfig[manage_change_password]=1',
                    $fileContent
                );
            file_put_contents($filePath, $fileContent);

            import_data: // 这里完成导入数据的工作

            $pdoObject = new \PDO($dbPdo, $dbUsersName, $dbPassword);

            // 解析 sql 文件，导入数据
            $sqlFileContent = file_get_contents(INSTALL_PATH . '/Asset/data/bzfshop.sql');
            $sqlFileContent = SqlHelper::removeComment($sqlFileContent);
            $sqlArray       = SqlHelper::splitToSqlArray($sqlFileContent, ';');
            foreach ($sqlArray as $sqlQuery) {
                $pdoObject->exec($sqlQuery);
            }
            unset($pdoObject);

        } catch (\PDOException $e) {
            $this->addFlashMessage($e->getMessage());
            goto out_fail;
        }

        $this->addFlashMessage('数据导入成功');

        // 成功，进入到下一步
        RouteHelper::reRoute($this, '/Install/Step4');
        return;

        out_fail: // 失败从这里退出
        global $smarty;
        $smarty->display('install_step3.tpl');
    }

}
