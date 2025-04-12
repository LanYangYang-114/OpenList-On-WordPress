<?php
if (!defined('ABSPATH')) {
    exit;
}

/*
 * ------------------------------------------------------------------------------
 * 数据处理
 * ------------------------------------------------------------------------------
 */

//前端传参时打包为JSON
function aya_alist_format_ajax_atts_encode($fs_data_atts = array())
{
    //转为JSON传递
    $data_json = aya_alist_json_encode($fs_data_atts);
    //编码为base64
    $data_base64 = base64_encode($data_json);

    $data_base64_urlsafe = str_replace(['+', '/', '='], ['-', '_', ''], $data_base64);

    return $data_base64_urlsafe;
}

//计算文件大小
function aya_alist_format_file_size($fs_data, $precision = 2)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($fs_data['size'], 0);
    //幂等计算
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 *$pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow];
}

//计算文件修改日期
function aya_alist_format_file_date($fs_data)
{
    //DateTime方法
    $this_date = new DateTime($fs_data['modified']);

    return $this_date->format('Y-m-d');
}

//生成文件图标的键值表映射
function aya_alist_icon_array_map()
{
    //静态变量缓存
    static $icon_map = null;

    //定义文件类型与图标的键值表映射
    if ($icon_map === null) {
        $icons_of_all = [
            //压缩文件
            'archive' => [
                'files' => ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz'],
                'icon_type' => 'zip'
            ],
            //图片
            'image' => [
                'files' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'heic', 'tiff', 'svg', 'raw', 'ico', 'swf'],
                'icon_type' => 'image'
            ],
            //音频
            'audio' => [
                'files' => ['mp3', 'flac', 'opus', 'ogg', 'aac', 'wav', 'wma', 'm4a'],
                'icon_type' => 'music'
            ],
            //视频
            'video' => [
                'files' => ['mp4', 'mkv', 'flv', 'ts', 'mov', 'mpg', 'mpeg', 'webm', 'm3u8'],
                'icon_type' => 'play'
            ],
            //文本
            'text' => [
                'files' => ['txt', 'json', 'conf', 'yml', 'log', 'ini', 'css', 'vtt', 'ass', 'srt', 'lrc'],
                'icon_type' => 'text'
            ],
            //富文本
            'document' => [
                'files' => ['rtf', 'html', 'htm', 'xhtml', 'mht', 'mhtml', 'chm', 'md', 'xml', 'epub', 'mobi'],
                'icon_type' => 'richtext'
            ],
            //加密格式
            'encryption' => [
                'files' => ['enc', 'pgp', 'gpg', 'bin', 'dll', 'pem', 'key'],
                'icon_type' => 'medical'
            ],
            //镜像格式
            'mirrorfile' => [
                'files' => ['iso', 'vhdx', 'vhd', 'dmg', 'img', 'crypt', 'crypted'],
                'icon_type' => 'post'
            ],
            //电子表单
            'spreadsheet' => [
                'files' => ['csv', 'tsv'],
                'icon_type' => 'spreadsheet'
            ],
            //SQL
            'db' => [
                'files' => ['sql', 'db'],
                'icon_type' => 'ruled'
            ],
            //字体
            'font' => [
                'files' => ['ttf', 'otf', 'ttc', 'oft', 'ps', 'woff', 'woff2'],
                'icon_type' => 'font'
            ],
            //文档
            'docx' => [
                'files' => ['doc', 'docx', 'odt'],
                'icon_type' => 'word'
            ],
            'pptx' => [
                'files' => ['ppt', 'pptx', 'odp'],
                'icon_type' => 'ppt'
            ],
            'xlsx' => [
                'files' => ['xls', 'xlsx', 'ods'],
                'icon_type' => 'excel'
            ],
            'pdf' => [
                'files' => ['pdf'],
                'icon_type' => 'pdf'
            ],
            //代码
            'code' => [
                'files' => ['php', 'js', 'tsx', 'py', 'java', 'c', 'cpp', 'h', 'hpp', 'go', 'swift', 'vue', 'rs', 'lua', 'sh', 'bat', 'cmd'],
                'icon_type' => 'code'
            ],
            //可执行文件
            'binary' => [
                'files' => ['exe', 'msi', 'apk', 'ipa', 'deb', 'iso', 'pkg', 'appimage', 'snap'],
                'icon_type' => 'binary'
            ],
        ];

        //递归到键值表中
        foreach ($icons_of_all as $icons) {
            foreach ($icons['files'] as $icon_ext) {
                $icon_map[$icon_ext] = $icons['icon_type'];
            }
        }
    }

    return $icon_map;
}

