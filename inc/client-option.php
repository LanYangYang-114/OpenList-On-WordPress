<?php
if (!defined('ABSPATH')) {
    exit;
}

/*
 * ------------------------------------------------------------------------------
 * 设置页面
 * ------------------------------------------------------------------------------
 */

//样式表文件
add_action('wp_enqueue_scripts', 'aya_alist_plugin_enqueue_scripts');
//在主题之后加载
add_action('after_setup_theme', 'aya_alist_server_option_page');
//在头部添加样式
add_action('wp_head', 'aya_alist_plugin_add_style');

//注册静态文件
function aya_alist_plugin_enqueue_scripts()
{
    wp_register_style('bootstrap-icons', AYA_ALIST_PLUGIN_URL . '/assets/css/bootstrap-icons.min.css', array(), '1.11.3', 'all');
    wp_register_style('alist-client', AYA_ALIST_PLUGIN_URL . '/assets/css/alist-style.css', array(), '1.1.0', 'all');

    wp_enqueue_style('alist-client');

    if (aya_alist_opt('view_icon_type') != 'false') {
        wp_enqueue_style('bootstrap-icons');
    }
}
function aya_alist_plugin_add_style()
{
    print ('<style>' . aya_alist_opt('custom_css') . '</style>');
}

//设置表单
function aya_alist_server_option_page()
{
    //检查设置框架
    if (!class_exists('AYF')) {
        return;
    }

    AYF::new_opt(
        [
            'title' => 'AIYA-AlistClient',
            'slug' => 'alist',
            'desc' => 'AIYA-CMS 主题， Alist文件列表服务器接入插件',
            'fields' => [
                [
                    'desc' => 'Alist 客户端短代码',
                    'type' => 'title_2',
                ],
                [
                    'type' => 'content',
                    'desc' => '文件/目录详情：[code][alist_cli method="get" path="/readme.md" password="" refresh="false"][/alist_cli][/code]',
                ],
                [
                    'type' => 'content',
                    'desc' => '列出文件目录：[code][alist_cli method="list" path="/" password="" refresh="false"][/alist_cli][/code]',
                ],
                [
                    'type' => 'content',
                    'desc' => '搜索文件或文件夹：[code][alist_cli method="search" parent="/" keyword="关键词" scope="2" password=""][/alist_cli][/code]',
                ],
                [
                    'type' => 'content',
                    'desc' => '直接输出文件真实地址：[code][alist_raw_url path="/readme.md" /][/code]（用于嵌入网页播放器）',
                ],
                [
                    'desc' => 'Alist 服务器接口',
                    'type' => 'title_1',
                ],
                aya_alist_server_option_test_request(),
                [
                    'title' => '服务器地址',
                    'desc' => 'Alist 的服务器地址',
                    'id' => 'alist_client_api_url',
                    'type' => 'text',
                    'default' => 'https://your.alsitserver.name',
                ],
                [
                    'title' => '用户名',
                    'desc' => 'Alist 用户名，用于请求 Alist API 认证',
                    'id' => 'alist_client_api_user',
                    'type' => 'text',
                    'default' => 'username',
                ],
                [
                    'title' => '密码',
                    'desc' => 'Alist 用户密码，用于请求 Alist API 认证',
                    'id' => 'alist_client_api_pswd',
                    'type' => 'text',
                    'default' => 'password',
                ],
                [
                    'title' => '令牌缓存时间',
                    'desc' => 'Alist 的 JWt Token 缓存时间（小时），设置为 [code]0[/code] 则每次都重新请求令牌
                    [br/] * 取决于 Alist 的config.json配置，默认为 48 小时',
                    'id' => 'alist_client_token_expire_hours',
                    'type' => 'text',
                    'default' => '48',
                ],
                [
                    'desc' => 'Alist 客户端设置',
                    'type' => 'title_1',
                ],
                [
                    'title' => '异步加载',
                    'desc' => '使用 AJAX 加载文件列表，而不是在页面中加载',
                    'id' => 'alist_client_ajax_load',
                    'type' => 'switch',
                    'default' => true,
                ],
                [
                    'title' => '自动分页',
                    'desc' => '当返回结果超过指定数量时自动分页，设置为 [code]0[/code] 时不分页
                    [br/]* 此选项可被短代码中的 [code]per_page=""[/code] 参数覆盖',
                    'id' => 'alist_client_per_page_num',
                    'type' => 'text',
                    'default' => '0',
                ],
                [
                    'title' => '文件图标切换',
                    'desc' => '为文件匹配获取图标（使用 bootstrap-icons 图标库）',
                    'id' => 'alist_client_view_icon_type',
                    'type' => 'radio',
                    'sub' => [
                        'false' => '禁用',
                        'type' => '后缀名',
                        'file' => '默认',
                        'file_fill' => '默认（填充）',
                        'earmark' => '折页',
                        'earmark_fill' => '折页（填充）',
                    ],
                    'default' => 'file',
                ],
                [
                    'title' => '链接设置',
                    'desc' => '设置文件列表和文件卡片中链接的跳转位置
                    [br/]文件页面：跳转到 Alist 的文件/文件夹详情页面
                    [br/]直接下载：直接下载 Alist 的文件（由 Alist 自身完成 302 跳转）
                    [br/]提取直链：直接取出 Alist 的文件真实文件地址',
                    'id' => 'alist_client_view_link_type',
                    'type' => 'radio',
                    'sub' => [
                        'page_rf' => '文件页面',
                        'down_rf' => '直接下载',
                        'raw_url' => '提取直链',
                    ],
                    'default' => 'down_rf',
                ],
                [
                    'title' => '列表时忽略目录',
                    'desc' => '在返回的文件列表中忽略的目录（只显示文件）',
                    'id' => 'alist_client_always_ignore_dir',
                    'type' => 'switch',
                    'default' => true,
                ],
                [
                    'title' => '显示 readme 内容',
                    'desc' => '如果获取到 Alist 中设置的 readme.md 内容，输出于短代码内容结束之前',
                    'id' => 'alist_client_display_readme',
                    'type' => 'switch',
                    'default' => false,
                ],
                [
                    'title' => '全局列表描述',
                    'desc' => '设置默认列表描述，输出于短代码内容结束之前',
                    'id' => 'alist_client_overall_desc',
                    'type' => 'textarea',
                    'default' => '文件下载由 your.alsitserver.name 提供支持',
                ],
                [
                    'desc' => 'Alist 自定义CSS',
                    'type' => 'title_1',
                ],
                [
                    //'title' => '文件列表样式',
                    'desc' => '添加自定义的 CSS 样式',
                    'id' => 'alist_client_custom_css',
                    'type' => 'code_editor',
                    'settings' => [
                        'lineNumbers' => true,
                        'tabSize' => 0,
                        'theme' => 'monokai',
                        'mode' => 'css',
                    ],
                    'default' => aya_alist_server_default_style_var(),

                ],
            ],
        ]
    );

    if (is_admin()) {
        //初始化简码输入框组件按钮
        AYA_Shortcode::instance();

        AYA_Shortcode::shortcode_register('alist-client', array(
            'id' => 'sc-alist-client',
            'title' => 'Alist 客户端',
            'note' => 'Alist 客户端的请求体调用',
            'template' => '[alist_cli {{attributes}}]{{content}}[/alist_cli]',
            'field_build' => array(
                [
                    'id' => 'method',
                    'type' => 'select',
                    'label' => '请求方法',
                    'desc' => '选择请求方法',
                    'sub' => [
                        'list' => '列出文件目录',
                        'get' => '获取文件/目录信息',
                    ],
                    'default' => 'list',
                ],
                [
                    'id' => 'path',
                    'type' => 'text',
                    'label' => '路径',
                    'desc' => '请求的目录或文件路径',
                    'default' => '/',
                ],
                [
                    'id' => 'content',
                    'type' => 'textarea',
                    'label' => '描述',
                    'desc' => '在页面中显示的描述文本（支持短代码嵌套）',
                    'default' => '',
                ],
                [
                    'id' => 'refresh',
                    'type' => 'checkbox',
                    'label' => '强制刷新',
                    'desc' => '是否强制刷新（跳过SQL缓存）',
                    'default' => false,
                ],
            )
        ));
        AYA_Shortcode::shortcode_register('alist-search', array(
            'id' => 'sc-alist-search',
            'title' => '在 Alist 中搜索',
            'note' => 'Alist 客户端的请求体调用',
            'template' => '[alist_cli {{attributes}}]{{content}}[/alist_cli]',
            'field_build' => array(
                [
                    'id' => 'method',
                    'type' => 'select',
                    'label' => '请求方法',
                    'desc' => '选择请求方法',
                    'sub' => [
                        'search' => '搜索文件/文件夹',
                    ],
                    'default' => 'search',
                ],
                [
                    'id' => 'parent',
                    'type' => 'text',
                    'label' => '搜索目录',
                    'desc' => '搜索的目录路径',
                    'default' => '/',
                ],
                [
                    'id' => 'keyword',
                    'type' => 'text',
                    'label' => '关键词',
                    'desc' => '搜索的关键词（支持短代码嵌套）',
                    'default' => '',
                ],
                [
                    'id' => 'scope',
                    'type' => 'select',
                    'label' => '搜索类型',
                    'desc' => '仅搜索文件或文件夹',
                    'sub' => [
                        '0' => '全部',
                        '1' => '文件夹',
                        '2' => '文件',
                    ],
                    'default' => '2',
                ],
                [
                    'id' => 'content',
                    'type' => 'textarea',
                    'label' => '描述',
                    'desc' => '在页面中显示的描述文本（支持短代码嵌套）',
                    'default' => '',
                ],
            )
        ));
    }
}

