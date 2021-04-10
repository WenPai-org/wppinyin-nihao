<?php
/**
 * 插件装载文件
 *
 * @package WenPai\PinYin
 */

namespace WenPai\PinYin;

use WenPai\PinYin\Src\{ PinYin, Slug };

/** 载入Composer的自动加载程序 */
require_once 'vendor/autoload.php';

/** 载入公共函数 */
require_once 'src/functions.php';

/** 载入设置项 */
if ( is_admin() && ! ( defined('DOING_AJAX' ) && DOING_AJAX) ) {
    require_once 'src/setting.php';
}

/** 载入Meta Box */
if ( is_admin() ) {
    require_once 'src/meta-box.php';
}

/** 载入Slug转拼音功能 */
if ( is_admin() ) {
    $args = array(
        'disable_file_convert' => wppy_get_option( 'disable_file_convert', 'slug_to_pinyin', 'off' ),
        'type'                 => (int)wppy_get_option( 'type', 'slug_to_pinyin', 0 ),
        'divider'              => wppy_get_option( 'divider', 'slug_to_pinyin', '-' ),
        'length'               => (int)wppy_get_option( 'length', 'slug_to_pinyin', 60 ),
        'baidu_app_id'         => wppy_get_option( 'baidu_app_id', 'slug_to_pinyin', '' ),
        'baidu_api_key'        => wppy_get_option( 'baidu_api_key', 'slug_to_pinyin', '' ),
    );
    $slug = new Slug( $args );
    $slug->register_hook();
}

/** 载入内容注音功能 */
$args = array(
    'global_autoload_py' => wppy_get_option( 'global_autoload_py', 'content_add_pinyin', [] ),
);
$pinyin = new PinYin( $args );
$pinyin->register_hook();
