<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin Name: AIYA-CMS 文件提取（Alist API）
 * Plugin URI: https://www.yeraph.com/
 * Description: 基于 Alist API 提取 Alist 服务器中的文件
 * Version: 1.1.0
 * Author: Yeraph Studio
 * Author URI: https://www.yeraph.com/
 * License: GPLv3 or later
 * Requires at least: 6.1
 * Tested up to: 6.7
 * Requires PHP: 8.2
 */

define('AYA_ALIST_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AYA_ALIST_PLUGIN_PATH', plugin_dir_path(__FILE__));

//依赖检查
if (file_exists(AYA_ALIST_PLUGIN_PATH . 'framework-required/setup.php')) {
    require_once AYA_ALIST_PLUGIN_PATH . 'framework-required/setup.php';
}
//引入 Alist 接口SDK文件
require_once AYA_ALIST_PLUGIN_PATH . 'lib/Http_Request.php';
require_once AYA_ALIST_PLUGIN_PATH . 'lib/Alist_API.php';
//插件文件
require_once AYA_ALIST_PLUGIN_PATH . 'inc/client-public.php';
require_once AYA_ALIST_PLUGIN_PATH . 'inc/client-option.php';
require_once AYA_ALIST_PLUGIN_PATH . 'inc/client-templates.php';
require_once AYA_ALIST_PLUGIN_PATH . 'inc/client-shortcode.php';
