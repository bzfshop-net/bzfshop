
## 棒主妇开源商城

棒主妇开源(http://www.bzfshop.net) 是 棒主妇商城(http://www.bangzhufu.com) 的开源项目。我们希望提供一个设计严谨、性能优异、完全免费开源的
B2C商城给大家使用，方便每一个人可以很容易的搭建属于自己的B2C商城。

bzfshop 设计为一个跨平台的程序，可以支持多种平台的安装，目前包括：

* 服务器集群安装 （F5/LVS 负载均衡设备 + 一堆 WebServer + Memcache 缓存服务器 + MySql Cluster）
* 普通服务器安装（虚拟主机、VPS、阿里云、独立服务器、...）
* 新浪 SAE 平台（把 src 目录下内容上传到 SAE 即可）
* 百度 BAE3 平台（把 src 目录下内容上传到 BAE3 即可）

***

### 商城链接说明

棒主妇开源：http://www.bzfshop.net

棒主妇文档：http://doc.bzfshop.net

商城演示：http://www.bzfshop.net/article/253.html

代码下载：http://www.bzfshop.net/article/239.html

### 商城安装方法

 1. 下载代码，解压，把 src 目录下的文件上传到你的网站根目录

 2. 系统配置

    Apache 2.2.x 用户（2.4.x 由于 urlRewrite 的规则变化了，需要你自行修改 .htaccess 文件）
    程序已经配置好了 .htaccess 文件，只要你的 Apache 支持 .htaccess，那就无需任何额外的配置了

    Nginx 用户
    我们提供了一个 nginx.conf 配置文件作为你的参考，你需要先配置好你的服务器，然后才能启动安装程序
	
	IIS 用户
	你需要给 IIS 服务器安装 urlRewrite 插件，让 IIS 能够使用 apache 的 .htaccess 文件，然后才能启动安装

 3. 程序安装

	程序要求 PHP 版本 >= 5.3.4 ，低于这个版本无法安装
 
    我们假设你已经完成了上面的系统配置问题，现在访问 /install 目录，你会看到安装引导程序，根据引导程序的提示完成安装即可

 4. 删除 install 目录

    完成安装之后，请一定记得要删除 install 目录，不然别人可能会访问你的 install 目录，再次安装程序导致你丢失所有数据

### 商城代码 clone 使用

	商城代码依赖 bootstrap-custom 子工程，所以你需要用 git submodule 来取得对应的子模块代码

	git clone https://github.com/bzfshop-net/bzfshop.git 
	
	cd bzfshop
	
	git submodule init 
	
	git submodule update 
	
	经过 submodule update 之后你就取得了 bootstrap-custom 工程的代码，这样整个工程代码就完整了
	
	
### 商城技术支持

 1. 棒主妇开源官方网站 http://www.bzfshop.net
 
 2. Google Group 讨论组 https://groups.google.com/d/forum/bzfshop-group
 
 3. QQ群 134820563 

我们是 Linux 工作环境上 QQ 很不方便。QQ群 只回答普通小白用户的初级问题，技术 Geek 请尽量使用 Google Group。
Google Group 可能需要翻墙才能访问（不会翻墙的技术不是一个好技术）。
