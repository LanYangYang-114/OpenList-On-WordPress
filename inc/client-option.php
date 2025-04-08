<?php
if (!defined('ABSPATH')) {
    exit;
}

/*
 * ------------------------------------------------------------------------------
 * 设置页面
 * ------------------------------------------------------------------------------
 */

//在主题之后加载
add_action('after_setup_theme', 'aya_alist_server_option_page');

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
                    'desc' => '文件/文件夹详情：[code][alist_cli method="get" path="/readme.md" password="" refresh="false"][/alist_cli][/code]',
                ],
                [
                    'type' => 'content',
                    'desc' => '文件/文件夹列表：[code][alist_cli method="list" path="/" password="" refresh="false"][/alist_cli][/code]',
                ],
                [
                    'type' => 'content',
                    'desc' => '搜索结果：[code][alist_cli method="search" parent="/" keyword="关键词" scope="2" password=""][/alist_cli][/code]',
                ],
                [
                    'type' => 'content',
                    'desc' => '直接输出文件真实地址：[code][alist_raw_url path="/readme.md" /][/code]（用于嵌入网页播放器）',
                ],
                [
                    'desc' => 'Alist 服务器接口',
                    'type' => 'title_1',
                ],
                //[
                //    'type' => 'callback',
                //    'function' => 'aya_alist_server_clear_token_button',
                //],
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
                    'desc' => 'Alist 的用户名，用于请求 Alist API 认证',
                    'id' => 'alist_client_api_user',
                    'type' => 'text',
                    'default' => 'username',
                ],
                [
                    'title' => '密码',
                    'desc' => 'Alist 的用户密码，用于请求 Alist API 认证',
                    'id' => 'alist_client_api_pswd',
                    'type' => 'text',
                    'default' => 'password',
                ],
                //[
                //    'desc' => 'Tips： Alist 的令牌（JWt Token）的有效期取决于 Alist 的设置，默认为 48 小时。',
                //    'type' => 'message',
                //],
                [
                    'title' => '令牌缓存时间',
                    'desc' => 'Alist 的 JWt Token 缓存时间（小时），设置为 [code]0[/code] 则每次都重新请求令牌',
                    'id' => 'alist_client_token_expire_hours',
                    'type' => 'text',
                    'default' => '48',
                ],
                [
                    'desc' => 'Alist 列表设置',
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
                    'title' => '获取文件图标',
                    'desc' => '为文件获取图标，使用 bootstrap-icons 图标库',
                    'id' => 'alist_client_view_icon',
                    'type' => 'radio',
                    'sub' => [
                        'off' => '禁用',
                        'ext' => '匹配文件后缀',
                        'typ' => '匹配文件类型',
                    ],
                    'default' => 'typ',
                ],
                [
                    'title' => '下载链接设置',
                    'desc' => '配置在文件列表中显示 Alist 的文件链接[br/]文件页面：直接返回文件详情页面（不会附带文件签名）[br/]下载地址：拼接文件的下载地址（请求[code]/d/[/code]路径）[br/]下载地址（代理）：拼接文件的下载地址（请求[code]/p/[/code]路径）',
                    'id' => 'alist_client_view_link_type',
                    'type' => 'checkbox',
                    'sub' => [
                        'page' => '文件页面',
                        'down' => '下载地址',
                        'proxy' => '下载地址（通过代理）',
                        'raw' => '直接取出直链',
                    ],
                    'default' => 'page',
                ],
            ],
        ]
    );
    
    if (is_admin()) {
        //初始化简码输入框组件按钮
        AYA_Shortcode::instance();

        AYA_Shortcode::shortcode_register('hidden-content', array(
            'id' => 'sc-alist-client',
            'title' => 'Alist 客户端',
            'note' => 'Alist 客户端的请求体调用',
            'template' => '[alist_cli {{attributes}}] {{content}} [/alist_cli]',
            'field_build' => array(
                [
                    'id' => 'method',
                    'type'  => 'select',
                    'label' => '请求方法',
                    'desc'  => '选择请求方法',
                    'sub' => [
                        'list' => '列表',
                        'get' => '详情',
                        'search' => '搜索',
                    ],
                    'default' => 'list',
                ],
                [
                    'id' => 'path',
                    'type' => 'text',
                    'label' => '路径',
                    'desc' => '请求的路径',
                    'default' => '/',
                ],
                [
                    'id' => 'refresh',
                    'type'  => 'checkbox',
                    'label' => '强制刷新',
                    'desc' => '是否强制刷新',
                    'default' => false,
                ],
                [
                    'id' => 'content',
                    'type' => 'textarea',
                    'label' => '描述',
                    'desc' => '在这里输入描述文本',
                    'default' => '',
                ]
            )
        ));
        AYA_Shortcode::instance();
    }
}

