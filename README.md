### ☁Emo 后端Hyperf项目代码

体验地址： http://emo.abigeater.com

## 功能：

1. 云听歌，由服务端跟踪播放进度
2. 在线唠嗑、 人数统计
3. 自动解析歌曲文件获取歌曲信息
4. 自动提取歌曲封面
5. 可手动编辑歌曲信息

## 如何部署：

1. 修改环境变量下的服务器地址：
  `config/autoload/player.php`[14:16] => 将服务器修改成你部署的服务器, 和静态文件路径

2. 本地可直接通过建立`.env`文件设置环境变量：

```code
PLAYER_HOST=http://127.0.0.1:9501
PLAYER_STATIC_PATH=music
```

3. 本地测试需要删除`config/autoload/server.php`[55:56]注释, 本地才能访问静态资源，在服务上参考部署文章设置静态文件目录即可。

4. 数据基本存储在内存内，所以只能以单核运行，如果新增了歌曲可重启服务或者内网访问地址：
    ```code
    curl 127.0.0.1:9501/reload/play/list
    ```
    即可重新加载歌曲列表

5. 将歌曲存放在目录`/resource/music/`下

6. 默认已开启自动监听音乐目录，增加新文件后自动重新加载歌单列表，通过环境变量`PLAYER_LISTEN_MUSIC_AUTO_LOAD=false`可关闭

参考部署文章：[Hyperf项目使用Supervisor部署](https://abigeater.com/archives/190)