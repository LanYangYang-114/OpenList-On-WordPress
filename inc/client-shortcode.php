<?php
if (!defined('ABSPATH')) {
    exit;
}

/*
 * ------------------------------------------------------------------------------
 * 直链短代码
 * ------------------------------------------------------------------------------
 */

//请求文件直链
add_shortcode('alist_raw_url', 'aya_alist_cli_shortcode_get_raw_url');

function aya_alist_cli_shortcode_get_raw_url($atts = array(), $content = null)
{
    $atts = shortcode_atts(
        array(
            'path' => '/',
            'password' => '',
        ),
        $atts
    );

    $path = trim($atts['path']);
    $pswd = trim($atts['password']);

    //获取文件
    $fs = aya_alist_cli()->fs_get($path, $pswd);

    //检查报错
    if (!is_array($fs)) {
        return $fs;
    }
    //检查是否为文件夹
    if (boolval($fs['is_dir'])) {
        return 'The path is a folder, and the project is not available';
    }

    return $fs['raw_url'];
}

/*
 * ------------------------------------------------------------------------------
 * 接口端短代码
 * ------------------------------------------------------------------------------
 */

//请求完整列表
add_shortcode('alist_cli', 'aya_alist_cli_shortcode_fs_methods');

function aya_alist_cli_shortcode_fs_methods($atts = array(), $content = null)
{
    $atts = shortcode_atts(
        array(
            'method' => 'list',
            'path' => '/',
            'password' => '',
            //'page' => 1,
            'per_page' => 0,
            //'force_root' => false,
            'parent' => '',
            'keywords' => '',
            'scope' => 2, //0:all 1:dir 2:file
            'refresh' => false,
        ),
        $atts,
    );

    //提取短代码参数
    $method = trim($atts['method']);
    $per_page = intval($atts['per_page']);
    $pswd = trim($atts['password']);
    $path = trim($atts['path']);
    $refresh = filter_var($atts['refresh'], FILTER_VALIDATE_BOOLEAN);

    //插件设置
    $ajax_load = aya_alist_opt('ajax_load');
    $default_per_page = intval(aya_alist_opt('per_page_num'));

    //分页计算
    $per_page_num = ($per_page == 0) ? $default_per_page : $per_page;
    $get_page = (empty($_GET['page_a'])) ? 1 : intval($_GET['page_a']);
    $page = ($per_page_num == 0) ? 1 : $get_page;

    //开始配置请求参数
    $fs_query = [];

    if ($method == 'get') {
        //加载为文件详情
        $fs_query['path'] = $path;
        $fs_query['refresh'] = $refresh;

        //检查路径是文件则切换为get方法
        if (!aya_alist_guess_is_file($path)) {
            $method = 'list';
        }

    } else if ($method == 'list') {
        //加载为文件列表
        $fs_query['path'] = $path;
        $fs_query['refresh'] = $refresh;

    } else if ($method == 'search') {
        //加载为搜索列表
        $parent = trim($atts['parent']);
        $keywords = trim($atts['keywords']);
        $scope = intval($atts['scope']);

        //如果没有配置根目录参数但配置了路径，则切换
        if ($parent == '' && $path != '/') {
            $parent = $path;
        }
        //如果分页值为0,则强制重新分页
        if ($per_page_num == 0) {
            $per_page_num = 10;
        }

        $fs_query['parent'] = $parent;
        $fs_query['keywords'] = $keywords;
        $fs_query['scope'] = $scope;
    } else {
        $method = 'null';
    }

    $fs_query['page'] = $page;
    $fs_query['per_page'] = $per_page_num;
    $fs_query['password'] = $pswd;
    $fs_query['query_method_is'] = $method;

    $html = '';

    $html .= '<div class="container alist-container">';

    //异步模式
    if ($ajax_load) {
        $html .= aya_alist_template_ajax_post($fs_query);
    } else {
        //直接加载
        $html .= aya_alist_template_workflow_main($fs_query);
    }
    //默认内容
    if ($content != '') {
        $html .= do_shortcode($content);
    }

    $html .= '</div>';

    return $html;
}