//服务器测试流程
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

    //aya_alist_refresh_token();

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



//add_action('after_setup_theme', 'aya_alist_server_option');
//add_action('wp_enqueue_scripts', 'aya_alist_plugin_enqueue_scripts');

//注册静态文件
function aya_alist_plugin_enqueue_scripts()
{
    $url_cdn = '//cdnjs.cloudflare.com/ajax/libs';
    //$url_cdn = '//s4.zstatic.net/ajax/libs';

    wp_register_script('bootstrap', $url_cdn . '/bootstrap/5.3.3/js/bootstrap.min.js', array(), '5.3.3', true);
    wp_register_style('bootstrap', $url_cdn . '/bootstrap/5.3.3/css/bootstrap.min.css', array(), '5.3.3', 'all');
    wp_register_style('bootstrap-icons', $url_cdn . '/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css', array(), '1.11.3', 'all');

    wp_enqueue_script('bootstrap');
    wp_enqueue_style('bootstrap');
    wp_enqueue_style('bootstrap-icons');
}

//添加设置选项
function aya_alist_server_option()
{
    //设置表单
    AYF::new_opt(array(
        'title' => 'AIYA-AlistClient',
        'slug' => 'alist',
        'desc' => 'AIYA-CMS 主题， Alist文件列表服务器接入插件',
        'fields' => array(
            array(
                'type' => 'message',
                'desc' => 'Alist 服务器需要在公开网络下可用，如果设置为 [code] http://127.0.0.1:5244 [/code] 使用时请启用直链获取设置',
            ),
            array(
                'desc' => 'Alist 列表设置',
                'type' => 'title_2',
            ),
            array(
                'title' => '默认列表描述',
                'desc' => '设置默认列表描述，输出于短代码 [code]content[/code] 之前',
                'id' => 'site_alist_list_desc',
                'type' => 'textarea',
                'default' => '文件下载由 your.alsitserver.name 提供支持',
            ),
            array(
                'title' => '异步加载',
                'desc' => '使用 AJAX 加载文件列表，而不是在页面中加载',
                'id' => 'site_alist_ajax_mode',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '获取文件直链（不推荐）',
                'desc' => '直接返回已解析的真实文件地址，而不是返回 Alist 的链接地址',
                'id' => 'site_alist_get_raw_url',
                'type' => 'switch',
                'default' => false,
            ),
            /*
            array(
                'desc' => 'Alist 自动功能',
                'type' => 'title_2',
            ),
            array(
                'title' => '自动为文章创建文件夹',
                'desc' => '在文章发布时，通过 Alist 接口为文章创建文件夹结构',
                'id' => 'site_alist_create_folder',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '选择驱动器',
                'desc' => '[b]必选！[/b]设置程序自动创建文件夹时，创建在哪个驱动器下',
                'id' => 'site_alist_create_folder_drive',
                'type' => 'radio',
                'sub'  => aya_alist_server_list_request(),
                'default' => 'false',
            ),
            array(
                'title' => '选择文件夹格式',
                'desc' => '[b]必选！[/b]设置程序自动创建文件夹时，文件夹名字格式',
                'id' => 'site_alist_create_folder_format',
                'type' => 'radio',
                'sub'  => array(
                    'by_title' => '/文章标题',
                    'by_date' => '/Y-m-d',
                    'by_id' => '/POST_ID',
                    'by_date_id' => '/POST_ID_Ymd',
                ),
                'default' => 'false',
            ),
            */
            array(
                'desc' => 'Alist 自定义CSS',
                'type' => 'title_2',
            ),
            array(
                //'title' => '文件列表样式',
                'desc' => '添加自定义的 CSS 样式',
                'id' => 'site_alist_custom_css',
                'type' => 'code_editor',
                'settings' => array(
                    'lineNumbers' => true,
                    'tabSize' => 0,
                    'theme' => 'monokai',
                    'mode' => 'css',
                ),
                'default' => '.alist-container{    
  .spinner-alist{
    color: linear-gradient(135deg, #c850c0, #4158d0);
  }
  .btn-alist-down {
    background: linear-gradient(135deg, #c850c0, #4158d0);
    border-color: #fff;
    color: #fff;
  }
  .btn-alist-down:hover {
    background: background: linear-gradient(135deg, #a1c4fd, #c2e9fb);/*transparent*/;
    border-color: #333;
    color: #fff;
  }
  .content-alist{
    color: #eee;
  }
}',
            ),
        ),
    ));
}
