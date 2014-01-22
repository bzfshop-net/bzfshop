

## 棒主妇开源商城

bzfshop is an opensource B2C project. We are trying to provide a well-designed , efficient and totally free B2C website (like magento but light weight than it)

Here is a online demo for your interest;

Shop: http://demo.bzfshop.net  

Mobile: http://demo.bzfshop.net/mobile  

Manage: http://demo.bzfshop.net/manage   
username: admin   password:123456

Supplier: http://demo.bzfshop.net/supplier  


棒主妇开源(http://www.bzfshop.net) 是 棒主妇商城(http://www.bangzhufu.com) 的开源项目。我们希望提供一个设计严谨、性能优异、完全免费开源的
B2C商城给大家使用，方便每一个人可以很容易的搭建属于自己的B2C商城。

bzfshop 设计为一个跨平台的程序，可以支持多种平台的安装，目前包括：

* 普通服务器安装（虚拟主机、VPS、阿里云、独立服务器、服务器集群 ...）
* 新浪 SAE 平台（把 src 目录下内容上传到 SAE 即可）
* 百度 BAE3 平台（把 src 目录下内容上传到 BAE3 即可）

***

### 商城说明

棒主妇开源：http://www.bzfshop.net

商城演示：http://www.bzfshop.net/article/253.html

代码下载：http://www.bzfshop.net/article/239.html

官方文档：http://www.bzfshop.net/article/241.html

### 商城安装方法

 1. 下载代码，解压，把 src 目录下的文件上传到你的网站根目录

 2. 系统配置

    Apache 用户
    程序已经配置好了 .htaccess 文件，只要你的 Apache 支持 .htaccess，那就无需任何额外的配置了

    Nginx 用户
    我们提供了一个 nginx.conf 配置文件作为你的参考，你需要先配置好你的服务器，然后才能启动安装程序

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
	
	
### 技术支持

 1. 棒主妇开源官方网站 http://www.bzfshop.net
 
 2. Google Group 讨论组 https://groups.google.com/d/forum/bzfshop-group
 
 3. QQ群 134820563 
 

	