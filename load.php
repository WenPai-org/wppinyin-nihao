<?php
/**
 * 插件装载文件
 *
 * @package WenPai\PinYin
 */

namespace WenPai\PinYin;

use WenPai\PinYin\Src\Slug;

/** 载入Composer的自动加载程序 */
require_once 'vendor/autoload.php';

/** 载入公共函数 */
require_once 'src/functions.php';

/** 载入设置项 */
if ( is_admin() && ! ( defined('DOING_AJAX' ) && DOING_AJAX) ) {
    require_once 'src/setting.php';
}

/** 载入Slug转拼音功能 */
$args = array(
    'disable_file_convert' => wppy_get_option( 'wppinyin_nihao_two', 'disable_file_convert', 'off' ),
    'type'                 => (int)wppy_get_option( 'wppinyin_nihao_two', 'type', 0 ),
    'divider'              => wppy_get_option( 'wppinyin_nihao_two', 'divider', '-' ),
    'length'               => (int)wppy_get_option( 'wppinyin_nihao_two', 'length', 60 ),
    'baidu_app_id'         => wppy_get_option( 'wppinyin_nihao_two', 'baidu_app_id', '' ),
    'baidu_api_key'        => wppy_get_option( 'wppinyin_nihao_two', 'baidu_api_key', '' ),
);
$slug = new Slug( $args );
$slug->register_hook();