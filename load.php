<?php



define( 'WPPY_NIHAO_PLUGIN_DIR_PATH' , plugin_dir_path( __FILE__ ) );




use WenPai\PinYin;

require_once(WPPY_NIHAO_PLUGIN_DIR_PATH . 'src/class-pinyin.php');

$PinYin = new PinYin();
$PinYin->register_hook();







