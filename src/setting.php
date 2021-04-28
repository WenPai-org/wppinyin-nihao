<?php
/**
 * 设置项注册文件
 *
 * @package WenPai\PinYin
 */

namespace WenPai\PinYin\Src;

use WenPai\Framework\Setting;

add_action( 'wp_loaded' , function () {
    Setting::create_options( WPPY_PREFIX, array(
        'menu_title' => '文派拼音生成器',
        'menu_slug'  => 'wppy',
        'network'    => false,
    ) );

    Setting::create_section( WPPY_PREFIX, array(
        array(
            'id'     => 'content_add_pinyin',
            'title'  => __( '内容注音', 'wppy-nihao' ),
            'fields' => array(
                array(
                    'name'  => 'global_autoload_py',
                    'label' => __( '自动注音', 'wppy-nihao' ),
                    'desc'  => __( '勾选想开启自动注音功能的文章类型', 'wppy-nihao' ),
                    'type'  => 'checkbox',
                    'default'  => array(
                        'post' => 'post',
                        'page' => 'page',
                    ),
                    'options' => wppy_get_registered_post_types(),
                ),
            ),
        ),
        array(
            'id'     => 'slug_to_pinyin',
            'title'  => __( 'Slug转拼音', 'wppy-nihao' ),
            'fields' => array(
                array(
                    'name'    => 'type',
                    'label'   => __( '转换方式', 'wppy-nihao' ),
                    'desc'    => __( '全拼、首字母 (首字母模式下，英文也会取第一个字母)', 'wppy-nihao' ),
                    'type'    => 'select',
                    'default' => 0,
                    'options' => array(
                        0 => '拼音全拼',
                        1 => '拼音首字母',
                        // 2 => '百度翻译',
                    ),
                ),
                array(
                    'name'              => 'divider',
                    'label'             => __( '拼音分隔分隔符', 'wppy-nihao' ),
                    'desc'              => __( '可以是：_ 或 - 或 . &nbsp; 默认为 “-”，如过不需要分隔符，请留空', 'wppy-nihao' ),
                    'placeholder'       => __( '', 'wppy-nihao' ),
                    'default'           => '',
                    'type'              => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                array(
                    'name'              => 'length',
                    'label'             => __( '别名长度限制', 'wppy-nihao' ),
                    'desc'              => __('超过设置的长度后，会按照指定的长度截断转换后的拼音字符串。为保持拼音的完整性，如果设置了分隔符，会在最后一个分隔符后截断', 'wppy-nihao'),
                    'type'              => 'text',
                    'default'           => '60',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                array(
                    'name'              => 'baidu_app_id',
                    'label'             => __( '百度翻译 APP ID', 'wppy-nihao' ),
                    'desc'              => __( '请在百度翻译开放平台获取：http://api.fanyi.baidu.com/api/trans/product/index', 'wppy-nihao' ),
                    'type'              => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                array(
                    'name'              => 'baidu_api_key',
                    'label'             => __( '百度翻译密钥', 'wppy-nihao' ),
                    'desc'              => __( '请在百度翻译开放平台获取：http://api.fanyi.baidu.com/api/trans/product/index', 'wppy-nihao' ),
                    'type'              => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                array(
                    'name'              => 'disable_file_convert',
                    'label'             => __( '禁用文件名转换', 'wppy-nihao' ),
                    'desc'              => __( '不要自动转换文件名', 'wppy-nihao' ),
                    'type'              => 'switcher',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ),
    ) );
} );

add_action( 'admin_footer', function () {
    echo <<<html
<script>
jQuery(document).ready(function($) {
    function wppy_nihao_slug_check_select() {
        var check_el = $('select[name="wppy_slug_to_pinyin[translator_api]"]').val(),
            check_el2 = $('select[name="wppy_slug_to_pinyin[type]"]').val(),
            condition_el = $(
                'input[name="wppy_slug_to_pinyin[baidu_app_id]"], input[name="wppy_slug_to_pinyin[baidu_api_key]"]').
                parent().
                parent();

        if (parseInt(check_el) === 0 || parseInt(check_el2) !== 2) {
            condition_el.hide();
        } else {
            condition_el.show();
        }
    }

    wppy_nihao_slug_check_select();

    $('select[name="wppy_slug_to_pinyin[translator_api]"]').change(function() {
        wppy_nihao_slug_check_select();
    });

    $('select[name="wppy_slug_to_pinyin[type]"]').change(function() {
        wppy_nihao_slug_check_select();
    });
});
</script>
html;
} );
