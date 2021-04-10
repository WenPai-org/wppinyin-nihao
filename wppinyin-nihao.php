<?php
/**
 * Plugin Name: WPPinyin Nihao
 * Description: 将汉语拼音的应用规则体系引入 WordPress 网站，自动转换文章/页面/附件/自定义文章类型的别名，以及对文章内容进行拼音标注等。
 * Author: WenPai.org
 * Author URI: https://wenpai.org/
 * Version: 1.0.0
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace WenPai\PinYin;

define( 'WPPY_PREFIX', 'wppy' );
define( 'WPPY_VERSION', '1.0.0' );
define( 'WPPY_PLUGIN_URL' , plugin_dir_url( __FILE__ ) );

require_once 'load.php';
