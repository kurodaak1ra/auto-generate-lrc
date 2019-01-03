# 歌词一键生成脚本
本脚本通过调用网上大佬写的 Node.js 版网易云音乐 API ，来实现自动匹配歌曲歌词，生成同名的、纯净的LRC文件

## 该脚本只适用于有详细属性的音乐文件，需要用到的属性有：标题（歌曲名）、参与创作的艺术家、唱片集

### Node.js API 大佬
https://github.com/Binaryify/NeteaseCloudMusicApi

### 运行环境
PHP

### 需要的插件
getID3（用于读取歌曲信息）
PHP 的 OpenCC 拓展（当然，也可以选择不使用 OpenCC）

### 为什么要用 OpenCC
在之前生成歌词的时候
