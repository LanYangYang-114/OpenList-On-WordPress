## Alist 文件列表客户端插件 for WordPress

![截图](https://github.com/yeraph-plus/alist-client-plugin-for-wordpress/blob/master/screenshot/2025-04-13%20081411.png?raw=true)

使用 WordPress 短代码功能在文章中插入 Alist 服务器中的文件链接，通过 Alist 托管站点的文件下载。

### 插件说明

**需要 `PHP>=8.1` 并包含 `curl` 和 `json` 拓展**

插件使用 Alist API 实现，所有请求均在后端完成，无需担心令牌泄露。

短代码功能完整支持 API 的 `get`、`list`、`search` 接口，可使用文件路径、文件夹、索引关键词方式获取 Alist 中的文件或文件列表，支持分页显示。

### 使用说明

各选项详细说明请见插件设置页，以下为一些需要注意的地方：

1. 短代码支持使用交互表单快捷输入，但仅限经典编辑器，未适配古腾堡。

2. 插件中设置的 Alist 的服务器地址需要外部可访问，如果你的 Alist 无法从外部访问（如 `http://127.0.0.1:5244/`），请将插件链接设置改为 “提取直链”，否则插件会直接拼接为内部链接。

3. 插件默认在获取到超过10个文件时开始分页，可通过设置为0不分页，但由于 Alist 索引文件时强制要求分页参数，可在短代码中添加 `per_page="10"` 参数覆盖全局设置。

4. 插件默认使用异步请求文件列表，异步加载时会在页面中载入额外的JS组件，可能会有部分 Pjax 形式的主题不兼容，此时可切换为同步加载，但是会拖慢页面加载速度。

5. 前端使用 bootstrap-icons 图标库匹配文件图标，需要在站点中加载图标库的字体样式表。

6. 如果使用搜索接口，需要先为 Alist 配置数据库，否则 Alist 是不支持索引的。

7. 如果配置短代码参数 `refresh="ture"` ，会使 Alist 跳过自身缓存直接从网盘位置获取文件，通常不建议使用。

有使用问题/功能建议请提 issue ，随缘处理。

### 短代码参数说明

#### 最简调用：

`[alist_cli path="/" /]`

不指定方法时，默认使用`list`方法。PS：插件会通过正则简单检查传入的路径是否为文件，如果是则会切换到`get`方法。

#### 文件列表：

`[alist_cli method="list" path="/" password="" per_page="0" refresh="false" /]`

参数按顺序为插件方法、路径、访问密码、分页、强制刷新，前台使用文件列表模板。

#### 文件/文件夹详情：

`[alist_cli method="get" path="/readme.md" password="" per_page="0" refresh="false" /]`

参数按顺序为插件方法、文件&文件夹路径、访问密码、分页、强制刷新，前台使用文件详情卡片模板。

#### 搜索文件或文件夹：

`[alist_cli method="search" parent="/" keywords="关键词" scope="2" password="" per_page="0" /]`

参数按顺序为插件方法、搜索根路径、关键词、搜索范围、访问密码、分页，前台使用文件列表模板。

使用搜索方法时列表加载更慢，这是因为 Alist 索引结果是不包含文件详细参数的，所以内部需要将列表重新循环为`get`方法以获取文件。

#### 直接输出文件真实地址：

`[alist_raw_url path="/readme.md" /]`

用于嵌入网页播放器。~~但是播放器插件是不打算写了。~~

### Alist API 封装类

插件使用 Alist API 的所有请求方法封装在项目的 `/lib` 目录下，是根据 Alist API (V3) 实现的PHP版客户端实例。

封装部分仅Alist的`auth`、`fs`、`public`这三组接口，其他的元信息、用户、设置等的功能接口外部也用不上，就没管。

目录下提供了 `demo.php` 方法样例，你可以以此为参考自行实现其他功能或将 Alist 嵌入到其他PHP项目中。

此实例的请求参数和返回响应与官方文档一致，具体的参数定义请参照 [Alist 文档](https://alist.nn.ci/zh/guide/api/auth.html) 。

### ~~在线要饭~~

赏赏作者，支持一下：[我的爱发电](https://afdian.com/a/NyaaACG)