//根据文件名提取图标
function aya_alist_format_get_icon($icon_name, $icon_class = 'icon-mr-s')
{

    return '<i class="bi bi-' . $icon_name . ' ' . $icon_class . '"></i>';
}

//拼接文件类型图标和文件名
function aya_alist_format_file_name($fs_data)
{
    //图标设置
    $view_icon = aya_alist_opt('view_icon_type');

    $file_name = $fs_data['name'];

    //跳过文件夹
    if (boolval($fs_data['is_dir'])) {
        return aya_alist_format_get_icon('folder') . $file_name;
    }

    //跳过图标
    if ($view_icon == 'false') {
        return $file_name;
    }
    //取后缀图标
    else if ($view_icon == 'type') {
        //提取扩展名并转为小写
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        //添加其他文件类型
        $icon_ext_map = ['aac', 'ai', 'bmp', 'cs', 'css', 'csv', 'doc', 'docx', 'exe', 'gif', 'heic', 'html', 'java', 'jpg', 'js', 'json', 'jsx', 'key', 'm4p', 'md', 'mdx', 'mov', 'mp3', 'mp4', 'otf', 'pdf', 'php', 'png', 'ppt', 'pptx', 'psd', 'py', 'raw', 'rb', 'sass', 'scss', 'sh', 'sql', 'svg', 'tiff', 'tsx', 'ttf', 'txt', 'wav', 'woff', 'xls', 'xlsx', 'xml', 'yml'];

        if (in_array($extension, $icon_ext_map)) {
            $icon_name = 'filetype-' . $extension;
        } else {
            $icon_name = 'file-earmark';
        }
    }
    //取文件类型图标
    else {
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $after_icon = 'file';
        $between_icon = '';
        $before_icon = '';

        if ($view_icon == 'file_fill' || $view_icon == 'earmark_fill') {
            $before_icon = '-fill';
        } else if ($view_icon == 'earmark' || $view_icon == 'earmark_fill') {
            $between_icon = '-earmark';
        }

        $icon_map = aya_alist_icon_array_map();
        $icon_map_ext = (isset($icon_map[$extension])) ? '-' . $icon_map[$extension] : '';

        $icon_name = $after_icon . $between_icon . $icon_map_ext . $before_icon;
    }

    return aya_alist_format_get_icon($icon_name) . $file_name;
}

//拼接文件链接
function aya_alist_format_file_link($fs_data, $fs_query)
{
    //链接设置
    $view_link = aya_alist_opt('view_link_type');

    $data_is_dir = boolval($fs_data['is_dir']);

    //从数据中拼接完整的文件路径
    if ($fs_query['query_method_is'] == 'get') {
        $full_path = $fs_query['path'];
    } else {
        $full_path = '/' . ltrim($fs_query['path'], '/') . $fs_data['name'];
    }
    //匹配按钮名称
    if ($data_is_dir) {
        $btn_name = aya_alist_format_get_icon('folder') . __('打开', 'AIYA-ALIST');
        $down_path = '';
    } else if ($view_link == 'page_rf') {
        $btn_name = aya_alist_format_get_icon('link-45deg') . __('查看', 'AIYA-ALIST');
        $down_path = '';
    } else {
        $btn_name = aya_alist_format_get_icon('download') . __('下载', 'AIYA-ALIST');
        $down_path = '/d';
    }
    //直链模式
    if ($view_link == 'raw_url') {
        //如果是文件夹
        if ($data_is_dir) {
            return '<a class="btn down-btn down-btn-disabled">' . __('不可用', 'AIYA-ALIST') . '</a>';
        }
        //如果是get模式
        if ($fs_query['query_method_is'] == 'get') {
            $btn_href = $fs_data['raw_url'];
        } else {
            //请求直链
            $new_fs_data = aya_alist_cli()->fs_get($full_path, $fs_query['password']);

            if (!is_array($new_fs_data)) {
                return '<a class="btn down-btn down-btn-disabled">' . __('不可用', 'AIYA-ALIST') . '</a>';
            }
            $btn_href = $new_fs_data['raw_url'];
        }
    } else {
        $server_url = aya_alist_server_url();
        $file_sign = ($fs_data['sign'] == '') ? '' : '?sign=' . $fs_data['sign'];

        $btn_href = $server_url . $down_path . $full_path . $file_sign;
    }

    return '<a class="btn down-btn" href="' . $btn_href . '" target="_blank" >' . $btn_name . '</a>';
}

