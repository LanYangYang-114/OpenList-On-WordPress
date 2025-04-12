<?php
if (!defined('ABSPATH')) {
    exit;
}

/*
 * ------------------------------------------------------------------------------
 * 插件方法
 * ------------------------------------------------------------------------------
 */

//取出设置
function aya_alist_opt($opt_name)
{
    if (!class_exists('AYF')) {
        return false;
    }

    return AYF::get_opt('alist_client_' . $opt_name, 'alist');
}

//JSON编码
function aya_alist_json_encode($data)
{
    $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }

    return $json;
}

//JSON解码
function aya_alist_json_decode($data)
{
    $array = json_decode($data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }

    return $array;
}

//获取服务器地址
function aya_alist_server_url()
{
    $server_url = aya_alist_opt('api_url');

    return trim($server_url, '/');
}

//Ping测试
function aya_alist_ping_server()
{
    $server = aya_alist_server_url();

    $alist = new Alist_API($server, false);

    return $alist->ping();
}

//获取Token
function aya_alist_request_token()
{
    $server = aya_alist_server_url();

    $user = aya_alist_opt('api_user');
    $pswd = aya_alist_opt('api_pswd');

    $alist = new Alist_API($server, false);

    return $alist->get_token($user, $pswd);
}

//Token缓存逻辑
function aya_alist_transient_token()
{
    //获取缓存
    $get_token = get_transient('alist_client_jwt_token');

    //取到缓存
    if ($get_token) {

        return $get_token;
    }

    //请求新的令牌
    $get_token = aya_alist_request_token();

    $expire_hours = intval(aya_alist_opt('token_expire_hours'));

    //不缓存时直接返回
    if ($expire_hours == 0) {
        return $get_token;
    }

    //检查令牌报错
    if (strpos($get_token, 'ERROR:') === false) {
        //设置缓存
        set_transient('alist_client_jwt_token', $get_token, $expire_hours * 3600);

        return $get_token;
    }

    return false;
}

//Token缓存刷新
function aya_alist_refresh_token()
{
    //获取缓存
    $get_token = get_transient('alist_client_jwt_token');

    if ($get_token) {
        //删除缓存
        delete_transient('alist_client_jwt_token');

        return true;
    } else {

        return false;
    }
}

//验证Token权限
function aya_alist_permission_check()
{
    $get_permission = aya_alist_cli()->get_me();

    //令牌失效
    if (!is_array($get_permission) && strpos($get_permission, 'ERROR:') !== false) {

        return false;
    }

    //是否被禁用
    if ($get_permission['disabled'] == true) {

        return false;
    }

    return true;
}

//返回 Alist 接口对象
function aya_alist_cli()
{
    $server = aya_alist_server_url();
    $token = aya_alist_transient_token();

    $alist_cli = new Alist_API($server, $token);

    return $alist_cli;
}

//判断字符串路径是否为文件
function aya_alist_guess_is_file($path)
{
    if (preg_match('/\.[a-z0-9]+$/i', $path)) {
        return true;
    } else {
        return false;
    }

}

//处理路径格式
function aya_alist_path_slash_filter($path)
{
    $path = trim($path, '/');

    return '/' . $path;
}