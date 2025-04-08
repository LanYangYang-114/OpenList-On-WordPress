<?php
if (!defined('ABSPATH')) {
    exit;
}

/*
 * ------------------------------------------------------------------------------
 * 数据处理
 * ------------------------------------------------------------------------------
 */

//计算文件大小
function aya_alist_format_file_size($file_bytes, $precision = 2)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($file_bytes, 0);
    //幂等计算
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 *$pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow];
}

//计算文件创建日期
function aya_alist_format_file_date($file_date)
{
    //DateTime方法
    $this_date = new DateTime($file_date);

    return $this_date->format('Y-m-d');
}

//生成文件图标的键值表映射
function aya_alist_icon_map()
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
        //添加其他文件类型
        $icon_map_ext = ['aac', 'ai', 'bmp', 'cs', 'css', 'csv', 'doc', 'docx', 'exe', 'gif', 'heic', 'html', 'java', 'jpg', 'js', 'json', 'jsx', 'key', 'm4p', 'md', 'mdx', 'mov', 'mp3', 'mp4', 'otf', 'pdf', 'php', 'png', 'ppt', 'pptx', 'psd', 'py', 'raw', 'rb', 'sass', 'scss', 'sh', 'sql', 'svg', 'tiff', 'tsx', 'ttf', 'txt', 'wav', 'woff', 'xls', 'xlsx', 'xml', 'yml'];
    }

    return $icon_map;
}

//判断文件类型图标
function aya_alist_format_file_icon($file_name)
{
    // 提取扩展名并转为小写
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (empty($extension)) {
        return 'folder';
    }

}