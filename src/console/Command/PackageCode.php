<?php

/**
 * @author QiangYu
 *
 * 每次发布，我们除了会把代码同步到 github 上之外，我们还会提供一份 .tar.bz2 的下载包，每次手动打包太麻烦，
 * 于是写这个命令自动打包，省去不少麻烦
 */

class PackageCode implements \Clip\Command
{

    /**
     * 执行系统命令
     *
     * @param $cmd
     */
    private function system($cmd, $dieOnError = true, $callback = null)
    {
        echo "[Begin] Command [" . $cmd . "]\n";

        $retValue = 0;
        system($cmd, $retValue);

        if ($dieOnError && 0 != $retValue) {
            die('command fail, die');
        }

        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, array($retValue));
        }

        echo "[End] Command [" . $cmd . "]\n";
    }

    private function clearDir($dir)
    {
        $this->system('find ' . $dir . ' -maxdepth 1 ! -path ' . $dir . ' -type d -exec rm -rf {} \;');
    }

    public function run(array $params)
    {
        global $f3;

        $commandDirPath = realpath(dirname(__FILE__));

        // 获得当前目录
        $bzfshopDirPath = realpath($commandDirPath . '/../../../');
        $bzfshopParentDirPath = realpath($bzfshopDirPath . '/../');
        $bzfshopDirName = substr($bzfshopDirPath, strlen($bzfshopParentDirPath) + 1);

        // 增加目录的访问权限
        $this->system('chmod -R a+w ' . $bzfshopDirPath . '/src/data');
        $this->system('chmod -R a+w ' . $bzfshopDirPath . '/src/asset');
        $this->system('chmod -R a+w ' . $bzfshopDirPath . '/src/manage/asset');
        $this->system('chmod -R a+w ' . $bzfshopDirPath . '/src/mobile/asset');
        $this->system('chmod -R a+w ' . $bzfshopDirPath . '/src/supplier/asset');
        $this->system('chmod -R a+w ' . $bzfshopDirPath . '/src/protected/Runtime');
        $this->system('chmod -R a+w ' . $bzfshopDirPath . '/src/protected/Config');

        // 清除打包没用的内容
        $this->system('rm -rf ' . $bzfshopDirPath . '/.git');

        // 清除 data
        $this->clearDir($bzfshopDirPath . '/src/data');

        // 清除 asset
        $this->clearDir($bzfshopDirPath . '/src/asset');
        $this->clearDir($bzfshopDirPath . '/src/manage/asset');
        $this->clearDir($bzfshopDirPath . '/src/mobile/asset');
        $this->clearDir($bzfshopDirPath . '/src/supplier/asset');

        // 清除 Runtime
        $this->clearDir($bzfshopDirPath . '/src/protected/Runtime/Log');
        $this->clearDir($bzfshopDirPath . '/src/protected/Runtime/Smarty');
        $this->clearDir($bzfshopDirPath . '/src/protected/Runtime/Temp');

        // 清理 install
        $this->system('rm -rf ' . $bzfshopDirPath . '/src/install/Asset/bootstrap-custom/.git');
        $this->system('rm -rf ' . $bzfshopDirPath . '/src/install/Asset/bootstrap-custom/kindeditor');
        $this->system('rm -rf ' . $bzfshopDirPath . '/src/install/Asset/bootstrap-custom/test*');
        $this->system(
            'find ' . $bzfshopDirPath . '/src/install/Asset/bootstrap-custom/plugin'
            . ' -maxdepth 1 ! -path ' . $bzfshopDirPath . '/src/install/Asset/bootstrap-custom/plugin'
            . ' ! -name pnotify -exec rm -rf {} \;'
        );

        // 清理 manage
        $this->system('rm -rf ' . $bzfshopDirPath . '/src/protected/Theme/Manage/Asset/bootstrap-custom/.git');
        $this->system('rm -rf ' . $bzfshopDirPath . '/src/protected/Theme/Manage/Asset/bootstrap-custom/test*');

        // 清理 shop
        $this->system('rm -rf ' . $bzfshopDirPath . '/src/protected/Theme/Shop/shop/Asset/bootstrap-custom/.git');
        $this->system('rm -rf ' . $bzfshopDirPath . '/src/protected/Theme/Shop/shop/Asset/bootstrap-custom/kindeditor');
        $this->system('rm -rf ' . $bzfshopDirPath . '/src/protected/Theme/Shop/shop/Asset/bootstrap-custom/test*');

        // 清理 supplier
        $this->system(
            'rm -rf ' . $bzfshopDirPath . '/src/protected/Theme/Supplier/supplier/Asset/bootstrap-custom/.git'
        );
        $this->system(
            'rm -rf ' . $bzfshopDirPath . '/src/protected/Theme/Supplier/supplier/Asset/bootstrap-custom/kindeditor'
        );
        $this->system(
            'rm -rf ' . $bzfshopDirPath . '/src/protected/Theme/Supplier/supplier/Asset/bootstrap-custom/test*'
        );

        // 切换目录
        chdir($bzfshopParentDirPath);

        // 代码打包
        $packageName = 'bzfshop_' . $f3->get('sysConfig[version]') . '.tar.bz2';
        $this->system('tar -cjf ' . $packageName . ' ' . $bzfshopDirName);

        // 计算 MD5
        $this->system('md5sum ' . $packageName . ' > ' . $packageName . '.md5sum');

    }

    public function help()
    {
        echo "Package bzfshop code to .tar.bz2";
    }
}

