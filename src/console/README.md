
console 工程不是 Web 的一部分，而是一个命令行工具

有些任务不适合 Web 操作，比如 大量数据的导入、导出，数据的转换 等等，这个时候我们往往需要一个命令行工具来帮助我们执行
“长时间的命令”，console 工程就是为这个目的而设计的

console 工程提供一串的命令（Command 目录下），把 php 当做传统的脚本执行（类似 bash, perl ...）

console 工程的使用（cd 到 console 目录下执行）

1. 列出所有命令

/usr/bin/php Clip

Usage: $ clip <command-name> [parameters...]

The following commands are available:
 - CalculateGoodsBuyNumber
 - CheckGoodsInnerImageUrl
 - CreateDictionary
 - CreatePrivilege
 - FixGoodsInnerLink
 - MigrateZuitu
 - RegenerateThumbImage
 - ResetData
 - Test

2. 查看命令的帮助

/usr/bin/php Clip help ResetData

Reset database and remove everything under /data

3. 执行命令

/usr/bin/php Clip ResetData


注意： Linux 系统打印显示字符会有不同的颜色效果用于命令提示等等， Windows 系统没有这个功能

更多的关于命令行程序如何开发请直接查看 棒主妇开源文档

