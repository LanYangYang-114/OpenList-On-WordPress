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
    return AYF::get_opt('alist_client_' . $opt_name, 'alist');
}

//Ping测试
function aya_alist_ping_server()
{
    $server = aya_alist_opt('api_url');

    $alist = new Alist_API($server, false);

    return $alist->ping();
}

//获取服务器地址
function aya_alist_server_url()
{
    $server_url = aya_alist_opt('api_url');

    return trim($server_url, '/');
}

//获取Token
function aya_alist_request_token()
{
    $user = aya_alist_opt('api_user');
    $pswd = aya_alist_opt('api_pswd');

    $alist = new Alist_API(aya_alist_server_url(), false);

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
    $alist = new Alist_API(aya_alist_server_url(), aya_alist_transient_token());

    $get_permission = $alist->get_me();

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

/*
 * ------------------------------------------------------------------------------
 * 数据处理
 * ------------------------------------------------------------------------------
 */

//计算文件大小
function aya_alist_file_size_format($bytes, $precision = 2)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    //幂等计算
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 *$pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow];
}

//计算文件创建日期
function aya_alist_file_date_format($date)
{
    //DateTime方法
    $this_date = new DateTime($date);

    return $this_date->format('Y-m-d');
}