//计算分页
function aya_alist_format_page($page, $per_page, $total)
{
    //计算总页数
    $total_page = ceil($total / $per_page);

    //计算当前页
    if ($page > $total_page) {
        $page = $total_page;
    } else if ($page < 1) {
        $page = 1;
    }

    $page_html = '';
    $page_html .= '';

    return $page_html;
}

/*
 * ------------------------------------------------------------------------------
 * 模板组件
 * ------------------------------------------------------------------------------
 */

//注册AJAX动作
add_action('wp_ajax_alist_request_data', 'aya_alist_callback_ajax_post');
add_action('wp_ajax_nopriv_alist_request_data', 'aya_alist_callback_ajax_post');

//Ajax请求体结构模板
function aya_alist_template_ajax_post($fs_data_atts = array())
{
    //使用uid方法生成DOM的ID
    $unique_id = 'alist-' . uniqid();
    //请求参数
    $ajax_url = admin_url('admin-ajax.php');
    $post_query = array(
        'action' => 'alist_request_data',
        'nonce' => wp_create_nonce('alist_cli_data_nonce'),
        'data_atts' => aya_alist_format_ajax_atts_encode($fs_data_atts),
    );
    $build_body = http_build_query($post_query);

    //HTML结构
    $html = '';

    $html .= '<div id="' . $unique_id . '">';
    $html .= '<div class="spinner" role="status"><span class="visually-hidden">LOADING...</span></div>';
    $html .= '</div>';

    $html .= "<script> (function () {
        let ajax_url = '$ajax_url';
        let container = document.getElementById('$unique_id');

        fetch(ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '$build_body'
        }).then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok. Please try again later.');
            }
            return response.text();
        }).then(content => {
            container.innerHTML = content;
        }).catch(error => {
            console.error('Fetch operation error:', error);
            container.innerHTML = 'Unable to load content. Please try again later.';
        });
    })(); </script>";

    return $html;
}

//Ajax请求回调
function aya_alist_callback_ajax_post()
{
    //验证请求
    if (!wp_verify_nonce($_POST['nonce'], 'alist_cli_data_nonce')) {
        wp_die();
    }
    if (isset($_POST['data_atts']) && $_POST['data_atts'] == '') {
        wp_die();
    }
    //解码
    $data_base64 = base64_decode($_POST['data_atts']);
    $data_query = aya_alist_json_decode($data_base64);

    if ($data_query !== false) {
        echo aya_alist_template_workflow_main($data_query);
    }
    wp_die();
}

//完整工作流
function aya_alist_template_workflow_main($fs_query)
{
    $fs_method = $fs_query['query_method_is'];

    //传入参数检查
    if ($fs_method == 'list' || $fs_method == 'get') {
        //传入路径为空
        if ($fs_query['path'] == '') {
            $atts_msg = __('路径参数不能为空', 'AIYA-ALIST');
        }
        //处理一下传入路径参数
        $fs_query['path'] = aya_alist_path_slash_filter($fs_query['path']);
    }
    //搜索词检查
    else if ($fs_method == 'search') {
        //如果关键词为空，则返回报错
        if ($fs_query['keyword'] == '') {
            $atts_msg = __('未设置搜索关键词', 'AIYA-ALIST');
        }
    }
    //未指定的方法
    else {
        $atts_msg = __('未定义的请求方法', 'AIYA-ALIST');
    }

    //参数错误转出
    if (isset($atts_msg)) {
        return aya_alist_template_error($atts_msg);
    }

    //获取文件
    $fs_data = aya_alist_cli()->fs_request($fs_method, $fs_query, true);

    //报错转出
    if (!is_array($fs_data)) {
        //根据关键词匹配报错信息
        if (strpos($fs_data, 'EOF') !== false) {
            $msg = __('本地服务器发送请求失败', 'AIYA-ALIST');
        } else if (strpos($fs_data, '403') !== false) {
            $msg = __('文件访问被拒绝，未设置的访问密码或用户没有权限', 'AIYA-ALIST');
        } else if (strpos($fs_data, '401') !== false) {
            $msg = __('文件访问被拒绝，令牌失效，请检查', 'AIYA-ALIST');
        } else if (strpos($fs_data, '500') !== false) {
            $msg = __('文件/目录位置不存在，或搜索功能未就绪', 'AIYA-ALIST');
        } else {
            $msg = $fs_data;
        }

        return aya_alist_template_error($msg);
    }

    //插件设置
    $overall_desc = aya_alist_opt('overall_desc');
    $display_readme = aya_alist_opt('display_readme');

    //加载模板
    $html = '';
    $html .= '<div class="container alist-container">';

    //根据传入方法切换加载模板 
    if ($fs_method == 'get') {
        $html .= aya_alist_template_file_detail($fs_data, $fs_query);
    } elseif ($fs_method == 'list') {
        $html .= aya_alist_template_file_tables($fs_data, $fs_query);
    } else if ($fs_method == 'search') {
        $html .= aya_alist_template_search_result($fs_data, $fs_query);
    }

    //加载文件描述
    if ($fs_data['readme'] != '' && $display_readme) {
        $html .= '<p class="disc">' . $fs_data['readme'] . '</p>';
    }
    //加载全局描述
    $html .= '<p class="disc">' . $overall_desc . '</p>';

    $html .= '</div>';

    return $html;
}