//定义默认的前台样式变量
function aya_alist_server_default_style_var()
{
    return ".alist-container {
    --alist-cli-container-max-width: 670px;
    --alist-cli-main-color: linear-gradient(135deg, #c850c0, #4158d0);
    --alist-cli-main-color-hover: linear-gradient(35deg, #4158d0, #c850c0);
    --alist-cli-card-background: #fff;
    --alist-cli-card-box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    --alist-cli-card-border-radius: 5px;
}";
}

//服务器测试
function aya_alist_server_option_test_request()
{
    //未初始化
    if (empty(aya_alist_opt('api_url'))) {
        return [
            'desc' => '首次启动：请先设置 Alist 服务器地址',
            'type' => 'message',
        ];
    }

    $ping_server = aya_alist_ping_server();

    //连接失败
    if (!$ping_server) {
        return [
            'desc' => '错误： Alist 服务器连接失败，请检查服务器地址',
            'type' => 'warning',
        ];
    }

    aya_alist_refresh_token();

    $get_token = aya_alist_transient_token();

    //用户名或密码错误
    if (!$get_token) {
        return [
            'desc' => '错误： Alist 用户名或密码错误，请检查设置',
            'type' => 'dismiss',
        ];
    }

    $get_permission = aya_alist_permission_check();

    //令牌无效或账号禁用
    if (!$get_permission) {
        return [
            'desc' => '错误： Alist 用户不存在或已禁用，请检查设置',
            'type' => 'warning',
        ];
    }

    //连接正常
    return [
        'desc' => '连接成功！ Alist 存储连接正常',
        'type' => 'success',
    ];
}