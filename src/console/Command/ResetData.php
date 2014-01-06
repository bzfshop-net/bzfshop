<?php
use Core\Helper\Utility\Sql as SqlHelper;
use Core\Modal\SqlMapper as DataMapper;

/**
 * @author QiangYu
 *
 * 重新加载所有数据，包括重新引入数据库，删除所有 /data 下的数据 和 Runtime 里面的数据
 *
 * 这个命令用于 demo 演示的时候每个小时重设一次所有数据，去除用户做的任何修改
 *
 */

class ResetData implements \Clip\Command
{

    /**
     * 删除一个目录下面所有的目录以及里面的内容
     *
     * @param string $targetDir 目录
     */
    private function removeAllDirInsideDir($targetDir)
    {
        $directory = dir($targetDir);
        while (false !== ($readdir = $directory->read())) {
            if ($readdir == '.' || $readdir == '..') {
                continue;
            }
            $PathDir = $targetDir . DIRECTORY_SEPARATOR . $readdir;
            if (is_dir($PathDir)) {
                \FileUnit::instance()->cleanDir($PathDir);
            }
        }
    }

    public function run(array $params)
    {

        global $f3;

        // 1. reset 整个数据库
        printLog('Begin Reset Database', 'ResetData');

        $dbEngine = DataMapper::getDbEngine();

        // 解析 sql 文件，导入数据
        $sqlFileContent = file_get_contents(CONSOLE_PATH . '/../install/Asset/data/bzfshop.sql');
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

        printLog('End Reset Database', 'ResetData');

        // 2. 删除 /data 目录下的所有目录

        printLog('remove everything under /data', 'ResetData');

        $this->removeAllDirInsideDir($f3->get('sysConfig[data_path_root]'));

        // 3. 删除 RunTime 目录下所有目录

        printLog('remove everything in Runtime', 'ResetData');

        $this->removeAllDirInsideDir($f3->get('sysConfig[runtime_path]') . DIRECTORY_SEPARATOR . 'Log/');
        $this->removeAllDirInsideDir($f3->get('sysConfig[runtime_path]') . DIRECTORY_SEPARATOR . 'Smarty/');
        $this->removeAllDirInsideDir($f3->get('sysConfig[runtime_path]') . DIRECTORY_SEPARATOR . 'Temp/');

        // 4. 清除 F3 的缓存
        $f3->clear('CACHE');

        printLog('ResetData Done', 'ResetData');
    }

    public function help()
    {
        echo "Reset database and remove everything under /data ";
    }
}
