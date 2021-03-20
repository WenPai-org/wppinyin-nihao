<?php
/**
 * Plugin Name: 文派拼音生成器
 * Description: 文章内容加拼音标注。
 * Author: 文派
 * Author URI:https://wenpai.org
 * Version: 1.0.0
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

use Overtrue\Pinyin\Pinyin;
use PHPHtmlParser\Dom;
use Wenpai\Framework\Setting;
// use Wenpai\Framework\Widget;

require_once 'wenpai-framework/framework/class-setting.php';
require_once 'wenpai-framework/framework/class-widget.php';

require_once('vendor/autoload.php');
require_once('inc/func.php');

define('WENPAIPINYIN_NIHAO_PREFIX', 'wppinyin_nihao');



/**
 * shortcode 短代码功能
 */
add_shortcode('wppinyin_shortcode', function () {
    if( ! is_single() ) return;
    $name = 'wppy_nihao_status_'.get_the_ID();
    printf('<label for="%s" class="wppy_nihao_label">
    <input type="checkbox" class="wppy_nihao_status" id="%s" name="%s" value="on">'.__('开/关注音','wppy_nihao').'
    </label>',$name,$name,$name);
});



/**
 * 前端单页面js引用
 */
add_action("wp_enqueue_scripts", function () {
    if( ! is_single() ) return;
    wp_register_script('wppy_nihao', plugins_url('wppinyin-nihao.js', __FILE__), array(), '', true);
    wp_enqueue_script('wppy_nihao');
    // 加载手动注音功能
    wp_register_script( 'wppy_gutenberg', plugins_url('/js/wppy-gutenberg.js', __FILE__), [
        'wp-element', 'wp-editor', 'wp-i18n',
        'wp-rich-text', 'wp-compose','wp-components',
    ], '0.01', true );
    wp_enqueue_script('wppy_gutenberg');

}, 11);



/**
 * 文章内容中自动增加注音
 * 
 * @param $content_original
 * 
 * @return mixed
 */
add_filter('the_content', function ($content_original) {
    global $post;
    $post_type = $post->post_type;
    $post_type_options = wppy_get_option( 'wppinyin_nihao_one', 'global_autoload_py' );
    // var_dump($post_type);
    // var_dump($post_type_options[$post_type]);
    // return $content_original;
    if( isset($_GET['zhuyin']) && $_GET['zhuyin'] == 'off' ){
        return $content_original;
    }else if( !isset($post_type_options[$post_type]) ){
        return $content_original;
    }
    // 小内存型
    // $pinyin = new Pinyin(); // 默认
    // 内存型
    $pinyin = new Pinyin('\\Overtrue\\Pinyin\\MemoryFileDictLoader');
    // I/O型
    // $pinyin = new Pinyin('\\Overtrue\\Pinyin\\GeneratorFileDictLoader');

    $html_tags = 'p,h1,h2,h3,h4,h5';
    $dom = new Dom;
    $dom->loadStr($content_original);
    
    foreach( explode(',' , $html_tags) as $tag ){
        foreach ($dom->find($tag) as $line) {
            $ruby_line = '<ruby>';
            foreach (my_mb_str_split($line->text) as $char) {
                if (preg_match("/[\x{4e00}-\x{9fa5}]/u", $char)) {
                    $pinyin_str = $pinyin->convert($char, PINYIN_TONE);
                    $pinyin_str = isset($pinyin_str[0]) ? $pinyin_str[0] : '';
                    $ruby_line .= "{$char}<rp>(</rp><rt>{$pinyin_str}</rt><rp>)</rp>";
                } else {
                    $ruby_line .= '</ruby>';
                    $ruby_line .= $char;
                    $ruby_line .= '<ruby>';
                }
            }
            $ruby_line .= '</ruby>';
            $line->firstChild()->setText($ruby_line);
        }
    }

    return $dom;
});



add_action('plugins_loaded', function () {
    // Register i18n.
	load_plugin_textdomain( 'wppy_nihao', false, basename( dirname( __FILE__ ) ) . '/languages' );

    // 加载slug转换主要功能
    require_once('inc/pinyin-slug.php');

});



/**
 * 加载后台JS
 * 
 * @param $hook
 *
 * @return mixed
 */
add_action('admin_enqueue_scripts', function($hook) {
    if($hook === 'post.php' ){
        // 加载手动注音功能
        wp_enqueue_script( 'wppy_gutenberg', plugins_url('/js/wppy-gutenberg.js', __FILE__), [
            'wp-element', 'wp-editor', 'wp-i18n',
            'wp-rich-text', 'wp-compose','wp-components',
        ], '0.01', true );
    }else if ($hook == 'settings_page_wenpai_pinyin_nihao') {
        wp_enqueue_script('wppinyin_nihao_slug_admin_script', plugin_dir_url(__FILE__) . 'scripts.js');
    }
    return;
});


add_action('wp_loaded',function(){

    /**
     * 创建设置页
     */
    Setting::create_options( WENPAIPINYIN_NIHAO_PREFIX, array(
        'menu_title' => '文派拼音生成器',
        'menu_slug' => 'wenpai_pinyin_nihao',
    ) );



    /**
     * 创建设置组
     */
    Setting::create_section( WENPAIPINYIN_NIHAO_PREFIX, array(
        array(
            'id'     => 'one',
            'title'  => '选项卡一',
            'fields' => array(
                array(
                    'name'  => 'global_autoload_py',
                    'label' => '加载时增加注音',
                    'desc'  => '开启后全站所有文章的内容均自动在加载时增加注音，同时需能勾选要生效的文章类型',
                    'type'  => 'checkbox',
                    'default' => array(
                        'one' => 'one',
                        'four' => 'four'
                    ),
                    'options' => wppy_get_registered_post_types(),                
                ),

            ),
        ), 
        
        array(
            'id'     => 'two',
            'title'  => '选项卡二',
            'fields' => array(
                array(
                    'name'    => 'type',
                    'label'   => __('转换方式','wppy_nihao'),
                    'desc'    => __('全拼、首字母或或百度翻译 (首字母模式下，英文也会取第一个字母)','wppy_nihao'),
                    'type'    => 'select',
                    'default' => 0,
                    'options' => array(
                        0 => '拼音全拼',
                        1 => '拼音首字母',
                        2 => '百度翻译',
                    ),
                ),

                array(
                    'name'              => 'divider',
                    'label'             => __('拼音分隔分隔符','wppy_nihao'),
                    'desc'              => __('可以是：_ 或 - 或 . &nbsp; 默认为 “-”，如过不需要分隔符，请留空','wppy_nihao'),
                    'placeholder'       => __('','wppy_nihao'),
                    'default'           => '',
                    'type'              => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                ),

                array(
                    'name'              => 'length',
                    'label'             => __('别名长度限制','wppy_nihao'),
                    'desc'              => __('超过设置的长度后，会按照指定的长度截断转换后的拼音字符串。为保持拼音的完整性，如果设置了分隔符，会在最后一个分隔符后截断','wppy_nihao'),
                    'type'              => 'text',
                    'default'           => '60',
                    'sanitize_callback' => 'sanitize_text_field',
                ),

                array(
                    'name'              => 'baidu_app_id',
                    'label'             => __('百度翻译 APP ID','wppy_nihao'),
                    'desc'              => __('请在百度翻译开放平台获取：http://api.fanyi.baidu.com/api/trans/product/index','wppy_nihao'),
                    'type'              => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                ),

                array(
                    'name'              => 'baidu_api_key',
                    'label'             => __('百度翻译密钥','wppy_nihao'),
                    'desc'              => __('请在百度翻译开放平台获取：http://api.fanyi.baidu.com/api/trans/product/index','wppy_nihao'),
                    'type'              => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                ),

                array(
                    'name'              => 'disable_file_convert',
                    'label'             => __('禁用文件名转换','wppy_nihao'),
                    'desc'              => __('不要自动转换文件名','wppy_nihao'),
                    'type'              => 'switcher',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ),
    ) );





});