//报错模板
function aya_alist_template_error($error_msg)
{
    $html = '';

    $html .= '<div class="container alist-container">';

    $html .= '<div class="card-flex"><div class="card">';
    $html .= aya_alist_format_get_icon('terminal-fill', 'icon-alert');
    $html .= '<p class="msg">' . $error_msg . '</p>';
    $html .= '</div></div>';

    $html .= '</div>';

    return $html;
}

//文件卡片模板
function aya_alist_template_file_detail($fs_data, $fs_query)
{
    $html = '';

    $html .= '<div class="card-flex">';
    $html .= '<div class="card">';

    $html .= '<h6 class="card-title">' . aya_alist_format_file_name($fs_data) . '</h6>';
    $html .= '<div class="split-line"></div>';

    $html .= '<dl class="details">';
    $html .= '<dt>' . __('文件大小', 'AIYA-ALIST') . '</dt>';
    $html .= '<dd>' . aya_alist_format_file_size($fs_data) . '</dd>';
    $html .= '<dt>' . __('修改日期', 'AIYA-ALIST') . '</dt>';
    $html .= '<dd>' . aya_alist_format_file_date($fs_data) . '</dd>';
    $html .= '<dt>' . __('提供方', 'AIYA-ALIST') . '</dt>';
    $html .= '<dd>' . $fs_data['provider'] . '</dd>';
    $html .= '</dl>';

    $html .= '<div class="row-link">' . aya_alist_format_file_link($fs_data, $fs_query) . '</div>';

    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

//文件列表模板
function aya_alist_template_file_tables($fs_data, $fs_query)
{
    //插件设置
    $ignore_dir = aya_alist_opt('always_ignore_dir');

    $html = '';

    $html .= '<h6 class="table-title mb-3">';
    $html .= aya_alist_format_get_icon('folder-open');
    $html .= '当前文件夹：' . $fs_query['path'];
    $html .= '</h6>';

    $html .= '<table class="table">';
    $html .= '<thead><tr>';
    $html .= '<th>' . __('文件', 'AIYA-ALIST') . '</th>';
    $html .= '<th>' . __('日期', 'AIYA-ALIST') . '</th>';
    $html .= '<th>' . __('大小', 'AIYA-ALIST') . '</th>';
    $html .= '<th>' . __('链接', 'AIYA-ALIST') . '</th>';
    $html .= '</tr></thead>';
    $html .= '<tbody>';

    foreach ($fs_data['content'] as $fs) {
        //跳过文件夹
        if ($ignore_dir && boolval($fs['is_dir'])) {
            continue;
        }
        $html .= '<tr>';
        $html .= '<td>' . aya_alist_format_file_name($fs) . '</td>';
        $html .= '<td>' . aya_alist_format_file_date($fs) . '</td>';
        $html .= '<td>' . aya_alist_format_file_size($fs) . '</td>';
        $html .= '<td>' . aya_alist_format_file_link($fs, $fs_query) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';

    return $html;
}

//搜索结果模板
function aya_alist_template_search_result($fs_data, $fs_query)
{
    echo '<pre>';
    print_r($fs_data);
    echo '</pre>';

    $html = '';

    $html .= '';
    return $html;
}