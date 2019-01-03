# 歌词一键生成脚本

### 本脚本通过调用网上大佬写的 Node.js 版网易云音乐 API ，来实现自动匹配歌曲歌词，生成同名的、纯净的LRC文件
说明：<br>
该脚本只适用于有详细属性的音乐文件，需要用到的属性有：标题（歌曲名）、参与创作的艺术家、唱片集
目前测试的只有 flac、wav、mp3 格式

### 运行环境
PHP（我用的是 7+）

### 需要的插件
getID3（用于读取歌曲信息）<br>
PHP 的 OpenCC 拓展（当然，也可以选择不使用 OpenCC）

### 为什么要用 OpenCC
在之前生成歌词的时候（我主要是 Anime 的 Hi-Res 歌曲）一些歌曲是日本汉字（也就是繁体），但是网易上面试简体，所以就会匹配不到，所以。

### 歌曲匹配
第一次：歌曲名+专辑名<br>
第二次：歌曲名<br>
第三次：OpenCC 转简体后的歌曲名+专辑名<br>
第四次：OpenCC 转简体后的歌曲名<br>
（反正总有一次能匹配上 23333）

### PHP 环境搭建
（小白用户）<br>
Windows 推荐 WAMP<br>
Mac 推荐 XAMPP<br>
Linux ... 自己搭吧

### Node.js API
https://github.com/Binaryify/NeteaseCloudMusicApi<br>
看教程自己搭建 API<br>
(网上有个现成的 API，不过貌似做了限制，没办法调用而且还被墙了：https://163.fczbl.vip)

### OpenCC Win 版编译
https://github.com/NauxLiu/opencc4php/pull/16

### getID3
http://getid3.sourceforge.net/<br>
下载后解压到和脚本同级目录，文件夹命名为：getid3（路径在脚本176行，如有需要请自行修改）
