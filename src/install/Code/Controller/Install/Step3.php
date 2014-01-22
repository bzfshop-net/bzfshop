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

        switch (CloudHelper::$currentEngineStr) {

            case CloudHelper::CLOUD_ENGINE_SAE:
                $smarty->assign('cloud_message', strtoupper(CloudHelper::$currentEngineStr) . ' 不需要配置数据库，请直接下一步');
                break;

            case CloudHelper::CLOUD_ENGINE_BAE3:
                $smarty->assign(
                    'cloud_message',
                    strtoupper(CloudHelper::$currentEngineStr)
                    . ' 这里需要的配置信息可以在 '
                    . strtoupper(CloudHelper::$currentEngineStr)
                    . ' 官方后台查找到'
                );
                break;

            default: // do nothing
        }

        $smarty->assign('currentEngineStr', CloudHelper::$currentEngineStr);

        // 页面显示
        $smarty->display('install_step3.tpl');
    }

    public function post($f3)
    {
        // 参数验证
        $validator = new Validator($f3->get('POST'));

        $dbHost = $validator->validate('dbHost');
        $dbPort = $validator->validate('dbPort');
        $dbName = $validator->validate('dbName');

        // 系统通用的配置
        $sysConfig = $validator->validate('sysConfig');

        if (!$this->validate($validator)) {
            goto out_fail;
        }

        try {

            // 对云平台要做特殊处理
            if (CloudHelper::CLOUD_ENGINE_SAE == CloudHelper::$currentEngineStr) {
                $dbPdo                    =
                    'mysql:host=' . SAE_MYSQL_HOST_M . ';port=' . SAE_MYSQL_PORT . ';dbname=' . SAE_MYSQL_DB;
                $sysConfig['db_username'] = SAE_MYSQL_USER;
                $sysConfig['db_password'] = SAE_MYSQL_PASS;
                goto import_data;
            }

            if (CloudHelper::CLOUD_ENGINE_BAE3 == CloudHelper::$currentEngineStr) {
                $sysConfig['db_username'] = $sysConfig['bae3_api_key'];
                $sysConfig['db_password'] = $sysConfig['bae3_secret_key'];
            }

            // 检查是否需要创建数据库
            $dbPdo    = 'mysql:host=' . $dbHost . ';port=' . $dbPort;
            $dbEngine = new \Core\Modal\DbEngine($dbPdo, $sysConfig['db_username'], $sysConfig['db_password']);

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
            // 清除 demo 配置
            $fileContent =
                preg_replace(
                    '/sysConfig\[is_demo\]=1/',
                    'sysConfig[is_demo]=0',
                    $fileContent
                );
            // 清除 Cache 的设置
            $sysConfig['cache'] = '';
            foreach ($sysConfig as $key => $value) {
                $fileContent =
                    preg_replace(
                        '/sysConfig\[' . $key . '\]="[^"]*"/',
                        'sysConfig[' . $key . ']="' . $value . '"',
                        $fileContent
                    );
            }
            file_put_contents($filePath, $fileContent);

            import_data: // 这里完成导入数据的工作

            $pdoObject = new \PDO($dbPdo, $sysConfig['db_username'], $sysConfig['db_password']);

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
        $smarty->assign('currentEngineStr', CloudHelper::$currentEngineStr);
        $smarty->display('install_step3.tpl');
    }

}
