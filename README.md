# 歌词一键生成脚本

### 本脚本通过调用网上大佬写的 Node.js 版网易云音乐 API ，来实现自动匹配歌曲歌词，生成同名的、纯净的LRC文件

### 说明：<br>
该脚本只适用于有详细属性的音乐文件，需要用到的属性有：标题（歌曲名）、参与创作的艺术家、唱片集
目前测试的只有 flac、wav、mp3 格式

### 运行环境
PHP（我用的是 7+）

### 需要的插件
getID3（用于读取歌曲信息）<br>
PHP 的 OpenCC 拓展（当然，也可以选择不使用 OpenCC）

### 为什么要用 OpenCC
在之前生成歌词的时候（我主要是 Anime 的 Hi-Res 歌曲）一些歌曲名字里面含有日本汉字（也就是繁体），但是网易上面是简体，所以就会匹配不到，所以转换一下，增大匹配到的几率

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

### 匹配精度
因为自动脚本无人工干预，所以要留有容差<br>
歌曲名字和艺术家名字最小匹配精度 0（无限制），最大匹配精度 100（完全匹配），不需要 %<br>
歌曲时长偏移量，0（时长完全一致），最大误差半分钟，前后误差 N 秒以内（以本地歌曲时长作为标准值，API获取到的时长上做增减），不需要 s

### Node.js API
https://github.com/Binaryify/NeteaseCloudMusicApi<br>
看教程自己搭建 API<br>
(网上有个现成的 API，不过貌似做了限制，没办法调用而且还被墙了：https://163.fczbl.vip)

### OpenCC Win 版编译
https://github.com/NauxLiu/opencc4php/pull/16

### getID3
http://getid3.sourceforge.net/<br>
下载后解压到和脚本同级目录，文件夹命名为：getid3（路径在脚本176行，如有需要请自行修改）
