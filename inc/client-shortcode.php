<?php
if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('alist_dev', 'aya_alist_shortcode_methods');

function aya_alist_shortcode_methods($atts = array(), $content = null)
{
    //print_r(aya_alist_permission_check());
    return;
}

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
        return __('目标路径为文件夹，项目不可用', 'AIYA-ALIST');
    }

    //$fs['raw_url']
    return print_r($fs);
}

//请求完整列表
add_shortcode('alist_list', 'aya_alist_cli_shortcode_fs_methods');

function aya_alist_cli_shortcode_fs_methods($atts = array(), $content = null)
{

    $atts = shortcode_atts(
        array(
            'method' => 'get', //by:list by:search
            'path' => '/',
            'password' => '',
            //'page' => 1,
            //'per_page' => 0,
            'refresh' => false,
            //'force_root' => false,
            'parent' => '',
            'keyword' => '',
            'scope' => 2, //0:all 1:file 2:dir
        ),
        $atts,
    );

    $method = trim($atts['method']);
    $path = trim($atts['path']);
    $pswd = trim($atts['password']);
}

//请求完整文件列表（Ajax）
add_shortcode('alist_list_ajax', 'aya_alist_cli_shortcode_fs_methods_ajax');
function aya_alist_cli_shortcode_fs_methods_ajax($atts = array(), $content = null)
{
    $atts = shortcode_atts(
        array(
            'method' => 'get', //by:list by:search
            'path' => '/',
            'password' => '',
            //'page' => 1,
            //'per_page' => 0,
            'refresh' => false,
            //'force_root' => false,
            'parent' => '',
            'keyword' => '',
            'scope' => 2, //0:all 1:file 2:dir
        ),
        $atts,
    );

    $method = trim($atts['method']);
    $path = trim($atts['path']);
    $pswd = trim($atts['password']);
}